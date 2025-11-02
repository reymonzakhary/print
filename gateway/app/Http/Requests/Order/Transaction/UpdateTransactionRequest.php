<?php

declare(strict_types=1);

namespace App\Http\Requests\Order\Transaction;

use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

final class UpdateTransactionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'st' => [
                'sometimes',
                'integer',
                new Enum(Status::class)
            ],

            'payment_method' => 'sometimes|string',
        ];
    }

    /**
     * @throws ValidationException
     */
    protected function passedValidation(): void
    {
        if ($this->has('st')) {
            $statusEnum = Status::from($this->input('st'));

            $this->checkStatusInput($statusEnum);
        }
    }

    /**
     * Check if the status (a.k. `st`) input is valid
     *
     * @param Status $status
     *
     * @return void
     *
     * @throws ValidationException
     */
    private function checkStatusInput(Status $status): void
    {
        if ($status === Status::DRAFT) {
            throw ValidationException::withMessages([
                'st' => __('You cannot change the status to Draft.')
            ]);
        }
    }
}
