<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Orders;

use App\Actions\PriceAction\CalculationAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Orders\OrderResource;
use App\Http\Resources\Orders\TrashedOrderResource;
use App\Models\Tenant\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class TrashedOrderController extends Controller
{
    /**
     * default hiding field from response
     */
    private array $hide;

    /**
     * default total result in one page
     */
    private int $per_page = 15;

    /**
     * @var Builder
     */
    private readonly Builder $model;

    /**
     * @param Request $request
     */
    public function __construct(
        Request $request
    ) {
        $this->per_page = $request->integer('per_page') ?? $this->per_page;
        $this->hide[] = $request->get('include_items') ?? 'items';
        $this->model = Order::onlyTrashed();
    }

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     tags={"Order"},
     *     path="/api/v1/mgr/orders/trashed",
     *     summary="get trashed orders list",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200", description="success",
     * )
     *
     * @return mixed
     */
    public function index(): mixed
    {
        return TrashedOrderResource::collection(
            $this->model
                ->where('type', true)
                ->whereOwnerOrAllowed()
                ->with([
                    'orderedBy' => function ($qs) {
                        return $qs->select(
                            'id',
                            'email'
                        );
                    },
                    'lockedBy' => function ($qs) {
                        return $qs->select(
                            'id',
                            'email'
                        );
                    },
                    'orderedBy.profile',
                    'context' => function ($qs) {
                        return $qs->select(
                            'id',
                            'name'
                        );
                    },
                    'delivery_address',
                    'invoice_address',
                    'services',
                    'services.media',
                    'services.media.tags',
                    'team',
                    'items',
                    'items.media',
                    'items.media.tags',
                    'items.services',
                    'items.services.media',
                    'items.services.media.tags',
                    'items.addresses',
                    'items.children',
                    'items.children.addresses',
                ])
                ->select(
                    'id',
                    'reference',
                    'note',
                    'order_nr',
                    'discount_id',
                    'type',
                    'st',
                    'st_message',
                    'delivery_multiple',
                    'delivery_pickup',
                    'shipping_cost',
                    'ctx_id',
                    'team_id',
                    'user_id',
                    'created_from',
                    'properties',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'expire_at',
                    'editing',
                    'locked',
                    'locked_at',
                    'locked_by',
                    'message',
                    'archived'
                )
                ->orderBy('order_nr', 'desc')
                ->paginate($this->per_page)
        );
    }

    /**
     * @OA\Get(
     *     tags={"Quotation"},
     *     path="/api/v1/mgr/quotations/trashed/{1}",
     *     summary="Show a tarshed quotation",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/OrderResource")),
     *          @OA\Property(type="string", title="message", description="message", property="message", example="Category has been created successfully"),
     *          @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     * )
     * @param int $id
     * @return OrderResource|JsonResponse
     */
    public function show(
        int $id
    ): JsonResponse|OrderResource
    {
        $order = $this->model->findOrFail($id);

        return OrderResource::make(
            (new CalculationAction($order->load([
                'orderedBy',
                'orderedBy.profile',
                'context',
                'items',
                'items.media',
                'items.media.tags',
                'items',
                'items.services',
                'items.addresses',
                'items.children',
                'items.children.addresses',
                'services',
                'lockedBy',
                'invoice_address',
                'delivery_address',
                'delivery_address.country',
                'invoice_address.country',
                'author',
                'mailQueues'
            ])))->Calculate()

        )
            ->hideChildren(
                [
                    'context' => [
                        'description',
                        'config'
                    ],
                    'customer' => [
                        'username',
                        'email_verified_at',
                        'created_at',
                        'updated_at',
                        'custom_field',
                        'bio',
                        'dob',
                        'permission',
                        'roles',
                        'teams'
                    ],
                    'lockedBy' => [
                        'username',
                        'email_verified_at',
                        'created_at',
                        'updated_at',
                        'custom_field',
                        'bio',
                        'dob',
                        'permission',
                        'roles',
                        'teams'
                    ]
                ]
            )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => __(
                    'Trashed order has been retrieved successfully'
                ),
                'meta' => [
                    'next' => $order->where([['id', '>', $order->getAttribute('id')], ['type', '=', 'true']])->min('id'),
                    'prev' => $order->where([['id', '<', $order->getAttribute('id')], ['type', '=', 'true']])->min('id'),
                    'total' => $order->where('type', true)->count(),
                    'current' => $order->getAttribute('id')
                ]
            ]);
    }

    /**
     * Restore a specific resource
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function restore(
        int $id
    ): JsonResponse
    {
        $order = $this->model->findOrFail($id);

        if (!$order->restore()) {
            return response()->json([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => __('Could not restore the order'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => __('Order has been restored successfully'),
        ]);
    }
}
