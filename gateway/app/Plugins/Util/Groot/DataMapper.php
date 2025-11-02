<?php

declare(strict_types=1);

namespace App\Plugins\Util\Groot;

use App\Facades\Settings;
use App\Plugins\DTO\Printcom\BoopsDTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

final readonly class DataMapper
{
    public function __construct(
        private Carbon $carbon
    ) {
    }

    /**
     * @param int $quantity
     * @param array $categorySelectedVariations
     * @param array $categoryData
     *
     * @return array|bool
     */
    public function mapProductPrindustryToGroot(
        int   $quantity,
        array $categorySelectedVariations,
        array $categoryData,
    ): array|bool
    {
        $options = [];
        $category = collect(data_get($categoryData, 'boops'));
        $categoryBoops = $category->first();

        if (!$categoryBoops || !is_array($categoryBoops) || count($categoryBoops) === 0) {
            throw new RuntimeException(
                sprintf(
                    'Looks like the category "%s" does not have boops?', $categoryData['slug']
                )
            );
        }

        foreach ($categorySelectedVariations as $boxData) {
            [$originalBoxSlug, $originalOptionSlugCollection] = [
                $this->findBoxInTheBoopsDocument($categoryBoops, $boxData['key'])['source_slug'],
                $this->findOptionInTheBoopsDocument($categoryBoops, $boxData['value'])['name'],
            ];

            $originalOptionSlugCollection = $this->flattenSourceSlugData($originalOptionSlugCollection);
            if(empty($originalOptionSlugCollection)){
                continue;
            }

            if ($originalBoxSlug === 'specific_customer_options') {
                continue;
            }

            // Format as "box_name: option_value"
            $options[] = sprintf('%s: %s', $originalBoxSlug, $originalOptionSlugCollection);
        }

        return $options;
    }

    /**
     * @param array|string $sourceSlugData
     *
     * @return string
     */
    private function flattenSourceSlugData($sourceSlugData): string
    {
        // Handle string format (new format from Python) - return as is
        if (is_string($sourceSlugData)) {
            return $sourceSlugData;
        }

        // Handle array format (legacy format) - return first element as string
        if (is_array($sourceSlugData)) {
            $result = [];
            foreach ($sourceSlugData as $sourceSlug) {
                if (is_array($sourceSlug)) {
                    $result = array_merge($result, $sourceSlug);
                } else {
                    $result[] = $sourceSlug;
                }
            }
            return !empty($result) ? (string) $result[0] : '';
        }


        return '';
    }

    /**
     * @param array $productBoops
     * @param string $boxSlug
     *
     * @return array|null
     */
    private function findBoxInTheBoopsDocument(
        array  $productBoops,
        string $boxSlug
    ): ?array
    {
        foreach ((new BoopsDTO($productBoops))->extractBoxesData(false) as $boxData) {
            if ($boxData['slug'] === $boxSlug) {
                return $boxData;
            }
        }

        throw new RuntimeException(
            sprintf('Could not find box "%s" within the boops document', $boxSlug)
        );
    }

    /**
     * @param array $productBoops
     * @param string $optionSlug
     *
     * @return array|null
     */
    private function findOptionInTheBoopsDocument(
        array  $productBoops,
        string $optionSlug
    ): ?array
    {
        foreach ((new BoopsDTO($productBoops))->extractOptionsData() as $optionData) {
            if ($optionData['slug'] === $optionSlug) {
                return $optionData;
            }
        }

        throw new RuntimeException(
            sprintf('Could not find option "%s" within the boops document', $optionSlug)
        );
    }

    /**
     * @param int $quantity
     * @param array $rawResults
     *
     * @return array
     */
    public function mapPriceFromGrootToPrindustry(
        int   $quantity,
        array $rawResults,
    ): array
    {
        $priceList = [];

        // Extract quantity_pricing data
        $quantityPricing = $rawResults['quantity_pricing'] ?? [];

        if (empty($quantityPricing) || !isset($quantityPricing['prices'])) {
            return [];
        }

        $qty = (int) ($quantityPricing['quantity'] ?? $quantity);

        foreach ($quantityPricing['prices'] as $priceData) {
            $price = (float) ($priceData['price'] ?? 0);
            $grossPrice = $price * 100; // Convert to cents
            $shippingPrice = 0;

            $grossPriceIncludingShipping = $grossPrice + $shippingPrice;
            $pppIncludingShipping = $grossPrice;

            $deliveryDate = Carbon::parse($priceData['deliverydate']);

            $priceList[] = [
                "qty" => $qty,

                "dlv" => [
                    "days" => $deliveryDate->diffInDaysFiltered(
                        static function (Carbon $day): bool {
                            return false === $day->isWeekend();
                        }
                    ),

                    "day" => $deliveryDate->format('d'),
                    "day_name" => $deliveryDate->format('D'),
                    "month" => $deliveryDate->format('M'),
                    "year" => $deliveryDate->format('Y'),
                    "actual_days" => $deliveryDate->diffInDays(),
                    "type" => $priceData['deliverytype'] ?? 'Standard',
                ],

                "p" => $grossPriceIncludingShipping,
                "selling_price_ex" => $grossPriceIncludingShipping,
                "selling_price_inc" => $grossPriceIncludingShipping,
                "vat" => Settings::vat()->value,
                "gross_price" => $grossPriceIncludingShipping,
                "gross_ppp" => $pppIncludingShipping,
                "ppp" => $pppIncludingShipping,
                "profit" => 0,
                "discount" => [],
                "margins" => []
            ];
        }

        return array_slice($priceList, 0, 4);
    }






}
