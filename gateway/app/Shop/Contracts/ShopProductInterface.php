<?php

namespace App\Shop\Contracts;

interface ShopProductInterface
{
    public function setCategories($category);

    public function products();
}
