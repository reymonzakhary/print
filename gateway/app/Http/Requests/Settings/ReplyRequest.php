<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Enums\MessageTo;
use App\Enums\MessageType;
use App\Enums\Status;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

final class ReplyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'string|required|max:255|min:3',
            'subject' => 'string|max:255|min:3',
            'body' => 'string|min:3',
            'type' => ['required','string', new Enum(MessageType::class)],
            'st' => 'required_if:type,contract|in:'. Status::ACCEPTED->value.','.Status::REJECTED->value.','.Status::SUSPENDED->value,
            'to' => 'required',
            'contract_id' => 'required',
            'recipient_hostname' => 'required',
            'recipient_email' => 'required',
            'from' => 'required',
            'sender_user_id' => 'integer|nullable',
            'recipient_user_id' => 'integer|nullable',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     * @throws ValidationException
     */
    protected function prepareForValidation(): void
    {
        $from = $this->message->getAttribute('from');
        $whoam = $from === 'sender'? 'recipient' : 'sender';
        $whoto = $from !== 'sender'? 'recipient' : 'sender';
        if(domain()->id === $this->message->getAttribute("{$from}_hostname")) {
            throw ValidationException::withMessages([
                'sender_hostname' => [
                    __('You cannot reply to your own message.')
                ]
            ]);
        }

        $this->merge([
            'from' => $from === 'sender'? 'recipient' : 'sender',
            'subject' => "Re: " . $this->message->getAttribute('subject'),
            'sender_hostname' => $this->message->getAttribute('sender_hostname'),
            'sender_name' => $this->message->getAttribute('sender_name'),
            'sender_email' => $this->message->getAttribute('sender_email'),
            'type' => $this->message->getAttribute('type')?->value,
            'contract_id' => $this->message->getAttribute('contract_id'),
            'to' => $this->message->getAttribute('to')?->value,
            'recipient_hostname' => $this->message->getAttribute('recipient_hostname'),
            'recipient_email' => $this->message->getAttribute('recipient_email'),
            "{$whoam}_user_id" => auth()->id(),
            "{$whoto}_user_id" => $this->message->getAttribute("{$whoto}_user_id"),

        ]);
    }
}
