<?php

namespace App\Plugins\Webhooks\DWD;

use App\Enums\Status;
use App\Models\Domain;
use App\Models\Tenant\Item;
use App\Models\Tenant\Order;
use App\Models\Website;
use App\Plugins\Webhooks\BaseWebhookHandler;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Handles DWD (Direct Web Drive) order webhooks
 * Processes incoming orders and forwards them to the DWD service
 */
class OrderWebhookHandler extends BaseWebhookHandler
{
    /**
     * Handle incoming DWD order webhook payload
     *
     * @param array $payload The webhook payload containing order data
     * @param string $endpoint The webhook endpoint that was called
     * @return array Response data indicating success or failure
     * @throws \Exception
     * @throws GuzzleException
     */
    public function handle(array $payload, string $endpoint): array
    {
        // Get the current tenant's hostname and website configuration
        $current_hostname = Domain::where('id' , $this->tenant_id)->select('id' , 'website_id')->with('website')->first();
        // Validate that the tenant has a properly configured website for external webhooks
        if (!$current_hostname || !$current_hostname->website->configure || !$current_hostname->website->external) {
            return [
                'status' => 'failed',
                'message' => 'No tenant website found for webhook trigger',
                'received_data' => null,
                'processed_at' => now()->toISOString(),
                'tenant_id' => $this->tenant_id,
            ];
        }
        // Retrieve the order with its items from the payload
        $order = Order::with(['items'])->find($payload['id']);
        // Check that all items in the order have associated media files (required for DWD)
        $allItemsHaveMedia = $order->items->every(function ($item) {
            return $item->getMedia()->isNotEmpty();
        });
        // Reject the order if any items are missing media files
        if (!$allItemsHaveMedia) {
            Log::debug("All Items Must Have Media To Proceed With DWD" , ['order_id' => $order->id]);
            $this->handleWebhookFailure($order , ['message' => 'All Items Must Have Media To Proceed With DWD'] , $current_hostname);
            return [
                'status' => 'failed',
                'message' => 'All Items Must Have Media To Proceed With DWD',
                'received_data' => ['order_id' => $order->id],
                'processed_at' => now()->toISOString(),
                'tenant_id' => $this->tenant_id,
            ];
        }
        // Transform the order data to DWD format
        $payload = $this->mapOrderPrindustryToDWD($order);
        // Send the order to DWD service
        $response = $this->makeRequest('POST', 'orders', formParams: [
            'order' => $payload,
            'tenant_id' => $this->tenant_id
        ], forceJson: true);
        if (!empty($response['data'])){
            // If the initial response doesn't contain an order ID, implement retry logic
            if (empty($response['data'][0]['id'])) {
                // Initialize retry parameters
                $get_order_request = null;
                $maxRetries = 3;
                $retryCount = 0;
                $startTime = time();
                $timeout = 120; // 2 minutes timeout
                // Retry getting the order details with exponential backoff
                while ($retryCount < $maxRetries && (time() - $startTime) < $timeout) {
                    try {
                        // Attempt to retrieve the order using its UUID
                        $get_order_request = $this->makeRequest('GET', 'orders/' . $response['data'][0]['uuid'], formParams: [
                            'order' => $payload,
                            'tenant_id' => $this->tenant_id
                        ], forceJson: true);
                        if (!empty($get_order_request['data'][0]['id'])) {
                            // Order successfully retrieved, handle success
                            $this->handleWebhookSuccess($order , $get_order_request , $current_hostname);
                            break;
                        } else {
                            // Order not ready yet, prepare for retry
                            $retryCount++;
                            if ($retryCount < $maxRetries && (time() - $startTime) < $timeout) {
                                $waitTime = 10 * $retryCount; // Exponential backoff
                                sleep($waitTime);
                            }
                        }
                    } catch (\Exception $e) {
                        // Handle request exceptions with retry logic
                        $retryCount++;
                        if ($retryCount < $maxRetries && (time() - $startTime) < $timeout) {
                            $waitTime = 10 * $retryCount; // Exponential backoff
                            sleep($waitTime);
                        } else {
                            // Max retries reached, handle failure
                            $this->handleWebhookFailure($order , $get_order_request , $current_hostname);
                            $get_order_request = [
                                'status' => 'failed',
                                'message' => 'Failed after ' . $retryCount . ' retries: ' . $e->getMessage()
                            ];
                        }
                    }
                }
                // Check if we failed to get order details after all retries
                if (empty($get_order_request['data']) || (empty($get_order_request['data'][0]['id']))) {
                    $this->handleWebhookFailure($order , $get_order_request , $current_hostname);
                    Log::debug("Get Order Request timed out after 2 minutes", [
                        'retries' => $retryCount,
                        'elapsed_time' => time() - $startTime
                    ]);
                    return [
                        'status' => 'failed',
                        'message' => 'DWD get order request failed',
                        'received_data' => $response['data'],
                        'processed_at' => now()->toISOString(),
                        'tenant_id' => $this->tenant_id,
                    ];
                }
            } else {
                // Order ID was present in initial response, proceed with success handling
                $get_order_request = $response;
                $this->handleWebhookSuccess($order , $get_order_request , $current_hostname);
            }
            return [
                'status' => 'success',
                'message' => 'DWD order webhook processed successfully',
                'received_data' => $response['data'],
                'processed_at' => now()->toISOString(),
                'tenant_id' => $this->tenant_id,
            ];
        } else {
            // No data in response, handle as failure
            $this->handleWebhookFailure($order , $response , $current_hostname);
            Log::info("Order Failed To Proceed in DWD" , ['response' => $response['message']]);
            return [
                'status' => 'failed',
                'message' => 'DWD order webhook failed to process',
                'received_data' => $response['message'] ?? 'No response from DWD',
                'processed_at' => now()->toISOString(),
                'tenant_id' => $this->tenant_id,
            ];
        }

    }

