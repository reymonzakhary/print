<?php

namespace App\Http\Controllers\Tenant\Mgr\Orders\Notifications;

use App\Events\Tenant\Order\MailQuotationEvent;
use App\Foundation\Settings\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Lexcons\LexiconResource;
use App\Http\Resources\Orders\OrderResource;
use App\Models\Tenants\Lexicon;
use App\Models\Tenants\MailQueue;
use App\Models\Tenants\Order;
use App\Repositories\LexiconRepository;
use App\Repositories\OrderRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class MailController extends Controller
{

    /**
     * @var OrderRepository
     */
    protected OrderRepository $order;
    /**
     * @var LexiconRepository
     */
    protected LexiconRepository $lexicon;
    /**
     * default hiding field from response
     */
    protected array $hide;

    /**
     * default total result in one page
     */
    protected int $per_page = 15;

    protected bool $type = false;


    /**
     * UserController constructor.
     * @param Request $request
     * @param Order   $order
     * @param Lexicon $lexicon
     */
    public function __construct(
        Request $request,
        Order   $order,
        Lexicon $lexicon
    )
    {
        $this->order = new OrderRepository($order);
        $this->lexicon = new LexiconRepository($lexicon);

        /**
         * default number of pages
         */
        $this->per_page = $request->get('per_page') ?? $this->per_page;
        $this->hide[] = $request->get('include_items') ?? 'items';
    }

    public function show(
        int $order_id,
        string $template
    )
    {
        return LexiconResource::collection(
            $this->lexicon->template($template)
        )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => __('orders.no_order_available')
            ]);
    }


    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int     $id
     * @return OrderResource|JsonResponse
     */
    public function email(
        Request $request,
        int     $id
    )
    {
        $expiresAfter = Settings::quotationExpiresAfter();
        if ($order = $this->order->show($id, false)) {

            if ($order->st === 300 && $order->orderedBy) {
                $order->update([
                    'st' => 301,
                    'expire_at' => Carbon::now()->addDays((int)$expiresAfter)
                ]);
                collect($order->items()->get())->map(function ($item) {
                    $item->update([
                        'st' => 309
                    ]);
                });
//                $message = json_encode(['subject'=> $request->subject,'body'=> $request->body]);
                $message = json_encode(['subject' => "offerte", 'body' => "body"]);
                $mailQueue = MailQueue::create(['model_id' => $order->id, 'model' => get_class($order), 'st' => 300, 'message' => $message]);
                event(new MailQuotationEvent($mailQueue, request()->tenant->uuid, request()->domain));
            } elseif ($order->st === 301 && $order->orderedBy) {
                // resend offer
                $message = json_encode(['subject' => $request->subject, 'body' => $request->body]);
                $mailQueue = MailQueue::create(['model_id' => $order->id, 'model' => get_class($order), 'st' => 300, 'message' => $message]);
                event(new MailQuotationEvent($mailQueue, request()->tenant->uuid, request()->domain));
                return OrderResource::make($order)
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

            //event(new OrderNotificationEmailEvent($order, auth()->user()));

            return OrderResource::make($order)
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

        /**
         * error response
         */
        return response()->json([
            'message' => __('orders.not_found'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);

    }

    /**
     * Display the specified resource.
     * @param int $id
     * @return JsonResponse
     */
    public function acceptance(
        int $id
    )
    {
        if ($order = $this->order->show($id)) {
            abort_unless($order->expire_at > Carbon::now(), response('Order is no longer available', Response::HTTP_GONE));
            $order->type = 1;
            $order->st = 302;
            $order->save();
            $order->items()->map(function ($item) {
                $item->update([
                    'st' => 309
                ]);
            });
            return response()->json([
                'message' => __('orders.accepted'),
                'status' => Response::HTTP_ACCEPTED
            ], Response::HTTP_ACCEPTED);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('orders.not_found'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);

    }
}
