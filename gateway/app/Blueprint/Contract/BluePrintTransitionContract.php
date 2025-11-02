<?php

namespace App\Blueprint\Contract;

interface BluePrintTransitionContract
{
    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null);

    public function approve(mixed $request, mixed $data, $node = null, mixed $cart = null);

    public function reject(mixed $request, mixed $data, $node = null, mixed $cart = null);
}
