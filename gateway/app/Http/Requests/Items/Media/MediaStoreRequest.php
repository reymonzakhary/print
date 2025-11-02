<?php

namespace App\Http\Requests\Items\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class MediaStoreRequest extends FormRequest
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
            'files' => 'required|array',
        ];
    }

    /**
     *
     * @return void
     * @throws ValidationException
     */
    public function prepareForValidation(): void
    {
        $model = $this->quotation? 'quotations': 'orders';

        if (!auth()->user()->can($model.'-items-media-create') && $this->has('files')) {
            throw ValidationException::withMessages([
                $model.'_items_media_create' => __('Not permitted action.')
            ]);
        }

    }
}
