<?php

declare(strict_types=1);

namespace App\Utilities\Order\Transaction\Lexicon;

use App\Enums\Transaction\TransactionLexiconTag;
use App\Models\Tenants\Transaction;
use LogicException;

/**
 * A service class that can be used to render transaction lexicon template
 */
final readonly class LexiconStaticResolver
{
    public function resolveText(
        string      $text,
        Transaction $transaction
    ): string
    {
        $textWithLineBreaks = str_replace(["\n", "\r\n"], "<br>", $text);

        return preg_replace_callback('/\[\[%([^]]+)]]/',
            function (array $matches) use ($transaction) {
                $tag = TransactionLexiconTag::tryFromFormatted($matches[1]);

                return $tag ? $this->renderTag($tag, $transaction) : $matches[0];
            },

            $textWithLineBreaks
        );
    }

    /**
     * @throws LogicException
     */
    public function renderTag(
        TransactionLexiconTag $tag,
        Transaction           $transaction
    ): string|int
    {
        return match (true) {
            ## Transaction Tags (Dynamic) ##
            $tag->doesNameMatchModelAttribute() => $this->fetchTransactionAttributeDynamically($tag->name, $transaction),

            ## Transaction Tags (Static) ##
            $tag === TransactionLexiconTag::PAYMENT_STATUS => $transaction->getAttribute('st'),
            $tag === TransactionLexiconTag::CUSTOMER_ID => $transaction->getAttribute('user_id'),

            ## Customer Relation Tags (Static) ##
            $tag === TransactionLexiconTag::CUSTOMER_FULL_NAME => $transaction->user()->firstOrFail()->fullname(),
            $tag === TransactionLexiconTag::CUSTOMER_EMAIL => $transaction->user()->firstOrFail()->getAttribute('email'),

            default => throw new LogicException(
                sprintf('Unknown transaction tag "%s"', $tag->name)
            )
        };
    }

    /**
     * Fetch a specific transaction attribute dynamically
     *
     * @param string $name
     * @param Transaction $transaction
     *
     * @return string|int
     */
    private function fetchTransactionAttributeDynamically(
        string      $name,
        Transaction $transaction
    ): string|int
    {
        if (!array_key_exists($name, $transaction->getAttributes())) {
            throw new LogicException(
                sprintf('Unknown transaction attribute "%s"', $name)
            );
        }

        return $transaction->getAttribute($name);
    }
}
