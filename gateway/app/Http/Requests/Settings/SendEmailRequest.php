<?php

namespace App\Http\Requests\Settings;

use App\Enums\MessageTo;
use App\Enums\MessageType;
use App\Facades\Plugins;
use App\Models\Domain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SendEmailRequest extends FormRequest
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
            'to' => ['required','string', new Enum(MessageTo::class)],
            'recipient_hostname' => 'required_if:to,supplier|integer|nullable',
            'recipient_email' => 'required_if:to,supplier|string|email|nullable',
//            'credentials' => 'required_if:type,contract|array|nullable',
//            'credentials.*.user_name' => 'required_if:type,contract|string|nullable',
//            'credentials.*.password' => 'required_if:type,contract|string|nullable',
//            'credentials.*.token' => 'required_if:type,contract|string|nullable',
            'sender_hostname' => 'required',
            'sender_name' => 'required',
            'sender_email' => 'required',
            'from' => 'string|required',
            'sender_user_id' => 'integer|nullable',
            'recipient_user_id' => 'integer|nullable',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'from' => 'sender',
            'sender_hostname' => \domain()->id,
            'sender_name' => auth()->user()->profile->fullName,
            'sender_email' => auth()->user()->email,
            'to' => $this->type === 'producer' ? MessageTo::CEC->value : MessageTo::SUPPLIER->value,
            'recipient_hostname' => $this->type === 'producer' ? null : $this->recipient_hostname,
            'sender_user_id' => auth()->id(),
        ]);

        if ($this->type === MessageType::CONTRACT->value) {

            if ($hostname = Domain::with('website')->where('id', $this->recipient_hostname)->first()) {
                $configure = collect($hostname->website->configure);
                if ($configure->has('auth') && $hostname->website->external) {
                    $this->validate($configure->get('auth'), $this->all());
                    $plugin = Plugins::load($hostname);
                    $plugin->auth(collect($configure->get('auth'))->map(fn($v, $k) => $this->{$k})->merge([
                        'tenant_id' => tenant()->uuid,
                    ])->toArray());
                }
            }
        }
    }
}
