<?php

namespace App\Shop\Contracts;

interface ShopCategoryInterface
{
    public function categories();
    public function category(string $category);
}
