<?php

namespace App\Actions\Import\Miele;

use App\Models\Tenant\Category;
use App\Models\Tenant\Media\FileManager;
use Mtownsend\XmlToArray\XmlToArray;


class XmlImport
{

    public $iso = 'en';
    public $flatProp = [];

    public function storeCategory($file)
    {
        $products = XmlToArray::convert(file_get_contents($file));
        $this->iso = strtolower(optional(optional($products)['@attributes'])['country']);
        $categoryName = optional($products['MATERIAL'])[0] ? $products['MATERIAL'][0]['@attributes']['productGroup'] : $products['MATERIAL']['@attributes']['productGroup'];
        $category = Category::FirstOrCreate(['iso' => $this->iso, 'name' => $categoryName]);
        if (optional($products['MATERIAL'])[0]) {
            collect($products['MATERIAL'])->map(function ($product) use ($category) {
                $this->storeProduct($category, $product);
            });
        } else {
            $this->storeProduct($category, $products['MATERIAL']);
        }
        return count($products['MATERIAL']);
    }

    public function storeProduct(Category $category, array $product)
    {
        $property = $this->preperProperties($product);
        $productData = $category->products()->create([
            'iso' => $this->iso,
            'name' => optional(optional($this->flatProp)['BASICS'])['CODE'],
            'ean' => optional(optional($this->flatProp)['BASICS'])['EAN'],
            'art_num' => optional(optional($this->flatProp)['BASICS'])['ART_NUM'],
            'description' => optional(optional($this->flatProp)['MC'])['LONG_DESCRIPTION'],
            'properties' => $property
        ]);

        $this->storeImages($this->flatProp['MC'], $productData);
    }

    public function preperProperties($product)
    {
        $this->flatProp = ['materialNumber' => optional(optional($product)['@attributes'])['materialNumber']];
        foreach ($product['PI_DATA'] as $key => $item) {
            if (optional($item)['ATTRIBUTE']) {
                foreach (optional($item)['ATTRIBUTE'] as $attr) {
                    $this->flatProp[$key][optional(optional($attr)['@attributes'])['name']] = optional($attr)['@content'];
                }
            } elseif (!optional($item)['ATTRIBUTE'] && count($item) > 1) {
                foreach ($item as $innerArr) {
                    foreach (optional($innerArr)['ATTRIBUTE'] as $innerItem) {
                        $this->flatProp[$key][optional(optional($innerItem)['@attributes'])['name']] = optional($innerItem)['@content'];
                    }
                }
            }
        }
//@todo have to be clean-up
        foreach ($this->flatProp as $key => $value) {
            $new[] = [
                'key' => $key,
                'value' => $value
            ];
        }
        return $new;
    }

    public function storeImages($mc, $productData)
    {
        foreach ($mc as $key => $item) {
            if (filter_var($item, FILTER_VALIDATE_URL)) {

                FileManager::create(['user_id' => auth()->user()->id,
                    'name' => $key,
                    'ext' => 'jpg',
                    'type' => 'image/jpg',
                    'path' => $item,
                    'disk' => 'external',
                    'external' => true,
                    'size' => 0,
                    'model_type' => 'App\Models\Tenants\Product',
                    'model_id' => $productData->id,
                ]);

            }
        }
        // TODO handle store images from external to our media manager
    }
}
