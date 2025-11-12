<?php

namespace App\Http\Controllers\Tenant\Mgr\Quotations\Notifications;

use App\Enums\LexiconStaticTag;
use App\Facades\Settings;
use App\Foundation\Status\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Notifications\SendQuotationMailRequest;
use App\Http\Resources\Lexcons\LexiconResource;
use App\Http\Resources\Orders\OrderResource;
use App\Http\Resources\Quotations\QuotationResource;
use App\Jobs\Tenant\Quotations\SendInternalMailJob;
use App\Mail\SendQuotationMail;
use App\Models\Tenant\Lexicon;
use App\Models\Tenant\MailQueue;
use App\Models\Tenant\Media\FileManager;
use App\Models\Tenant\Quotation;
use App\Models\User;
use App\Repositories\LexiconRepository;
use App\Repositories\OrderRepository;
use App\Scoping\Scopes\Lexicons\LexiconLanguageScope;
use App\Utilities\Quotation\QuotationHasher;
use App\Utilities\Traits\ConsumesExternalServices;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

final class MailController extends Controller
{
    use ConsumesExternalServices;

    /**
     * @var OrderRepository
     */
    private readonly OrderRepository $quotation;

    /**
     * @var LexiconRepository
     */
    private readonly LexiconRepository $lexicon;

    /**
     * default hiding field from response
     */
    protected array $hide;

    /**
     * default total result in one page
     */
    protected int $per_page = 15;

    protected bool $type = false;


    public $base_uri;

    /**
     * UserController constructor.
     * @param Request   $request
     * @param Quotation $quotation
     * @param Lexicon   $lexicon
     */
    public function __construct(
        Request   $request,
        Quotation $quotation,
        Lexicon   $lexicon
    )
    {
        $this->quotation = new OrderRepository($quotation);
        $this->lexicon = new LexiconRepository($lexicon);

        /**
         * default number of pages
         */
        $this->per_page = $request->get('per_page') ?? $this->per_page;
        $this->hide[] = $request->get('include_items') ?? 'items';
    }

