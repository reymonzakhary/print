<?php

namespace App\Http\Requests\System\Messages;

use App\Enums\MessageType;
use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class ReplyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'string|required|max:255|min:3',
            'subject' => 'string|max:255|min:3',
            'body' => 'string|min:3',
            'type' => ['required','string', new Enum(MessageType::class)],
            'st' => 'required|in:'. Status::ACCEPTED->value.','.Status::REJECTED->value.','.Status::SUSPENDED->value,
            'to' => 'required',
            'contract_id' => 'required',
            'recipient_hostname' => 'nullable|string|max:255',
            'recipient_email' => 'nullable|string|max:255',
            'sender_hostname' => 'nullable|max:255',
            'sender_name' => 'nullable|string|max:255',
            'from' => 'required',
            'sender_user_id' => 'integer|nullable',
            'recipient_user_id' => 'integer|nullable',
            'contract_data' => [
                Rule::requiredIf(function ()  {
                    return $this->type === MessageType::PRODUCER->value && $this->st === Status::ACCEPTED->value;
                }),
                'array'
            ],
            'contract_data.payment_terms' => [
                Rule::requiredIf(function () {
                    return $this->type === MessageType::PRODUCER->value && $this->st === Status::ACCEPTED->value;
                }),
                'string'
            ],
            'contract_data.runs' => [
                Rule::requiredIf(function () {
                    return $this->type === MessageType::PRODUCER->value && $this->st === Status::ACCEPTED->value;
                }),
                'array'
            ],
            'contract_data.runs.*.from' => [
                Rule::requiredIf(function () {
                    return $this->type === MessageType::PRODUCER->value && $this->st === Status::ACCEPTED->value;
                }),
                'numeric'
            ],
            'contract_data.runs.*.to' => [
                Rule::requiredIf(function () {
                    return $this->type === MessageType::PRODUCER->value && $this->st === Status::ACCEPTED->value;
                }),
                'numeric'
            ],
            'contract_data.runs.*.percentage' => [
                Rule::requiredIf(function () {
                    return $this->type === MessageType::PRODUCER->value && $this->st === Status::ACCEPTED->value;
                }),
                'numeric',
                'between:0,100'
            ],
            'can_request_quotation' => [
                Rule::requiredIf(function ()  {
                    return $this->type === MessageType::PRODUCER->value && $this->st === Status::ACCEPTED->value;
                }),
                'boolean'
            ],
        ];
    }

    /**
     * Get the validation messages for defined rules.
     *
     * This method provides a list of error messages for validation rules
     * applied to various fields, ensuring that descriptive validation messages
     * are returned to the user. Each key in the returned array corresponds to
     * a validation rule for specific fields in the request data, and the value
     * provides the associated error message.
     *
     * @return array The array of validation messages.
     */
    public function messages()
    {
        return [
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a text.',
            'title.max' => 'The title must not exceed 255 characters.',
            'title.min' => 'The title must be at least 3 characters.',
            'subject.string' => 'The subject must be a text.',
            'subject.max' => 'The subject must not exceed 255 characters.',
            'subject.min' => 'The subject must be at least 3 characters.',
            'body.string' => 'The body must be a text.',
            'body.min' => 'The body must be at least 3 characters.',
            'type.required' => 'The type field is required.',
            'type.string' => 'The type must be a text.',
            'st.required' => 'The status field is required.',
            'st.in' => 'The selected status is invalid.',
            'to.required' => 'The recipient field is required.',
            'contract_id.required' => 'The contract ID is required.',
            'from.required' => 'The sender field is required.',
            'recipient_hostname.max' => 'The recipient hostname must not exceed 255 characters.',
            'recipient_email.max' => 'The recipient email must not exceed 255 characters.',
            'sender_hostname.max' => 'The sender hostname must not exceed 255 characters.',
            'sender_name.max' => 'The sender name must not exceed 255 characters.',
            'sender_user_id.integer' => 'The sender user ID must be a number.',
            'recipient_user_id.integer' => 'The recipient user ID must be a number.',
            'contract_data.required' => 'The contract data is required.',
            'contract_data.array' => 'The contract data must be an array.',
            'contract_data.payment_terms.required' => 'The payment terms are required.',
            'contract_data.payment_terms.string' => 'The payment terms must be a text.',
            'contract_data.runs.required' => 'The runs data is required.',
            'contract_data.runs.array' => 'The runs must be an array.',
            'contract_data.runs.*.from.required' => 'The from value is required for each run.',
            'contract_data.runs.*.from.numeric' => 'The from value must be a number.',
            'contract_data.runs.*.to.required' => 'The to value is required for each run.',
            'contract_data.runs.*.to.numeric' => 'The to value must be a number.',
            'contract_data.runs.*.percentage.required' => 'The percentage is required for each run.',
            'contract_data.runs.*.percentage.numeric' => 'The percentage must be a number.',
            'contract_data.runs.*.percentage.between' => 'The percentage must be between 0 and 100.',
            'can_request_quotation.required' => 'The quotation request permission is required.',
            'can_request_quotation.boolean' => 'The quotation request permission must be true or false.'
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
        $this->merge([
            'from' => $from === 'sender'? 'recipient' : 'sender',
            'subject' => "Re: " . $this->message->getAttribute('subject'),
            'sender_hostname' => $this->message->getAttribute('sender_hostname'),
            'sender_name' => 'Prindustry',
            'sender_email' => 'info@prindustry.com',
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
