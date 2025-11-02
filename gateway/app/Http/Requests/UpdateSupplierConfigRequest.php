<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierConfigRequest extends FormRequest
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
            'through' => 'required|array',
            'through.mail' => 'required|boolean',
            'through.ftp' => 'required|boolean',

            "mail.smtp" => 'required_if:through.mail,true|boolean',
            "mail.smtp_auth" => 'required_if:through.mail,true|boolean',
            "mail.smtp_secure" => 'required_if:through.mail,true|boolean',
            "mail.host" => 'required_if:through.mail,true',
            'mail.port' => 'required_if:through.mail,true|numeric',
            'mail.to' => 'required_if:through.mail,true|email',
            'mail.username' => 'required_if:through.mail,true',
            'mail.password' => 'required_if:through.mail,true',

            "ftp.sftp" => 'required_if:through.ftp,true|boolean',
            'ftp.host' => 'required_if:through.ftp,true|string',
            'ftp.username' => 'required_if:through.ftp,true|string',
            'ftp.password' => 'required_if:through.ftp,true',
            'ftp.port' => 'required_if:through.ftp,true|numeric',
            'ftp.path' => 'required_if:through.ftp,true|string',

            "orders" => 'array',
            "orders.collect" => 'in:item,order,reseller',
            "orders.status" => 'string',
            "orders.when" => 'in:status,default',
            "orders.from" => 'nullable|date_format:H:i',
            "orders.to" => 'nullable|date_format:H:i',

            "order_statuses" => 'array',
            "order_statuses.automation" => 'boolean',
            "order_statuses.steps" => 'nullable|array',
            "order_statuses.steps.*" => 'required_if:order_statuses.automation,true|array',
            "order_statuses.steps.*.status" => 'required_if:order_statuses.automation,true|integer|exists:statuses,code',
            "order_statuses.steps.*.dynamic" => 'required_if:order_statuses.automation,true|boolean',
            "order_statuses.steps.*.cond_from" => 'required_if:order_statuses.steps.*.dynamic,true|string',
            "order_statuses.steps.*.cond" => 'required_if:order_statuses.steps.*.dynamic,true|array',
            "order_statuses.steps.*.cond.operator" => 'required|in:-,+',
            "order_statuses.steps.*.cond.int" => 'required|integer',
            "order_statuses.steps.*.cond.type" => 'required|in:minutes,days,hours',
            "order_statuses.steps.*.after" => 'required_if:order_statuses.steps.*.dynamic,false|nullable|numeric',
            "order_statuses.steps.*.to" => 'required_if:order_statuses.automation,true||numeric',
        ];
    }
}
