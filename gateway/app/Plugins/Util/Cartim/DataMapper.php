<?php

declare(strict_types=1);

namespace App\Plugins\Util\Cartim;

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
    public function mapProductPrindustryToCartim(
        int   $quantity,
        array $categorySelectedVariations,
        array $categoryData,
    ): array|bool
    {
//        dd($categoryData);
        $result = ['Quantity' => $quantity];
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

            // $originalOptionSlugCollection is now a simple string (option name like "30 cl")
            $result[$originalBoxSlug] = $originalOptionSlugCollection;
        }
        return
            collect($result)->map(function ($value, $key) {
                return [
                    $key => $value
                ];
            })->values()->all();
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
    public function mapPriceFromCartimToPrindustry(
        int   $quantity,
        array $rawResults,
    ): array
    {
        if (isset($rawResults['price'])) {
            $rawResults = [$rawResults];
        }
        $priceList = [];
        foreach ($rawResults as $rawResult) {
            ['price' => $productPriceData, 'deliveryDays' => $deliveryDays, 'promisedArrivalDate' => $productShippingData] = $rawResult;
            $grossPrice = $productPriceData * 100;
            $shippingPrice = 0;


            $deliveryDate = $deliveryDays;

            $grossPriceIncludingShipping = $grossPrice  + $shippingPrice;
            $pppIncludingShipping = $grossPrice;
            $productShippingData = Carbon::parse($productShippingData);
            $priceList[] = [
                "qty" => $quantity,

                "dlv" => [
                    "days" => $productShippingData->diffInDaysFiltered(
                        static function (Carbon $day): bool {
                            return false === $day->isWeekend();
                        }
                    ),

                    "day" => $productShippingData->format('d'),
                    "day_name" => $productShippingData->format('D'),
                    "month" => $productShippingData->format('M'),
                    "year" => $productShippingData->format('Y'),
                    "actual_days" => $productShippingData->diffInDays(),
                    "type" => $rawResult['DeliveryType'],
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
