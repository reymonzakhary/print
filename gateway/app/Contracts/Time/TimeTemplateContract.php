<?php

namespace App\Contracts\Time;

use Exception;
use Illuminate\Support\Str;

class TimeTemplateContract
{
    public function __invoke(
        string $method,
               ...$arguments,
    )
    {
        if (!method_exists(__CLASS__, $method)) {
            throw new Exception("We can\'t handle this method `${method}`");
        }
        return $this->{$method}($arguments);
    }

    public function format($status, $item)
    {
        return $this->calc(
            $status->cond->operator,
            (int)data_get($item, Str::lower($status->cond_from)) * $this->day(),
            (int)$status->cond->int * $this->{$status->cond->type}()
        );
    }

    private function calc($operator, $num, $original)
    {
        if ($operator == '+') {
            return -1 * ($num + $original);
        } elseif ($operator == '-') {
            return ($num + $original);
        }
    }

    public function day()
    {
        return 24 * 60;
    }

    public function hour()
    {
        return 60;
    }

    public function minute()
    {
        return 1;
    }
}
