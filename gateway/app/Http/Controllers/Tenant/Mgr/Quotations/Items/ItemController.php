<?php

namespace App\Http\Controllers\Tenant\Mgr\Quotations\Items;

use App\Http\Controllers\Controller;
use App\Http\Requests\Items\ItemStoreRequest;
use App\Http\Requests\Items\QuotationItemUpdateRequest;
use App\Http\Resources\Items\QuotationItemResource;
use App\Http\Resources\Items\QuotationItemResourceCollection;
use App\Models\Tenants\Item;
use App\Models\Tenants\Quotation;
use App\Repositories\ItemRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ItemController extends Controller
{
    /**
     * @var ItemRepository
     */
    protected ItemRepository $items;

    /**
     * default hiding field from response
     */
    protected array $hide;

    /**
     * default total result in one page
     */
    protected int $per_page = 5;

    /**
     * UserController constructor.
     * @param Request $request
     * @param Item    $item
     */
    public function __construct(
        Request $request,
        Item    $item
    )
    {
        $this->items = new ItemRepository($item);
        /**
         * default hidden field
         */
        $this->hide = [];

        /**
         * default number of pages
         */
        $this->per_page = $request->get('per_page') ?? $this->per_page;
    }

    /**
     * @param Quotation $quotation
     * @return QuotationItemResource|QuotationItemResourceCollection
     */
    public function index(
        Quotation $quotation
    ): QuotationItemResource|QuotationItemResourceCollection
    {
        $this->items->order = $quotation;

        return QuotationItemResource::collection($this->items->all())->hide($this->hide);
    }

    /**
     * @param Quotation        $quotation
     * @param ItemStoreRequest $request
     * @return QuotationItemResource
     */
    public function store(
        Quotation        $quotation,
        ItemStoreRequest $request
    ): QuotationItemResource
    {
        $this->items->order = $quotation;
        $item = $this->items->create($request->validated());

        $price = 0;

        collect($this->items->order->items()->get())->each(function ($item) use (&$price) {
            $price += (int)optional($item->price)['p'];
        });

        $this->items->order->update(['price' => $price]);

        return QuotationItemResource::make(
            $this->items->show($item->id)
        )->additional([
            'message' => __("Item has been created successfully!"),
            "status" => Response::HTTP_OK
        ])
            ->hide($this->hide);
    }


    /**
     * Update an item
     *
     * @param QuotationItemUpdateRequest $request
     * @param Quotation $quotation
     * @param int $id
     * @return JsonResponse|QuotationItemResource
     */
    public function update(
        QuotationItemUpdateRequest $request,
        Quotation         $quotation,
        int               $id
    ): JsonResponse|QuotationItemResource
    {
        $this->items->order = $quotation;

        if (!$quotation->items()->where('items.id', $id)->exists()) {
            return response()->json([
                'message' => __('items.not_found'),
                'status' => Response::HTTP_NOT_FOUND

            ], Response::HTTP_NOT_FOUND);
        }

        if ($this->items->update($id, $request->all())) {
            $price = 0;

            collect($this->items->order->items()->get())->each(function ($item) use (&$price) {
                $price += (int)$item->product['price']['p'];
            });

            $this->items->order->update(['price' => $price]);

            return QuotationItemResource::make($quotation->items()->where('items.id', $id)->first())->hide($this->hide);
        }
        /**
         * error response
         */
        return response()->json([
            'message' => __('items.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Quotation $quotation
     * @param int $id
     * @return JsonResponse|Response
     */
    public function destroy(
        Quotation $quotation,
        int       $id
    )
    {
        $this->items->order = $quotation;

        if (!$quotation->items()->where('items.id', $id)->exists()) {
            return response()->json([
                'message' => __('items.not_found'),
                'status' => Response::HTTP_NOT_FOUND

            ], Response::HTTP_NOT_FOUND);
        }

        if ($this->items->delete($id)) {
            /**
             * error response
             */
            return response()->json([
                'data' => [
                    'message' => __('items.item_removed')
                ]
            ], Response::HTTP_ACCEPTED);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('item.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }
}
