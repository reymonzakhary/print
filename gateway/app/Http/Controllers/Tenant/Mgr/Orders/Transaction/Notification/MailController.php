<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Orders\Transaction\Notification;

use App\Enums\Status;
use App\Enums\Transaction\TransactionLexiconTag;
use App\Facades\Settings;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\Transaction\SendTransactionMailRequest;
use App\Http\Resources\Lexcons\LexiconResource;
use App\Jobs\Tenant\Order\Transaction\SendTransactionMailJob;
use App\Models\Tenants\Order;
use App\Models\Tenants\Transaction;
use App\Repositories\LexiconRepository;
use App\Scoping\Scopes\Lexicons\LexiconLanguageScope;
use App\Utilities\Order\Transaction\Lexicon\LexiconStaticResolver;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class MailController extends Controller
{
    public function __construct(
        private readonly LexiconRepository $lexiconRepository
    ) {
    }

    /**
     * @param Order $order
     * @param Transaction $transaction
     *
     * @return mixed
     */
    public function show(
        Order       $order,
        Transaction $transaction,
    ): mixed
    {
        return LexiconResource::collection(
            $this->getLexiconDefaultTemplates()
        )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null,
                'meta' => [
                    'tags' => TransactionLexiconTag::getFormatted()
                ],
            ]);
    }

    /**
     * @param SendTransactionMailRequest $request
     * @param Order $order
     * @param Transaction $transaction
     * @param LexiconStaticResolver $lexiconStaticResolver
     *
     * @return JsonResponse
     */
    public function send(
        SendTransactionMailRequest $request,
        Order                      $order,
        Transaction                $transaction,
        LexiconStaticResolver      $lexiconStaticResolver,
    ): JsonResponse
    {
        $lexiconDefaultTemplates = $this->getLexiconDefaultTemplates();

        $subjectTemplate = $request->input(
            'subject',
            $lexiconDefaultTemplates->firstWhere('template', 'invoice.subject')?->getAttribute('value')
        );

        $bodyTemplate = $request->input(
            'body',
            $lexiconDefaultTemplates->firstWhere('template', 'invoice.body')?->getAttribute('value')
        );

        if (!$subjectTemplate || !$bodyTemplate) {
            return response()->json([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => __('The subject or body template is missing.'),
            ]);
        }

        $mailQueue = $transaction->mailQueues()->create([
            'st' => Status::NEW,

            'from' => Settings::mailSmtpFrom()->value,
            'to' => $order->orderedBy()->firstOrFail()->getAttribute('email'),

            'subject' => $lexiconStaticResolver->resolveText($subjectTemplate, $transaction),
            'message' => $lexiconStaticResolver->resolveText($bodyTemplate, $transaction)
        ]);

        SendTransactionMailJob::dispatch(
            $request->tenant,
            $request->user(),
            $transaction,
            $mailQueue,
            $request->input('language'),
        );

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => __('Email sending has been scheduled successfully.'),
        ]);
    }

    /**
     * @return Collection
     */
    private function getLexiconDefaultTemplates(): Collection
    {
        return $this->lexiconRepository->template('mail.invoice', $this->getScopes());
    }

    /**
     * @return array
     */
    private function getScopes(): array
    {
        return [
            'language' => new LexiconLanguageScope()
        ];
    }
}
