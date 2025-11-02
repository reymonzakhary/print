<?php

declare(strict_types=1);

namespace App\Plugins\DTO\Printcom;

use RuntimeException;

final readonly class BoopsDTO
{
    public function __construct(
        private array $boopsData
    ) {
    }

    /**
     * @return string
     */
    public function extractCategorySlug(): string
    {
        if (!array_key_exists('slug', $this->boopsData)) {
            throw new RuntimeException("`slug` parameter is missing from the `boops` document");
        }

        return $this->boopsData['slug'];
    }

    /**
     * @return array
     */
    public function extractCategoryRelatedFields(): array
    {
        return array_diff_key($this->boopsData, array_flip(['boops']));
    }

    /**
     * @param bool $withOptions
     *
     * @return array
     */
    public function extractBoxesData(
        bool $withOptions = true
    ):  array
    {
        $boxesData = [];

        foreach($this->boopsData['boops'] as $boxData) {
            $boxesData[] = $withOptions ? $boxData : array_diff_key($boxData, array_flip(['ops']));
        }

        return $boxesData;
    }

    /**
     * @return array
     */
    public function extractOptionsData(): array
    {
        $optionsData = [];

        foreach ($this->boopsData['boops'] as $boxData) {
            foreach ($boxData['ops'] as $optionData) {
                $optionsData[] = $optionData;
            }
        }

        return $optionsData;
    }
}
