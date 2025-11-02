<?php

namespace Modules\Cms\Plugins\Resources\Contracts;

interface ResourceRepository
{

    public const VERSION = '1.0';

    public const boxTpl = '';


    public function boxTpl(string $string): void;

    public function boxClass(): string;

    public function classes(): string;

}
