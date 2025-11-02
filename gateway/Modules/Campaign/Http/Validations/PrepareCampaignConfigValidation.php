<?php

namespace Modules\Campaign\Http\Validations;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PrepareCampaignConfigValidation
{
    /**
     * @var bool
     */
    private static bool $export_html = false;

    /**
     * @var bool
     */
    private static bool $export_image = false;

    /**
     * @var bool
     */
    private static bool $export_pdf = false;

    /**
     * @var int|null
     */
    private static ?int $output_availability = NULL;

    /**
     * @var bool
     */
    private static bool $multiple = false;

    /**
     * @var string
     */
    private static string $output = '';

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        collect($config)->each(function ($value, $key) {
            $method = Str::camel($key);
            $this->{$method}($value);
        });
    }

    /**
     * @param mixed $val
     * @return bool
     */
    final public function exportHtml(mixed $val = null): bool
    {
        if ($val) {
            return self::$export_html = filter_var($val, FILTER_VALIDATE_BOOLEAN);
        }
        return self::$export_html;
    }

    /**
     * @param mixed $val
     * @return bool
     */
    final public function exportImage(mixed $val = null): bool
    {
        if ($val) {
            return self::$export_image = filter_var($val, FILTER_VALIDATE_BOOLEAN);
        }
        return self::$export_image;
    }

    /**
     * @param mixed $val
     * @return bool
     */
    final public function exportPdf(mixed $val = null): bool
    {
        if ($val) {
            return self::$export_pdf = filter_var($val, FILTER_VALIDATE_BOOLEAN);
        }
        return self::$export_pdf;
    }

    /**
     * @param mixed $val
     * @return bool
     */
    final public function export(mixed $val = null)
    {
        return null;
    }

    /**
     * @param mixed $val
     * @return int|null
     * @throws ValidationException
     */
    final public function outputAvailability(mixed $val = null): ?int
    {
        if ($val) {
            $type = gettype($val);
            if (!filter_var($val, FILTER_VALIDATE_INT)) {
                throw ValidationException::withMessages(['output_availability' =>
                    __("The output availability must be a type of int, type {$type} giving.")
                ]);
            }
            return self::$output_availability = Carbon::now()->addDays($val)->timestamp;
        }
        return self::$output_availability;
    }

    /**
     * @param mixed $val
     * @return bool
     */
    final public function multiple(mixed $val = null): bool
    {
        if ($val) {
            return self::$multiple = filter_var($val, FILTER_VALIDATE_BOOLEAN);
        }
        return self::$multiple;
    }

    /**
     * @param mixed $val
     * @return bool
     */
    final public function output(mixed $val = null): string
    {
        if ($val) {
            return self::$output = $val;
        }
        return self::$output;
    }

    /**
     * @return array|null
     */
    final public function prepare(): ?array
    {
        $methods = get_class_methods($this);
        $ex = [
            '__construct',
            'prepare'
        ];
        $response = [];


        foreach (array_values(array_diff($methods, $ex)) as $method) {
            $response[$method] = $this->{$method}();
        }
        return $response;
    }

}
