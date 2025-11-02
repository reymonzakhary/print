<?php

namespace App\Http\Controllers\Tenant\Mgr\Orders\Items\Blueprints;

use App\Blueprints\Contracts\BlueprintContactInterface;
use App\Foundation\Status\Status;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use App\Models\Tenants\Sku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class BlueprintController extends Controller
{
    public function __invoke(
        Order $order,
        Item $item,
        Request $request
    )
    {
        if (!$order->items()->where('items.id', $item->id)->exists()) {
            return response()->json([
                'message' => __('There is no blueprint for the item.'),
                'status' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        $product = (object) optional($order->items()->where('items.id', $item->id)->first())->product?->toArray();
        if($product->custom) {
            $signature = $product->signature;
            $sku = Sku::find($product->price['id'])->with('product')->first();
            $qty = $product->price['qty'];

            $request->merge([
                'product' => $sku->product,
                'quantity' => $qty,
                'ns' => 'checkout',
                'variations' => $product->hasVariation? $product->variation:null,
                'mode' => "custom",
                'sku' => $sku,
                'template' => $sku->template,
                'type' => 'sku',
                'signature' => $signature,
                'child' => false,
                'attachment_to' => 'order_item',
                'attachment_type' => 'output',
                'attachment_destination' => $item,
                'status_from' => Status::PENDING,
                'status_to' => Status::NEW,
            ]);

            if (Storage::disk('local')->exists("{$signature}/tmp")) {
                $tmp = \File::files(Storage::disk('local')->path("{$signature}/tmp"));
                collect($tmp)->each(fn($file) => $request->files->set(
                    $file->getExtension(),
                    new UploadedFile(
                        $file->getPathname(),
                        $file->getBasename(),
                        $file->getExtension()
                    )
                )
                );
            }
            app(BlueprintContactInterface::class)->init($request)->runAsPreferred();
            collect($item->media)->each(fn($media) => Storage::disk($media->disk)->delete($media->path));
            // rin blueprint on order item//
            $item->media()->delete();

            return response()->json([
                'data' => null,
                'message' => __('Order item blueprint has been restarted.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            'data' => null,
            'message' => __('This item did not have a blueprint.'),
            'status' => Response::HTTP_FAILED_DEPENDENCY
        ], Response::HTTP_FAILED_DEPENDENCY);
    }
}
