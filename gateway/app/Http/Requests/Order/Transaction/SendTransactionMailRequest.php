<?php

declare(strict_types=1);

namespace App\Http\Requests\Order\Transaction;

use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

final class SendTransactionMailRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'language' => 'required|string|exists:languages,iso',
            'subject' => 'sometimes|string',
            'body' => 'sometimes|string',
        ];
    }

    /**
     * @return void
     *
     * @throws ValidationException
     */
    protected function passedValidation(): void
    {
        $this->checkIfTransactionCanBeMailed();
    }

    /**
     * @throws ValidationException
     */
    private function checkIfTransactionCanBeMailed(): void
    {
        $transaction = $this->route('transaction');

        if ($transaction->getAttribute('st') === Status::DRAFT->value) {
            throw ValidationException::withMessages([
                'transaction' => 'Draft transactions cannot be mailed to the customer.'
            ]);
        }
    }
}
