<?php

declare(strict_types=1);

namespace App\Plugins\Util\Printcom;

use App\Facades\Settings;
use App\Plugins\DTO\Printcom\BoopsDTO;
use Carbon\Carbon;
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
    public function mapProductVariationsStructureFromPrindustryToPrintCom(
        int   $quantity,
        array $categorySelectedVariations,
        array $categoryData,
    ): array|bool
    {
        $result = ['copies' => $quantity];
        $category = collect(data_get($categoryData, 'boops'));
        $variation_amount = count($category->pluck('boops')->first()) + 1;
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
                $this->findOptionInTheBoopsDocument($categoryBoops, $boxData['value'])['source_slug'],
            ];

            $originalOptionSlugCollection = $this->flattenSourceSlugData($originalOptionSlugCollection);
            if(empty($originalOptionSlugCollection)){
                continue;
            }
            $optionSourceSlugKey = sprintf('%s%s', $categoryData['slug'], $boxData['key']);
            if ($originalBoxSlug === 'specific_customer_options') {
                continue;
            }

            $result[$originalBoxSlug] = $originalOptionSlugCollection[$optionSourceSlugKey];

            if ($boxData['dynamic'] === true) {
                $result = array_merge($result, $boxData['_']);
            }
        }

        return $variation_amount === count($result) ? $result : false;
    }

    /**
     * @param array $sourceSlugData
     *
     * @return array
     */
    private function flattenSourceSlugData(array $sourceSlugData): array
    {
        $result = [];

        foreach ($sourceSlugData as $sourceSlug) {
            $result = array_merge($result, $sourceSlug);
        }

        return $result;
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
     * @param array $rawResult
     *
     * @return array
     */
    public function mapPriceResultDataStructureFromPrintComToPrindustry(
        int   $quantity,
        array $rawResult,
    ): array
    {
        ['product_price' => $productPriceData, 'product_shipping' => $productShippingData] = $rawResult;

        $grossPriceFormattedAsInteger = Str::replace('.', '', (string)$productPriceData['prices']['normalPrice']);

        $priceList = [];

        foreach ($productShippingData as $possibleDayData) {
            foreach ($possibleDayData['possibilities'] as $timeSlotData) {
                $deliveryDate = $this->carbon->parse($timeSlotData['deliveryDate']);

                $grossPriceIncludingShipping = $grossPriceFormattedAsInteger + ($timeSlotData['price']['base'] * 100);
                $pppIncludingShipping = $grossPriceIncludingShipping / $quantity;

                $priceList[] = [
                    "qty" => $quantity,

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

                continue 2;
            }
        }

        return array_slice($priceList, 0, 4);
    }
}
