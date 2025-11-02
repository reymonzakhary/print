<?php

namespace App\Blueprint\Contract;

interface BluePrintActionContract
{
    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null);
}
