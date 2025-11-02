<?php

declare(strict_types=1);

namespace App\Utilities\Order;

use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\Storage;

final readonly class OrderHasher
{
    public function __construct() {}

    /**
     * Generate a hash for a given model
     *
     * @param Order $order
     *
     * @return string
     */
    public function generate(
        Order $order
    ): string
    {
        $extractedAttributes = $this->extractAttributesFromOrder($order);
        $serializedData = serialize($extractedAttributes);

        return hash('sha256', $serializedData);
    }

    /**
     * Verify hash against given model
     *
     * @param Order $order
     * @param string $givenHash
     *
     * @return bool
     */
    public function verify(
        Order $order,
        string $givenHash
    ): bool
    {
        $extractedAttributes = $this->extractAttributesFromOrder($order);
        $serializedData = serialize($extractedAttributes);
        $currentHash = hash('sha256', $serializedData);

        return $givenHash === $currentHash;
    }

    /**
     * Extract attributes from a given order model
     *
     * NOTE: We cannot generate the hash here based on the `updated_at` attributes, as some of the order data
     * may get modified frequently when opening or leaving the order (e.g. `locked_at` && `locked_by`)
     *
     * @param Order $order
     * @return array
     */
    private function extractAttributesFromOrder(Order $order): array
    {
        return [
            'order' => $this->excludeUnNeededAttributes($order->getAttributes()),
            'items' => $order->items->map(
                function (Item $item): array {
                    return [
                        'pivot' => $this->excludeUnNeededAttributes($item->pivot->getAttributes()),
                        'item' => $this->excludeUnNeededAttributes($item->getAttributes()),
                    ];
                }
            )->toArray()
        ];
    }

    /**
     * Exclude the un-needed keys from a given list of attributes
     *
     * @param array $attributes
     *
     * @return array
     */
    private function excludeUnNeededAttributes(array $attributes): array
    {
        return array_diff_key($attributes, array_flip([
            'updated_at',
            'created_at',
            'locked',
            'locked_by',
            'locked_at',
            'editing'
        ]));
    }
}
