<?php

declare(strict_types=1);

namespace App\Enums\Transaction;

use EmreYarligan\EnumConcern\EnumConcern;

enum TransactionLexiconTag: string
{
    use EnumConcern;

    ## Transaction Tags (Dynamic) ##
    case INVOICE_NUMBER = 'invoice_number';
    case INVOICE_DATE = 'invoice_date';
    case DUE_DATE = 'due_date';
    case TYPE = 'type';
    case COUNTER = 'counter';
    case LEVEL = 'level';
    case PAYMENT_METHOD = 'payment_method';
    case ORDER_ID = 'order_id';
    case COMPANY_ID = 'company_id';
    case TEAM_ID = 'team_id';
    case CONTRACT_ID = 'contract_id';
    case PARENT_ID = 'parent_id';
    case DISCOUNT_ID = 'discount_id';

    ## Transaction Tags (Static) ##
    case PAYMENT_STATUS = 'payment_status';
    case CUSTOMER_ID = 'customer_id';

    ## Customer Relation Tags (Static) ##
    case CUSTOMER_FULL_NAME = 'customer.full_name';
    case CUSTOMER_EMAIL = 'customer.email';

    /**
     * Get all tags formatted
     *
     * @return array
     */
    public static function getFormatted(): array
    {
        return self::all()
            ->map(
                static function (string $value) {
                    return sprintf('[[%%%s]]', $value);
                }
            )
            ->values()
            ->toArray();
    }

    /**
     * Instantiate from a formatted value (a.k. [[%transaction.invoice_number]])
     *
     * @param string $value
     *
     * @return self|null
     */
    public static function tryFromFormatted(string $value): self|null
    {
        $rawValue = str_replace('transaction.', '', $value);

        return self::tryFrom($rawValue);
    }

    /**
     * @return bool
     */
    public function doesNameMatchModelAttribute(): bool
    {
        return match ($this) {
            TransactionLexiconTag::INVOICE_NUMBER,
            TransactionLexiconTag::INVOICE_DATE,
            TransactionLexiconTag::DUE_DATE,
            TransactionLexiconTag::TYPE,
            TransactionLexiconTag::COUNTER,
            TransactionLexiconTag::LEVEL,
            TransactionLexiconTag::PAYMENT_METHOD,
            TransactionLexiconTag::ORDER_ID,
            TransactionLexiconTag::COMPANY_ID,
            TransactionLexiconTag::TEAM_ID,
            TransactionLexiconTag::CONTRACT_ID,
            TransactionLexiconTag::PARENT_ID,
            TransactionLexiconTag::DISCOUNT_ID => true,

            default => false
        };
    }
}