    /**
     * @return AnonymousResourceCollection|mixed
     */
    public function show(): mixed
    {
        return LexiconResource::collection(
            $this->lexicon->template('mail', 'quotation', $this->scopes())
        )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null,
                'meta' => [
                    'tags' => LexiconStaticTag::getAllFormatted()
                ],
            ]);
    }

    /**
     * Render a lexicon template to a fully qualified string
     *
     * @param string|null $template
     * @param Quotation $quotation
     *
     * @return string
     */
    private function renderLexicon(
        ?string    $template,
        Quotation $quotation
    ): string
    {
        $templateWithLineBreaks = str_replace(["\n", "\r\n"], "<br>", $template);

        $staticRenderedTemplate = preg_replace_callback('/\[\[%([^]]+)\]\]/',
            function (array $matches) use ($quotation) {
                return LexiconStaticTag::resolveOrFallback($matches[1], $quotation);
            },
            $templateWithLineBreaks
        );

        return preg_replace_callback('/\[\[%([^]]+)\]\]/',
            function (array $matches) use ($quotation) {
                $variables = explode('.', $matches[1]);

                array_shift($variables);

                return walk($quotation, $variables);
            },

            $staticRenderedTemplate
        );
    }

    /**
     * Send a quotation mail to the customer
     *
     * @param SendQuotationMailRequest $request
     * @param Quotation $quotation
     * @param QuotationHasher $quotationHasher
     *
     * @return QuotationResource|JsonResponse
     */
    public function send(
        SendQuotationMailRequest $request,
        Quotation $quotation,
        QuotationHasher $quotationHasher,
    ): QuotationResource|JsonResponse
    {
        if (!$quotation->connection) {
            $expiresAfter = Settings::quotationExpiresAfter()?->value;

            if ($quotation->st === Status::NEW && $quotation->orderedBy) {
                // @todo create facade for user settings
                $language = $request->get('language') ?: Settings::managerLanguage()->value;

                // @todo create facade for lexicons
                $mailQuotationLexcon = Lexicon::where('namespace', 'mail')
                    ->where('area', 'quotation')
                    ->where('language', strtolower($language))
                    ->get();

                $mailSubject = $request->input('subject') ?: $mailQuotationLexcon->firstWhere(
                    'template',
                    'subject'
                )?->value;

                $mailBody = $request->input('body') ?: $mailQuotationLexcon->firstWhere(
                    'template',
                    'body'
                )?->value;

                $mailGreeting = $request->input('greeting') ?: $mailQuotationLexcon->firstWhere(
                    'template',
                    'greeting'
                )?->value;

                $mailRegards = $request->input('regards') ?: $mailQuotationLexcon->firstWhere(
                    'template',
                    'regards'
                )?->value;

                $quotation->update([
                    'st' => Status::WAITING_FOR_RESPONSE,
                    'expire_at' => Carbon::now()->addDays($expiresAfter)
                ]);

                $mailQueue = $quotation->mailQueues()->create([
                    'st' => Status::NEW,
                    'message' => MailQueue::formatMessageObject(
                        greeting: $this->renderLexicon($mailGreeting, $quotation),
                        message: $this->renderLexicon($mailBody, $quotation),
                        regards: $this->renderLexicon($mailRegards, $quotation),
                    ),
                    'sent_at' => null,
                    'from' => Settings::mailSmtpFrom()->value,
                    'to' => $quotation->orderedBy->email,
                    'subject' => $this->renderLexicon($mailSubject, $quotation),
                    'attachment' => true,
                    'tenant_id' => tenant()->uuid
                ]);

                SendInternalMailJob::dispatch(
                    tenant(),
                    $request->user(),
                    $quotation,
                    $mailQueue,
                    $expiresAfter,
                    $language,
                    $quotationHasher->generate($quotation)
                );
            }

            return QuotationResource::make($quotation)
                ->hide(
                    ['shipping_cost']
                )->hideChildren(
                    [
                        'status' => [
                            'id', 'created_at', 'updated_at'
                        ],
                        'context' => [
                            'description', 'config'
                        ],
                        'customer' => [
                            'username',
                            'email_verified_at', 'created_at',
                            'updated_at', 'custom_field',
                            'bio', 'dob'
                        ]
                    ]
                )
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => null
                ]);
        }

        $company = User::find($quotation->user_id)->company;

        $external_quotation = \App\Models\Quotation::where([
            ['internal_id', $quotation->id],
            ['hostname_id', tenant()->id],
        ])->first();

        \Log::debug(['$external_quotation' => $external_quotation, 'internal_id' => $quotation->id, 'hostname_id' => tenant()->id]);

        if ($quotation->st === Status::NEW) {
            // send email to company
            Mail::to($company->email)->send(new SendQuotationMail($quotation, $external_quotation));

            $quotation->update([
                'st' => Status::MAILED
            ]);

            return response()->json([
                'message' => 'Quotation sent successfully',
                'status' => Response::HTTP_ACCEPTED
            ], Response::HTTP_ACCEPTED);
        }

        return response()->json([
            'message' => 'Can not send quotation',
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View|Application|JsonResponse
     */

    public function acceptance(
        int $id
    )
    {
        if ($order = $this->quotation->show($id, false)) {
            abort_unless($order->expire_at > Carbon::now(), response('Order is no longer available', Response::HTTP_GONE));

            if (request()->input('q') !== md5(sha1($order->expire_at . env('APP_KEY')))) {
                return response()->json([
                    'message' => __('URL is no longer available'),
                    'status' => Response::HTTP_NOT_FOUND
                ], 404);
            }

            Auth::login($order->orderedBy);
            $order->type = 1;
            $order->st = 302;
            $order->save();
            $order->items->map(function ($item) {
                $item->update([
                    'st' => 309
                ]);
            });
            Auth::logout();

            $setting = Settings::quotationLogo();
            $vat = Settings::vat();
            $logo = !empty($setting) ? FileManager::find($setting) : null;

            if (!empty($logo)) {
                $logo_name = (isset($logo->name)) ? $logo->name : '';
                $logo_local_path = $logo->getImagePath($logo->path, $logo->name);
            } else {
                $logo_name = '';
                $logo_local_path = '';
            }
            $order = QuotationResource::make($order)->additional([
                "setting" => $setting,
                "vat" => $vat,
                "token" => md5(sha1($order->expire_at . env('APP_KEY'))),
                "domain" => request()->domain,
                "logo_path" => optional($logo)->name,
                "local_path" => $logo_local_path ? Storage::disk('tenant')->get($logo_local_path) : ""
            ]);
            return view('pdf.order.accept_qutation', ['order' => $order]);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('orders.not_found'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }

    public function view(int $id)
    {
        $logo_name = '';
        $logo_local_path = '';
        $file = '';
        $vat = Settings::vat();
        $setting = Settings::quotationLogo(null);
        $logo = !empty($setting) ? FileManager::find($setting) : null;
        if ($logo) {
            $logo_name = $logo->name ?? '';
            $logo_local_path = $logo->getImagePath($logo->path, $logo->name);
            $file = optional(Storage::disk('tenant'))->exists($logo_local_path) ?
                optional(Storage::disk('tenant'))->get($logo_local_path) :
                "";
        }

        $OrderRepository = new OrderRepository(Quotation::where('id', $id)->first());

        $order = $OrderRepository->show($id, false);

        if ($order) {
            $order = OrderResource::make($order)->additional([
                "setting" => $setting,
                "vat" => $vat,
                "token" => md5(sha1($order->expire_at . env('APP_KEY'))),
                "domain" => request()->domain,
                "logo_path" => $logo_name,
                "local_path" => $logo_local_path
            ]);
            return view('pdf.order.web_qutation', ['order' => $order]);
        }

        return response()->json([
            'message' => __('orders.not_found'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }

    /**
     * @return array
     */
    private function scopes(): array
    {
        return [
            'language' => new LexiconLanguageScope()
        ];
    }}
