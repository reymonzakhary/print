<?php

namespace App\Http\Controllers\System\V2\Mgr\Messages;

use App\Enums\MessageType;
use App\Enums\Status;
use App\Events\Messages\CrossTenantMessage;
use App\Foundation\ContractManager\Facades\ContractManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\System\Messages\ReplyRequest;
use App\Http\Resources\Settings\MessageResource;
use App\Models\Hostname;
use App\Models\Message;
use Hyn\Tenancy\Environment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    /**
     * Retrieve a collection of messages where the recipient is 'cec'.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return MessageResource::collection(
            Message::tree()->breadthFirst()->where('to', 'cec')->with('contract')->get()
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Show a message resource with additional data.
     *
     * @param Message $message The message to display.
     * @return MessageResource The message resource with additional data.
     */
    public function show(
        Message $message
    ): MessageResource
    {
        $message->markAsRead();
        return MessageResource::make(
            $message
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Reply to a message and handle contract updates and cross-tenant messaging.
     *
     * @param Message $message The message to reply to.
     * @param ReplyRequest $request The request containing reply details.
     * @return JsonResponse|MessageResource JSON response with message or MessageResource
     */
    public function reply(
        Message $message,
        ReplyRequest $request
    ): JsonResponse|MessageResource
    {
        $targetTenant = Hostname::query()
            ->with('website')
            ->where('id', $request->input("sender_hostname"))->first();

        if (!$targetTenant) {
            return response()->json([
                'message' => __('Recipient of the message does not exist'),
                'status' => Response::HTTP_NOT_FOUND,
            ], Response::HTTP_NOT_FOUND);
        }
        $reply = $message->children()->create($request->only([
            'title', 'subject', 'body', 'contract_id', 'type', 'to', 'recipient_hostname', 'recipient_email',
            'sender_name', 'sender_email', 'sender_hostname', 'from', 'sender_user_id', 'recipient_user_id'
        ]));

        $this->updateContractIfNeeded($request, $message);
        $this->handleCrossTenantMessaging($targetTenant, $request, $reply);

        return MessageResource::make($reply)
            ->additional([
                'message' => __('Reply has been sent successfully.'),
                'status' => Response::HTTP_CREATED
            ]);
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
        $contract_id = $message->contract_id;
        if ($request->input('type') === MessageType::PRODUCER->value  &&  in_array($message->contract->st , [Status::REJECTED->value , Status::ACCEPTED->value , Status::SUSPENDED->value , Status::PENDING->value])) {
            if ($request->st === Status::ACCEPTED->value) {

                ContractManager::acceptSupplierContract($contract_id ,$request->contract_data ,$request->can_request_quotation);
                $message->contract->requester->website->update([
                    'supplier' => true
                ]);
            } else{
                ContractManager::update($contract_id , [
                    'st' => $request->st,
                    'active' => false,
                    'activated_at' => null
                ]);
                $message->contract->requester->website->update([
                    'supplier' => false
                ]);
            }
        }else{
            ContractManager::update($contract_id , [
                'st' => $request->st
            ]);

        }
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
    }
}
