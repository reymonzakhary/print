<?php

declare(strict_types=1);

namespace App\Utilities\Quotation;

use App\Models\Tenants\Item;
use App\Models\Tenants\Quotation;
use Illuminate\Contracts\Hashing\Hasher;
use Psr\Log\LoggerInterface;

final readonly class QuotationHasher
{
    public function __construct(
//        private LoggerInterface $logger,
        private Hasher $hasher
    ) {}

    /**
     * Generate a hash for a given model
     *
     * @param Quotation $quotation
     *
     * @return string
     */
    public function generate(
        Quotation $quotation
    ): string
    {
        $extractedAttributes = $this->extractAttributesFromQuotation($quotation);
        $generatedHash = $this->hasher->make(serialize($extractedAttributes));

//        $this->logger->debug(
//            'Quotation hash generated', [
//                'quotation_id' => $quotation->getAttribute('id'),
//                'generated_hash' => $generatedHash,
//                'extracted_attributes' => $extractedAttributes,
//            ]
//        );

        return base64_encode($generatedHash);
    }

    /**
     * Verify hash against given model
     *
     * @param Quotation $quotation
     * @param string $givenHash
     *
     * @return bool
     */
    public function verify(
        Quotation $quotation,
        string $givenHash
    ): bool
    {
        $extractedAttributes = $this->extractAttributesFromQuotation($quotation);

        if (false === $this->hasher->check(serialize($extractedAttributes), base64_decode($givenHash))) {
//            $this->logger->warning(
//                'Quotation hash verification failed', [
//                    'quotation_id' => $quotation->getAttribute('id'),
//                    'give_hash' => $givenHash,
//                    'extracted_attributes' => $extractedAttributes,
//                ]
//            );

            return false;
        }

        return true;
    }

    /**
     * Extract attributes from a given quotation model
     *
     * NOTE: We cannot generate the hash here based on the `updated_at` attributes, as some of the quotation data
     * may get modified frequently when opening or leaving the quotation (e.g. `locked_at` && `locked_by`)
     *
     * @param Quotation $quotation
     * @return array
     */
    private function extractAttributesFromQuotation(Quotation $quotation): array
    {
        return [
            'quotation' => $this->excludeUnNeededAttributes($quotation->getAttributes()),

            'items' => $quotation->items()->get()->map(
                function (Item $item): array {
                    return [
                        'pivot' => $this->excludeUnNeededAttributes($item->pivot->getAttributes()),
                        'item' => $this->excludeUnNeededAttributes($item->getAttributes()),
                    ];
                }
            )->toArray()
        ];
    }

    /**
     * Exclude the un-needed keys from a given list of attributes
     *
     * @param array $attributes
     *
     * @return array
     */
    private function excludeUnNeededAttributes(array $attributes): array
    {
        return array_diff_key($attributes, array_flip([
            'updated_at',
            'created_at',
            'locked',
            'locked_by',
            'locked_at'
        ]));
    }
}
