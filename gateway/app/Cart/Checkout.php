<?php

namespace App\Cart;

use App\Blueprints\Contracts\BlueprintContactInterface;
use App\Cart\Contracts\CartContractInterface;
use App\Cart\Contracts\CheckoutContractInterface;
use App\DTO\Tenant\Orders\ItemDTO;
use App\Enums\OrderOrigin;
use App\Facades\Settings;
use App\Foundation\Status\Status;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use App\Repositories\ItemRepository;
use App\Repositories\OrderRepository;
use File;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Checkout implements CheckoutContractInterface
{
    /**
     * The total is count of all prices from the items
     * @var int
     */
    protected int $total = 0;

    /**
     * The Order has been created.
     * @var Order
     */
    protected Model $order;

    /**
     * @var mixed
     */
    protected mixed $address;



    /**
     * @param SessionManager            $session
     * @param Request                   $request
     * @param CartContractInterface     $cart
     * @param BlueprintContactInterface $blueprint
     */
    public function __construct(
        protected SessionManager            $session,
        protected Request                   $request,
        protected CartContractInterface     $cart,
        protected BlueprintContactInterface $blueprint
    )
    {
    }

    /**
     * @return mixed
     * @throws BindingResolutionException
     * @throws ValidationException
     */
    public function process(): mixed
    {
        if ($this->cart->contents()->count()) {

            $this->bootstrapOrder();
            $this->prepareOrderItems();
        }

        return $this->cart->contents();
    }

    /**
     * Initializes a new order by creating an instance of Order repository, merging request data with additional attributes,
     * creating the order, and calling the address method.
     */
    protected function bootstrapOrder(): void
    {
        $order = new OrderRepository(new Order());
        $orderAttributes = array_merge($this->request->all(), ['created_from' => OrderOrigin::FromShop]);
        $this->order = $order->create($orderAttributes);
        $this->address();
    }

    /**
     * Fetches the address based on the user and request data. Address selection depends on whether to use team address
     * settings, then syncs the address data with the delivery and invoice addresses of the order.
     */
    final public function address(): void
    {
        $user = $this->request->user();
        if (!Settings::useTeamAddress()?->value) {
            $address = $user->addresses()->where('addresses.id', $this->request->get('address'))->first();
            if (!$address) {
                $address = $user->userTeams->reject(function ($team) {
                    return !$team->address()->where('addresses.id', $this->request->get('address'))->first();
                })->map(function ($team) {
                    return $team->address()->where('addresses.id', $this->request->get('address'))->first();
                })->first();
            }
        } else {
            $address = $user->userTeams->reject(function ($team) {
                return !$team->address()->where('addresses.id', $this->request->get('address'))->first();
            })->map(function ($team) {
                return $team->address()->where('addresses.id', $this->request->get('address'))->first();
            })->first();
        }

        $this->order->delivery_address()->sync([$address->id => [
            'type' => 'delivery',
            'full_name' => $this->request->get('address_full_name'),
            'company_name' => $this->request->get('address_company_name'),
            'phone_number' => $this->request->get('address_phone_number'),
            'tax_nr' => $this->request->get('address_tax_nr'),
            'team_address' => (bool)$this->request->get('address_team_address'),
            'team_id' => $this->request->get('address_team_id'),
            'team_name' => $this->request->get('address_team_name')
        ]]);

        $this->order->invoice_address()->sync([$address->id => [
            'type' => 'invoice',
            'full_name' => $this->request->get('invoice_address_full_name'),
            'company_name' => $this->request->get('invoice_address_company_name'),
            'phone_number' => $this->request->get('invoice_address_phone_number'),
            'tax_nr' => $this->request->get('invoice_address_tax_nr'),
            'team_address' => (bool)$this->request->get('invoice_team_address'),
            'team_id' => $this->request->get('invoice_team_id'),
            'team_name' => $this->request->get('invoice_team_name')
        ]]);
    }


    /**
     * Prepares order items by calculating the total price of items in the cart with a status of 'NEW',
     * updates the order details with the total price, user ID, status, and optional reference,
     * prepares custom or print product items based on the SKU ID, cleans the cart, and returns the updated order.
     */
    protected function prepareOrderItems()
    {
        $this->total = $this->cart->contents()->where('st', Status::NEW)->sum(fn($item) => $item->price->amount());
        $this->order->update([
            'price' => $this->total,
            "user_id" => $this->request->user()->id,
            'st' => Status::NEW,
            'reference' => $this->request?->reference ?? null
        ]);
        $this->cart->contents()->where('st', Status::NEW)->map(function ($item) {

            is_numeric($item->sku_id)?
                $this->prepareCustomProductItem($item)
                : $this->preparePrintProductItem($item);

        });

        $this->cart->clean();
        return $this->order;

    }

    /**
     * Prepares a custom product item by extracting signature from the item's media path,
     * creating an order item using ItemDTO with custom details, updating pivot data with quantity and shipping cost,
     * merging additional data into the request, handling attachments, handling uploaded files,
     * processing blueprint if product has blueprint, deleting media files, and finally deleting media entries related to the item.
     *
     * @param $item The custom product item to be processed
     */
    private function prepareCustomProductItem($item): void
    {
        $signature = Str::before(Str::after($item->media->first()?->path, "{$this->request->uuid}/{$item->id}/"), '/output');
        $orderItem = $this->order->items()->create(ItemDTO::fromShopCustom($item, $signature, $this->request, Status::NEW));
        $this->order->items()->updateExistingPivot($orderItem, ['qty' => $item->qty, 'shipping_cost' => $this->request->shipping_cost]);
            $this->request->merge([
                'product' => $item->sku->product,
                'quantity' => $item->qty,
                'ns' => 'checkout',
                'variations' => $item->variation->toArray(),
                'mode' => "custom",
                'sku' => $item->sku,
                'template' => $item->template,
                'type' => 'sku',
                'signature' => $signature,
                'child' => false,
                'attachment_to' => 'order_item',
                'attachment_type' => 'output',
                'attachment_destination' => $orderItem,
                'status_from' => Status::PENDING,
                'status_to' => Status::NEW,
            ]);


        if (Storage::disk('local')->exists("{$signature}/tmp")) {
            $tmp = \File::files(Storage::disk('local')->path("{$signature}/tmp"));
            collect($tmp)->each(fn($file) => $this->request->files->set(
                    $file->getExtension(),
                    new UploadedFile(
                        $file->getPathname(),
                        $file->getBasename(),
                        $file->getExtension()
                    )
                )
            );
        }

        if($this->request->product->hasBlueprint) {
            $this->blueprint->init($this->request)->runAsPreferred();
        }

        collect($item->media)->each(fn($media) => Storage::disk($media->disk)->delete($media->path));
        // rin blueprint on order item//
        $item->media()->delete();
    }

    /**
     * Prepares and prints a product item by creating an instance of Item repository, setting the order, creating the item,
     * transferring media content from the cart to the new order item, and deleting the cart media.
     *
     * @param $item The item to prepare and print
     * @return mixed The created order item
     */
    private function preparePrintProductItem($item)
    {
        $itemRepository = new ItemRepository(new Item());

        $itemRepository->order = $this->order;
        $variation =  $item->variation->toArray();

        $orderItem = $itemRepository->create(
            array_merge([
                    'reference' => $item?->reference,
                    'delivery_separated' => $this->request->get('delivery_separated'),
                    'supplier_id' => $this->request->get('host_id'),
                    'supplier_name' => hostname()?->custom_fields?->pick('company_name') ?? hostname()->fqdn,
                    'connection' => tenant()->uuid,
                    'tenant_id' => tenant()->uuid,
                    'internal' => true,
                    'vat' => optional(optional($variation)['price'])['vat'],
                ],
                $variation
            )
        );

        $orderItem->update([
            'st' => Status::NEW,
        ]);


        collect($item->media)->each(function ($media) use ($orderItem) {
            $path = "/orders/{$this->order->id}/items/{$orderItem->id}"; // order items storage path

            $content = Storage::disk($media->disk)->get(tenant()->uuid."$media->path/{$media->name}"); // get cart item media content

            if ($content) {
                Storage::disk('tenancy')->put(tenant()->uuid."{$path}/{$media->name}", $content); // get create new

                $media->update(['model_type' => Item::class, 'model_id' => $orderItem->id, 'path' => $path, 'disk' => 'tenancy']); // transfer media from cart variation to the new order item

                Storage::disk($media->disk)->delete(tenant()->uuid."$media->path/{$media->name}"); // delete media
            }
        });
        $item->media()->delete();
        return $orderItem;
    }

}
