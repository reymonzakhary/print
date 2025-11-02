<?php

namespace Modules\Cms\Transformers\Snippets\Calculation;

use App\Plugins\Moneys;
use Carbon\Carbon;
use Cmixin\BusinessDay;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class FullCalculationResource extends JsonResource
{
    /**
     * @var array
     */
    protected array $withoutFields = [];


    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->filterFields([
            "product" => $this->getProduct(optional($this->resource)['product'] ?? []),
            "pm" => optional($this->resource)['pm'],
            "tables" => $this->tables(optional($this->resource)['tables']),
            "info" => $this->getAdditional(optional($this->resource)['additional'] ?? []),
        ]);
    }

    public function tables(
        $data = []
    ) {
        if (empty($data)) {
            return [
                'pm' => '',
                'qty' => 0,
                'dlv' => 0,
                'p' => 0,
                'display_p' => 0,
                'ex' => 0,
                'ppp' => 0,
                'display_ppp' => 0,
                'display_gross_price' => 0,
                'gross_price' => 0,
                'buying_price' => 0,
                'display_buying_price' => 0,
                'deliver_at' => 0,
                'selling_price' => 0,
                'display_selling_price' => 0,
                'profit' => 0,
                'display_profit' => 0,
                'discount' => [],
                'margins' => [],
            ];
        }
        return [
            'pm' => $data['pm'],
            'qty' => $data['qty'],
            'dlv' => $data['dlv'],
            'p' => $data['p'],
            'display_p' => ((new Moneys())->setAmount($data['p']))->format(),
            'ex' => optional($data)['ex'],
            'ppp' => $data['ppp'],
            'display_ppp' => ((new Moneys())->setAmount($data['ppp']))->format(),

            'display_gross_price' => ((new Moneys())->setAmount($data['gross_price']))->format(),
            'gross_price' => $data['gross_price'],

            'buying_price' => $data['buying_price'],
            'display_buying_price' => ((new Moneys())->setAmount($data['buying_price']))->format(),
            'deliver_at' => $this->getBusinessDay(optional($this['tables'])['data'], (int)optional($data['dlv'])['days']),
            'selling_price' => $data['selling_price'],
            'display_selling_price' => ((new Moneys())->setAmount($data['selling_price']))->format(),

            'profit' => $data['profit'],
            'display_profit' => ((new Moneys())->setAmount($data['profit']))->format(),
            'discount' => $this->displayDiscountAndMargin($data['discount']),
            'margins' => $this->displayDiscountAndMargin($data['margins']),
        ];
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param array $fields
     * @return $this
     */
    public function hide(array $fields)
    {
        $this->withoutFields = $fields;

        return $this;
    }

    public function displayDiscountAndMargin(array $data)
    {
        if (optional($data)['type'] && optional($data)['value']) {
            if ($data['type'] === "fixed") {
                $data['display_value'] = ((new Moneys())->setAmount($data['value']))->format();
            } else {
                $data['display_value'] = $data['value'];
            }
        }

        return $data;
    }

    /**
     * Remove the filtered keys.
     *
     * @param array $data
     * @param int   $dlv
     * @return array
     */
    protected function getBusinessDay(
        array $data,
        int $dlv
    ): array {
        if (count($data['production_days']) === 0) {
            return [];
        }
        if ($data['countries']) {
            $region = Str::lower("{$data['countries'][0]['iso2']}-{$data['countries'][0]['un_code']}");
            $baseList = $region; // or region such as 'us-il'
        } else {
            $baseList = 'nl-national'; // or region such as 'us-il'
        }

        $extraDays = 0;
        $i = 0;
        $working_day = true;
        $additionalHolidays = [];
        $extraDaysWorks = [];
        $now = Carbon::now();
        $isoDay = Str::lower($now->copy()->locale('en')->isoFormat('ddd'));
        $start_day = collect($data['production_days'])->first(fn ($item) => ($item['day'] === $isoDay));
        $day_off = collect($data['production_days'])->pluck('active', 'day');

        $dlv = (strtotime($start_day['deliver_before']) >= time()) && $start_day['active'] ? $dlv ?? -1 : $dlv;
        while ($working_day) {
            $date = Carbon::now()->addDays($extraDays);
            if (!$day_off[Str::lower($date->locale('en')->isoFormat('ddd'))]) {
                $additionalHolidays[$date->isoFormat('ddd-D')] = $date->isoFormat('M-D');
            } else {
                /**
                 * Fixme if data in calendar Remove it from  $extraDaysWorks Array
                 * we don't have calendar yet
                 */
                $extraDaysWorks[$date->isoFormat('ddd-D')] = $date->isoFormat('M-D');
                $i++;
            }
            $extraDays++;
            if ($i > $dlv) {
                $working_day = false;
            }
        }
        BusinessDay::enable('Illuminate\Support\Carbon', $baseList, $additionalHolidays, $extraDaysWorks);
        $deliver_at = $now::addBusinessDay($dlv);
        return [
            'day' => $deliver_at->isoFormat('D'),
            'day_name' => $deliver_at->isoFormat('dddd'),
            'month' => $deliver_at->isoFormat('MMMM'),
            'year' => $deliver_at->isoFormat('YYYY'),
            'actual_days' => $deliver_at->diffInDays(Carbon::today())
        ];
    }

    /**
     * @param array $additional
     * @return array
     */
    protected function getAdditional(
        array $additional = []
    ): array {
        if (!count($additional)) {
            return [];
        }

        return array_merge($additional, [
            "approximately_production_duration" => [
                "duration" => optional($additional['approximately_production_duration'])['duration'],
                "end_at" => Carbon::parse(request()->start_at)->addMinutes((int) round(optional($additional['approximately_production_duration'])['duration'] ?? 0)),
                "duration_type" => optional($additional['approximately_production_duration'])['duration_type'],
            ],
        ]);
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function getProduct(
        array $attributes = []
    ): array {
        return [
            "id" => optional($attributes)['_id'],
            "category_name" => optional($attributes)['category_name'],
            "category_display_name" => getDisplayName(optional($attributes)['category_display_name']),
            "category_slug" => optional($attributes)['category_slug'],
            "linked" => optional($attributes)['linked'],
            "supplier_category" => optional($attributes)['supplier_category'],
            "object" => optional($attributes)['object'],
        ];
    }

    protected function getObject(array|null $object): array
    {
        if ($object) {
            return array_map(function ($item) {
                return [
                    'key' => $item['key'],
                    'value' => $item['value'],
                ];
            }, $object);
        }
        return [];
    }

    /**
     * @param array $array
     * @return array
     */
    protected function filterFields(array $array)
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }
}
