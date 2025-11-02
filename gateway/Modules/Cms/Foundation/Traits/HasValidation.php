<?php

namespace Modules\Cms\Foundation\Traits;

use Modules\Cms\Entities\Resource;

trait HasValidation
{
    /** 
     * prepare the rules to be used in the validation
     * 
     * @param array $rules rules incoming from request
     * 
     * @return array
     * 
    */
    private function prepareRules(array $rules): array
    {
        collect($this->rules??[])->each(function ($value, $rule) use (&$rules) {
            $requestRule = array_unique(array_merge(
                $this->parseRuleValue(optional($rules)[$rule]),
                $this->parseRuleValue($value)
            ));
            $rules[$rule] = $requestRule;
        });

        return $rules;
    }

    /**
     * parse rule value to be always array
     * @param string|array|null $value
     * 
     * @return array
     */
    private function parseRuleValue(string|array|null $value): array
    {
        if (is_string($value)) {
            return explode('|', $value);
        }

        if (is_array($value)) {
            return $value;
        }

        return [];
    }

}
