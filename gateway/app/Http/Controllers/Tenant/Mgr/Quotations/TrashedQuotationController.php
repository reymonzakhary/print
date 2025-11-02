<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Quotations;

use App\Actions\PriceAction\CalculationAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Quotations\QuotationResource;
use App\Http\Resources\Quotations\TrashedQuotationResource;
use App\Models\Tenants\Quotation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class TrashedQuotationController extends Controller
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
        $this->model = Quotation::onlyTrashed();
    }

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     tags={"Quotation"},
     *     path="/api/v1/mgr/quotations/trashed",
     *     summary="get trashed quotations list",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200", description="success",
     * )
     *
     * @return mixed
     */
    public function index(): mixed
    {
        return TrashedQuotationResource::collection(
            $this->model
                ->where('type', false)
                ->whereOwnerOrAllowed()
                ->with([
                    'orderedBy' => function ($qs) {
                        return $qs->select(
                            'id',
                            'email'
                        );
                    },
                    'orderedBy.profile',
                    'lockedBy' => function ($qs) {
                        return $qs->select(
                            'id',
                            'email'
                        );
                    },
                    'context' => function ($qs) {
                        return $qs->select(
                            'id',
                            'name'
                        );
                    },
                    'delivery_address',
                    'invoice_address',
                    'services',
                    'items',
                    'items.media',
                    'items.media.tags',
                    'items.services',
                    'items.addresses',
                    'items.children',
                    'items.children.addresses',
                ])
                ->select(
                    'id',
                    'reference',
                    'order_nr',
                    'discount_id',
                    'type',
                    'st',
                    'user_id',
                    'delivery_multiple',
                    'delivery_pickup',
                    'shipping_cost',
                    'price',
                    'note',
                    'created_from',
                    'ctx_id',
                    'expire_at',
                    'editing',
                    'locked',
                    'locked_by',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'connection',
                    'message'
                )
                ->orderBy('created_at', 'desc')
                ->paginate($this->per_page)
        )->hide(
            $this->hide + ['shipping_cost', 'note']
        )->hideChildren(
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
                'message' => null
            ]);
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
     * @return QuotationResource|JsonResponse
     */
    public function show(
        int $id
    ): JsonResponse|QuotationResource {
        $quotation = $this->model->findOrFail($id);

        return QuotationResource::make(
            (new CalculationAction($quotation->load([
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
                    'Trashed quotation has been retrieved successfully'
                ),
                'meta' => [
                    'next' => $quotation->where([['id', '>', $quotation->id], ['type', '=', 'false']])->min('id'),
                    'prev' => $quotation->where([['id', '<', $quotation->id], ['type', '=', 'false']])->min('id'),
                    'total' => $quotation->where('type', false)->count(),
                    'current' => $quotation->getAttribute('id')
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
    ): JsonResponse {
        $quotation = $this->model->findOrFail($id);

        if (!$quotation->restore()) {
            return response()->json([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => __('Could not restore the quotation'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => __('Quotation has been restored successfully'),
        ]);
    }
}
