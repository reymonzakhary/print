<?php

namespace App\Http\Resources\Products;

use Carbon\Carbon;
use Cmixin\BusinessDay;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class PrintProductShopCalcResource extends JsonResource
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
    public function toArray($request): array
    {
        return $this->filterFields([
            "type" => optional($this->resource)['type'],
            "calculation_type" => optional($this->resource)['calculation_type'],
            "items" => optional($this->resource)['items'],
            "product" => optional($this->resource)['product'],
            "connection" => optional($this->resource)['connection'],
            "tenant_id" => optional($this->resource)['tenant_id'],
            "tenant_name" => optional($this->resource)['tenant_name'],
            "external" => optional($this->resource)['external'],
            "external_id" => optional($this->resource)['external_id'],
            "external_name" => optional($this->resource)['external_name'],
            "category" => optional($this->resource)['category'],
            "margins" => $this->displayDiscountAndMargin(optional($this->resource)['margins'] ?? []),
            "divided" => optional($this->resource)['divided'],
            "quantity" => optional($this->resource)['quantity'],
            "calculation" => PrintShopFullCalculationCalcResource::collection(optional($this->resource)['calculation']?? []),
            "prices" => PrintPriceShopCalcResource::collection(optional($this->resource)['prices']??[])
        ]);
    }

    /**
     * @param array $data
     * @return array
     */
    public function prices(
        array $data = []
    ): array
    {

        $vat_p = ($data['p'] * $data['vat']) / 100;
        $vat_ppp = ($data['ppp'] * $data['vat']) / 100;
        return [
            'pm' => $data['pm'],
            'qty' => $data['qty'],
            'dlv' => $data['dlv'],
            'vat' => "% " . $data['vat'],
            'p' => $data['p'],
            'display_p' => ((new \App\Plugins\Moneys())->setAmount($data['p']))->format(),
            'vat_p' => $vat_p,
            'display_vat_p' => ((new \App\Plugins\Moneys())->setAmount($vat_p))->format(),
            'ex' => optional($data)['ex'],
            'ppp' => $data['ppp'],
            'display_ppp' => ((new \App\Plugins\Moneys())->setAmount($data['ppp']))->format(),
            'vat_ppp' => $vat_ppp,
            'display_vat_ppp' => ((new \App\Plugins\Moneys())->setAmount($vat_ppp))->format(),

            'display_gross_price' => ((new \App\Plugins\Moneys())->setAmount($data['gross_price']))->format(),
            'gross_price' => $data['gross_price'],

            'buying_price' => $data['buying_price'],
            'display_buying_price' => ((new \App\Plugins\Moneys())->setAmount($data['buying_price']))->format(),
            'deliver_at' => $this->getBusinessDay($this->resource['category'], (int)$data['dlv']['days']),
            'selling_price' => $data['selling_price'],
            'display_selling_price' => ((new \App\Plugins\Moneys())->setAmount($data['selling_price']))->format(),

            'profit' => $data['profit'],
            'display_profit' => ((new \App\Plugins\Moneys())->setAmount($data['profit']))->format(),
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

    /**
     * @param array $data
     * @return array
     */
    public function displayDiscountAndMargin(
        array $data
    ): array
    {
        if (optional($data)['type'] && optional($data)['value']) {
            if ($data['type'] === "fixed") {
                $data['display_value'] = ((new \App\Plugins\Moneys())->setAmount($data['value']))->format();
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
        array $data, int $dlv
    ): array
    {
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
        $start_day = collect($data['production_days'])->first(fn($item) => ($item['day'] === $isoDay));
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
    ): array
    {
        if(!count($additional)) {
            return [];
        }

        return [
            "duration" => optional($additional)['duration'],
            "end_at" => Carbon::parse(request()->start_at)->addMinutes((int) round(optional($additional)['duration']??0)),
            "duration_type" => optional($additional)['duration_type'],
        ];

    }


    /**
     * @param array $array
     * @return array
     */
    protected function filterFields(
        array $array
    ): array
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }

}
