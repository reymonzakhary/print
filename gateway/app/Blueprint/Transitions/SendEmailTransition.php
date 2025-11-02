<?php

namespace App\Blueprint\Transitions;

use App\Blueprint\Actions\Traits\HasReportingTraits;
use App\Blueprint\Contract\BluePrintTransitionContract;
use App\Mail\BlueprintMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SendEmailTransition implements BluePrintTransitionContract
{
    use HasReportingTraits;

    protected $template = null;
    protected $row = null;

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {
        $xls = data_get($request->toArray(), $data['input']['from']);
        if (!$xls) {
            throw ValidationException::withMessages([
                'rows' => _('We can\'t read rows correctly!'),
                'key' => 'Send Email Transition'
            ]);
        }
        $request->merge(['SendEmailTransition' => collect($xls)->map(function ($row) use ($request, $data) {
//            $this->createReport('Send Email Transition', $row, $request);
            return $this->{"from" . Str::ucfirst($data['input']['template']['from'])}($row, $data['input']['template'], $request)
                ->getText($row, $request)
                ->send($row, $data);
        })]);
    }

    public function approve(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {
        // TODO: Implement approve() method.
    }

    public function reject(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {
        // TODO: Implement reject() method.
    }

    protected function fromDb($row, $file, $request)
    {

        $this->data = [
            'search' => [],
            'replace' => [],
        ];
        $fallback = [
            'where' => '',
            'replace' => [],
        ];
        $className = 'App\\Models\\Tenants\\' . $file['model'];
        $this->row = $row;
        $where = "";
        $replace = [];
        /**
         * @todo delete when lockup is ready
         */
//        $where = "UPPER(name) = ?";
//        $replace[] = Str::upper($row['program']);

        collect($file['selector'])->map(function ($obj) use ($row, &$where, &$replace, &$fallback) {
            if (!optional($obj)['regex']) {
                $replace[] = $row[Str::lower($obj['cond']['expr'])] ?? Str::upper($obj['cond']['expr']);
                $fallback['replace'][] = Str::upper($obj['fallback']);

            } else {
                preg_match("/\[(.*?)\]/", $obj['cond']['expr'], $match);

                if (optional($match)[1]) {
                    $replace[] = Str::upper(Str::replace($match[0], $row[$match[1]], $obj['cond']['expr']));
                    $fallback['replace'][] = Str::upper($obj['fallback']);
                } else {
                    return null;
                }
            }
            $where .= "UPPER({$obj['cond']['column']}) = ?";
            $fallback['where'] .= "UPPER({$obj['cond']['column']}) = ?";
        });
        if ($template = app($className)->whereRaw($where, $replace)->first()) {
            $this->template = $template;
            return $this;
        } elseif ($template = app($className)->whereRaw($fallback['where'], $fallback['replace'])->first()) {
            $this->template = $template;
            return $this;
        }

        throw ValidationException::withMessages([
            'template' => __('Template :name not found! ', ['name' => Str::upper($row['program'])])
        ]);
    }

    protected function getText($row, $request)
    {
        $this->template->content;
        return $this;
    }

    protected function send()
    {
        /**
         * need more data
         */
        $data = [
            'name' => 'asjdkasjdk asdkjas asdjalks daskd'
        ];
        Mail::to('mamdouh.khaled@live.com')->send(new BlueprintMail([
            'data' => $this->row,
            'template' => $this->template->value
        ]));
//        mail('mamdouh.khaled@live.com', 'test', Blade::render());
    }
}
