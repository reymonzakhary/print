<?php

namespace App\Http\Requests\System\Companies;

use App\Foundation\Status\Status;
use App\Models\Contract;
use App\Models\Quotation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;


class StoreQuotationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'suppliers' => 'required|array',
            'suppliers.*' => ['integer', 'bail', 'exists:hostnames,id', function ($attribute, $supplier, $fail) {
                $contract = Contract::select('active')->where([
                    ['receiver_id', $supplier],
                    ['requester_id', auth()->user()->company->id]
                ])->first();

                if (!$contract) {
                    $fail(__("Contract not found with supplier :id.", ['id' => $supplier]));
                }

                if (!$contract?->active) {
                    $fail(__("The supplier :id is not active.", ['id' => $supplier]));
                }

                if (Quotation::where([
                    ['company_id', auth()->user()->company->id],
                    ['hostname_id', $supplier],
                    ['external_id', $this->quotation_id]
                ])->exists()) {
                    $fail(__("You have already sent this quotation to supplier :id", ['id' => $supplier]));
                }

            }],
            'reference' => 'nullable|string|max:100',
            'quotation_id' => 'required|integer',
            'st' => 'required',
            'user_id' => 'integer|exists:users,id',
            'connection' => 'required',
            'author_id' => 'required',
            'created_from' => 'required',
            'internal' => 'required',
            'delivery_pickup' => 'nullable|boolean',
            'delivery_multiple' => [
                'nullable',
                'required_if:delivery_pickup,false'
            ],

            'items.*.calculation_type' => 'required|string',
            'items.*.type' => 'required|string',
            'items.*.internal' => 'required|boolean',
            'items.*.product' => 'required|array',
            'items.*.reference' => 'nullable|string|max:100',
            'items.*.category_id' => 'nullable|string',
            'items.*.category_name' => 'required|string',
            'items.*.category_slug' => 'required|string',
            'items.*.price' => 'required|array',
//            'items.*.product.prices.supplier_id' => 'required|uuid',
            'items.*.price.pm' => 'nullable|string',
            'items.*.price.qty' => 'required|integer',
            'items.*.price.dlv' => 'required|array',
            'items.*.price.dlv.title' => 'nullable|string',
            'items.*.price.dlv.days' => 'required|numeric',
            'items.*.price.p' => 'required',

            'items.*.delivery_separated' => [
                'nullable',
                'required_if:delivery_multiple,true',
                function ($attribute, $value, $fail) {
                    return request()->replace([$attribute => NULL]);
                }
            ],
            'items.addresses' => [
                'array',
                'required_if:items.*.delivery_separated,true',
            ],
            'items.address' => [
                'integer',
                'exists:addresses,id',
                'required_if:items.*.delivery_separated,false',
            ],
            'items.*.files' => 'array|nullable',
            'items.*.files.*.name' => 'string',
            'items.*.files.*.url' => 'string',
            'items.*.files.*.content' => 'nullable|mimetypes:application/pdf'
        ];
    }

    protected function prepareForValidation()
    {
        $items = [];
        collect(collect($this->all())->get('items'))->each(function ($item) use (&$items){
            $items[] = array_merge([
                "category_slug" => Str::slug(optional($item)["category_name"]),
                "calculation_type"  =>  "open_product",
                "type" =>  "print",
                'internal' => true,
            ], $item);
        });

        $this->merge([
            'items' => $items,
            'user_id' => auth()->id(),
            'connection' => 'cec',
            'st' => Status::NEW,
            'internal' => false,
            'author_id' => 1,
            'created_from' => 'api'
        ]);
    }

}