    /**
     * Transform Prindustry order format to DWD format
        * @return array The order data in DWD format
     */
    private function mapOrderPrindustryToDWD($order): array
    {
        // Map delivery and invoice addresses to DWD format
        $delivery_address = $this->mapAddressToDWD($order->delivery_address()->first() , $order->connection);
        $invoice_address = $this->mapAddressToDWD($order->invoice_address()->first() , $order->connection);
        $orderLines = [];

        // Transform each order item to DWD format
        foreach ($order->items as $item) {
            // Build order item with SKU, attributes, and file URLs
            $order_item = [
                'sku' => $item->product->category['sku'],
                'attributes' => [],
                'files' => $item->getMedia()->map(function ($file) {
                    return [
                        'url' => Storage::disk($file->disk)->url(tenant()->uuid . $file->path . $file->name)
                    ];
                })->toArray()
            ];

            if (isset($item->product) && is_object($item->product)) {
                // Add product attributes from the product data
                foreach ($item->product->product as  $product) {
                    $order_item['attributes'][] = [
                        'attribute' => $product['source_key'],
                        'value' => $product['source_value']
                    ];
                }
                // Add delivery type and quantity as attributes
                $order_item['attributes'][] = [
                    'attribute' => 'Delivery Type',
                    'value' => ($item->product->price['dlv']['type'] )
                ];

                $order_item['attributes'][] = [
                    'attribute' => 'Quantity',
                    'value' => $item->product->quantity
                ];
            }
            $orderLines[] = $order_item;
        }

        // Return the complete order data in DWD format
        return [
            "orderLines" => $orderLines,
            'testOrder' => true, // TODO: Set based on environment
            'invoiceAddress' => $invoice_address,
            'deliveryAddress' => $delivery_address,
            'deliveryMethod' => '1',
            "order_id" => strval($order->id)
        ];
    }



    /**
     * Transform address data to DWD format
     *
     * @param mixed $address The address object to transform
     * @param string $connection The connection UUID to get tenant data
     * @return array The address data in DWD format
     */
    private function mapAddressToDWD(mixed $address , string $connection): array
    {
        // Get tenant data from the connection UUID for fallback values
        $website = Website::query()->where('uuid' , $connection)->with('hostname')->first();
        $hostname = $website->hostname;
        $tenant_data =$hostname->custom_fields->toArray();
        // Split full name into first and last name
        $nameParts = explode(' ', ($address->pivot->full_name ?? $tenant_data['name']), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';
        // Return address data in DWD format with fallbacks to tenant data
        return [
            'company' => $address->pivot->company_name ??  $tenant_data['company_name'],
            'firstName' => $firstName ,
            'lastName' => $lastName ,
            'email' => $tenant_data['email'], // TODO: Use customer email instead of tenant email
            'street' => $address->address,
            'housenumber' => trim($address->number),
            'zipcode' =>  trim($address->zip_code),
            'city' => $address->city,
            'country' => strtolower($address->country->iso2)
        ];

    }


    /**
     * Handle webhook failure by updating order items status to rejected
     *
     *  $order The order that failed
     * @param array $response The failure response data
     * @param Hostname $current_hostname The current tenant hostname
     * @throws \Exception
     */
    private function handleWebhookFailure($order , array $response , Hostname $current_hostname): void
    {
        Log::debug("handling failure" , $response);

        // Update each item in the order to rejected status
        $order->items()->each(function ($item) use ($response , $current_hostname) {
            // Update order item status in current tenant
            $item->st = Status::REJECTED->value;
            $item->st_message = $response['message'] ?? 'No response from DWD';
            $item->save();

            // Switch to original tenant and update the source item
            switchTenant($item->connection);
            $original_item = Item::query()->where('id', $item->product->item_ref)->first();
            $original_item->st = Status::REJECTED->value;
            $original_item->st_message = $response['message'] ?? 'No response from DWD';
            $original_item->save();

            // Switch back to current tenant
            switchTenant($current_hostname->website->uuid);
        });

    }

    /**
     * Handle webhook success by updating order items with external order data
     * @ $order The order that succeeded
     * @param array $get_order_request The successful response data from DWD
     * @param Hostname $current_hostname The current tenant hostname
     * @throws \Exception
     */
    private function handleWebhookSuccess($order , array $get_order_request , Hostname $current_hostname): void
    {
        // Update each item with the external order data from DWD
        $order->items()->each( function ($item) use ($get_order_request , $current_hostname) {
            // Save external order data to current tenant item
            $item->product->external_order = $get_order_request['data'];
            $item->save();

            // Switch to original tenant and update the source item
            switchTenant($item->connection);
            $original_item = Item::query()->where('id', $item->product->item_ref)->first();
            $original_item->product->external_order = $get_order_request['data'];
            $original_item->save();

            // Switch back to current tenant
            switchTenant($current_hostname->website->uuid);
        });
    }


}
