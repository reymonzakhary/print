<?php

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum BlueprintNamespaces: string
{
    use EnumConcern;

    case ORDERS = 'orders';

    case QUOTATIONS = 'quotations';

    case CART = 'cart';

    case CHECKOUT = 'checkout';

    case SHOP = 'shop';

    case WORKFLOW_SHOP = 'workflow_shop';

    public static function getNamespaceGroup($value): array
    {

        return match ($value) {
            'workflow' => ['orders', 'quotations', 'workflow_shop'],
            'linked' => ['cart', 'shop', 'checkout'],
            default => []
        };
    }

    public static function grouped()
    {
        return [
            'workflow' => [self::ORDERS, self::QUOTATIONS],
            'linked' => [self::CART, self::CHECKOUT, self::SHOP]
        ];
    }
}
