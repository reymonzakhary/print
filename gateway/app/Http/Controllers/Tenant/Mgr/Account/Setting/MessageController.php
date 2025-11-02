<?php

namespace App\Http\Controllers\Tenant\Mgr\Account\Setting;

use App\Enums\MessageType;
use App\Enums\Status;
use App\Events\Messages\CrossTenantMessage;
use App\Foundation\ContractManager\Facades\ContractManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ReplyRequest;
use App\Http\Requests\Settings\SendEmailRequest;
use App\Http\Resources\Settings\MessageResource;
use App\Jobs\Tenant\Setting\RequestContract;
use App\Jobs\Tenant\Setting\RequestMessage;
use App\Jobs\Tenant\Setting\RequestSystemContract;
use App\Models\Hostname;
use App\Models\Message;
use App\Models\Website;
use Carbon\Carbon;
use Hyn\Tenancy\Environment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{

    /**
     * Retrieve all messages as a collection of MessageResource objects.
     *
     * @return AnonymousResourceCollection The JSON response containing the MessageResource collection.
     */
    public function index(): AnonymousResourceCollection
    {
        $type = request()->input('type')??'recipient';
        return MessageResource::collection(
            Message::tree()->breadthFirst()->where("{$type}_hostname", hostname()->id)->with('contract')->get()
        )
            ->additional([
                'messages' => null,
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * Mark a message as read and return it as a resource in JSON format.
     *
     * @param Message $message The message object to display.
     *
     * @return JsonResource The JSON resource representing the message.
     */
    public function show(
        Message $message
    ): JsonResource
    {
        $message->markAsRead();
        return MessageResource::make(
            $message
        )
            ->additional([
                'messages' => null,
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * Store a new message based on the request type.
     *
     * @param SendEmailRequest $request The email request object.
     *
     * @return JsonResponse The JSON response.
     */
    public function store(
        SendEmailRequest $request
    ): JsonResponse
    {
        match ($request->type) {
          MessageType::CONTRACT->value => Bus::chain([
              RequestContract::dispatch($request),
              RequestMessage::dispatch($request),
          ]),

          default => Bus::chain([
              RequestSystemContract::dispatch($request, tenant(),  hostname()),
              RequestMessage::dispatch($request)
          ])
        };

        return response()->json([
            'message' => __('Message sent successfully.'),
            'status' => Response::HTTP_CREATED
        ]);
    }

    /**
     * Reply to a message based on the request data.
     *
     * @param Message $message The message object being replied to.
     * @param ReplyRequest $request The reply request object containing reply details.
     *
     * @return MessageResource|JsonResponse
     */
    public function reply(
        Message $message,
        ReplyRequest $request
    ): JsonResponse|MessageResource
    {
        $targetTenant = $this->getTargetTenant($request);
        if (!$targetTenant) {
            return response()->json([
                'message' => __('Recipient of the message does not exist'),
                'status' => Response::HTTP_NOT_FOUND,
            ], Response::HTTP_NOT_FOUND);
        }
        $reply = $this->createReply($message, $request);
        $this->updateContractIfNeeded($request, $message);
        $this->handleCrossTenantMessaging($targetTenant, $request, $reply);

        return MessageResource::make($reply)
            ->additional([
                'message' => __('Reply has been sent successfully.'),
                'status' => Response::HTTP_CREATED
            ]);
    }

    /**
     * Get the target tenant based on the request.
     *
     * @param Request $request The request object containing 'from' and 'sender'/'recipient' information.
     *
     * @return Builder|Model The query builder for the tenant or null if not found.
     */
    private function getTargetTenant(
        Request $request
    ): Builder|Model|null
    {
        $broadcast = $request->from === 'sender' ? 'recipient' : 'sender';
        return Hostname::query()
            ->with('website')
            ->where('id', $request->input("{$broadcast}_hostname"))->first();
    }

    /**
     * Create a reply message.
     *
     * @param Message $message The parent message.
     * @param Request $request The request object containing data to create the reply.
     *
     * @return Model The created reply message.
     */
    private function createReply(
        Message $message,
        Request $request
    ): Model
    {
        return $message->children()->create($request->only([
            'title', 'subject', 'body', 'contract_id', 'type', 'to', 'recipient_hostname', 'recipient_email',
            'sender_name', 'sender_email', 'sender_hostname', 'from'
        ]));
    }

    /**
     * Update contract information if needed based on the request and message.
     *
     * @param Request $request The request object containing updates.
     * @param Message $message The message object to update contract for.
     */
    private function updateContractIfNeeded(
        Request $request,
        Message $message
    ): void
    {
        if ($request->input('type') === MessageType::CONTRACT->value && $message->contract->st === Status::PENDING->value) {
            $data = [
                        'st' => $request->st,
                        'active' => $request->st === Status::ACCEPTED->value,
                        'activated_at' => Carbon::now()->toDateTimeString()
                    ];
        }else{
            $data = [
                'st' => $request->st
            ];
        }
        ContractManager::update($message->contract()->first(), $data);
    }

    /**
     * Handle cross-tenant messaging by switching the tenant environment and sending the message.
     *
     * @param Model $targetTenant The target tenant model.
     * @param Request $request The request object.
     * @param Message|Model $reply The message or model for the reply.
     *
     * @return void
     */
    private function handleCrossTenantMessaging(
        Model $targetTenant,
        Request $request,
        Message|Model $reply
    ): void
    {
        app(Environment::class)->tenant($targetTenant->website);
        switchSupplierWebsocket($targetTenant->website->getAttribute('uuid'));
        event(new CrossTenantMessage($reply));
        app(Environment::class)->tenant($request->tenant);
        switchSupplierWebsocket($request->tenant->getAttribute('uuid'));
    }
}
