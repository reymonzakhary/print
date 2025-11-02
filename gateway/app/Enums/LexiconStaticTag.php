<?php

declare(strict_types=1);

namespace App\Enums;

use App\Models\Tenants\Quotation;
use EmreYarligan\EnumConcern\EnumConcern;

enum LexiconStaticTag: string
{
    use EnumConcern;

    case QUOTATION_ID = 'quotation.id';
    // case QUOTATION_EXTERNAL_ID = 'quotation.external_id';
    case QUOTATION_REFERENCE = 'quotation.reference';
    case QUOTATION_PRICE = 'quotation.price';
    case QUOTATION_EXPIRE_AT = 'quotation.expire_at';
    case QUOTATION_STATUS_NAME = 'quotation.status.name';
    case QUOTATION_STATUS_CODE = 'quotation.status.code';
    // case QUOTATION_DELIVERY_TYPE = 'quotation.delivery_type';
    // case QUOTATION_DELIVERY_METHOD = 'quotation.delivery_method';
    case QUOTATION_DELIVERY_ADDRESS = 'quotation.delivery_address';
    case QUOTATION_INVOICE_ADDRESS = 'quotation.invoice_address';
    case QUOTATION_CONTEXT = 'quotation.context';
    case QUOTATION_VAT_PRICE = 'quotation.vat_price';
    case CUSTOMER_FULL_NAME = 'customer.full_name';
    case CUSTOMER_FIRST_NAME = 'customer.first_name';
    case CUSTOMER_LAST_NAME = 'customer.last_name';
    case CUSTOMER_GENDER = 'customer.gender';
    case CUSTOMER_EMAIL = 'customer.email';
    case QUOTATION_CREATED_FROM = 'quotation.created_from';
    
    public function render(Quotation $quotation): mixed
    {
        $resolvedTemplate = match ($this) {
            self::QUOTATION_ID => $quotation->getAttribute('id'),
            // self::QUOTATION_EXTERNAL_ID => $quotation->getAttribute('external_id'),
            self::QUOTATION_REFERENCE => $quotation->getAttribute('reference'),
            self::QUOTATION_PRICE => $quotation->getFormattedPriceAttribute(),
            self::QUOTATION_EXPIRE_AT => $quotation->getAttribute('expire_at'),
            self::QUOTATION_STATUS_NAME => Status::from($quotation->getAttribute('st'))->name,
            self::QUOTATION_STATUS_CODE => $quotation->getAttribute('st'),
            // self::QUOTATION_DELIVERY_TYPE => $quotation->getAttribute('delivery_type'), //here
            // self::QUOTATION_DELIVERY_METHOD => $quotation->getAttribute('delivery_method'),//here
            self::QUOTATION_DELIVERY_ADDRESS => $quotation->formatAddressFromRelation('delivery_address'),
            self::QUOTATION_INVOICE_ADDRESS => $quotation->formatAddressFromRelation('invoice_address'),
            self::QUOTATION_CONTEXT => $quotation->context->getAttribute('name'),
            self::QUOTATION_VAT_PRICE => $quotation->getFormattedVatAttribute(),
            self::CUSTOMER_FULL_NAME => $quotation->orderedBy->profile->getAttribute('first_name'),
            self::CUSTOMER_FIRST_NAME => $quotation->orderedBy->profile->getAttribute('last_name'),
            self::CUSTOMER_LAST_NAME => $quotation->orderedBy->fullName(),
            self::CUSTOMER_GENDER => $quotation->orderedBy->profile->getAttribute('gender'),
            self::CUSTOMER_EMAIL => $quotation->orderedBy->getAttribute('email'),
            self::QUOTATION_CREATED_FROM => $quotation->getAttribute('created_from'),
        };

        return $resolvedTemplate;
    }

    public static function getAllFormatted(): array
    {
        return self::all()->map(function (string $value) {
            return sprintf('[[%%%s]]', $value);
        })->values()->toArray();
    }

    public static function resolveOrFallback(string $tag, Quotation $quotation): string
    {
        $enum = self::tryFrom($tag);

        if (!$enum) {
            return '{{EMPTY-VALUE}}';
        }

        $value = $enum->render($quotation);

        return ($value === null || $value === '') ? '' : e($value);
    }

}
