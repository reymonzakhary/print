<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Quotations;

use App\Actions\PriceAction\CalculationAction;
use App\Enums\AddressType;
use App\Enums\OrderOrigin;
use App\Enums\Status;
use App\Events\Tenant\Order\DeleteQuotationEvent;
use App\Events\Tenant\Order\LockQuotationEvent;
use App\Events\Tenant\Order\UnlockQuotationEvent;
use App\Events\Tenant\Quotation\QuotationAcceptedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\QuotationStoreRequest;
use App\Http\Requests\Order\QuotationUpdateRequest;
use App\Http\Resources\Quotations\QuotationActivityResource;
use App\Http\Resources\Quotations\QuotationResource;
use App\Mail\Tenant\Quotation\DeclineMail;
use App\Models\Tenants\Item;
use App\Models\Tenants\Quotation;
use App\Models\Tenants\User as TenantsUser;
use App\Models\User;
use App\Scoping\Scopes\Orders\OrderStatusScope;
use App\Scoping\Scopes\Orders\QuotationSearchScope;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class QuotationController extends Controller
{
    /**
     * default hiding field from response
     */
    private readonly array $hide;

    /**
     * default total result in one page
     */
    private readonly int $per_page;

    /**
     * @var string|mixed
     */
    private string $column = 'created_at';

    /**
     * @var string|mixed
     */
    private string $sort = 'desc';

    /**
     * UserController constructor.
     * @param Dispatcher $dispatcher
     * @param Request $request
     */
    public function __construct(
        private readonly Dispatcher $dispatcher,
        Request $request
    )
    {
        $this->per_page = $request->integer('per_page') ?? 15;
        $this->hide = !$request->boolean('include_items') ? ['items'] : [];
        $this->column = in_array($request->input('sort_by'), ['id', 'created_at', 'updated_at']) ?$request->input('sort_by'): 'created_at';
        $this->sort = $request->input('sort_order') === 'desc' ? 'desc': 'asc';
    }

    /**
     * @OA\Get(
     *     tags={"Quotation"},
     *     path="/api/v1/mgr/quotations",
     *     summary="get company address list",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/OrderResource")),
     *          @OA\Property(type="string", title="message", description="message", property="message", example="Category has been created successfully"),
     *          @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     * )
     * @return mixed
     */
    public function index(): mixed
    {
        event(new UnlockQuotationEvent(request()->user()));

        return QuotationResource::collection(
            Quotation::where(
                'orders.type',
                false
            )->whereOwnerOrAllowed()
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
                    'orders.id',
                    'orders.reference',
                    'orders.order_nr',
                    'orders.discount_id',
                    'orders.type',
                    'orders.st',
                    'orders.user_id',
                    'orders.delivery_multiple',
                    'orders.delivery_pickup',
                    'orders.shipping_cost',
                    'orders.price',
                    'orders.note',
                    'orders.created_from',
                    'orders.ctx_id',
                    'orders.expire_at',
                    'orders.editing',
                    'orders.locked',
                    'orders.locked_by',
                    'orders.locked_at',
                    'orders.created_at',
                    'orders.updated_at',
                    'orders.connection',
                    'orders.message'
                )
                ->withScopes($this->scope())
                ->orderBy($this->column, $this->sort)
                ->paginate($this->per_page)
        )->hide(
            $this->hide + ['shipping_cost', 'note']
        )->hideChildren(
            [
                'context' => [
                    'description', 'config'
                ],
                'customer' => [
                    'username',
                    'email_verified_at', 'created_at',
                    'updated_at', 'custom_field',
                    'bio', 'dob',
                    'permission', 'roles', 'teams'
                ],
                'lockedBy' => [
                    'username',
                    'email_verified_at', 'created_at',
                    'updated_at', 'custom_field',
                    'bio', 'dob',
                    'permission', 'roles', 'teams'
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
     *     path="/api/v1/mgr/quotations/{1}",
     *     summary="get company address list",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/OrderResource")),
     *          @OA\Property(type="string", title="message", description="message", property="message", example="Category has been created successfully"),
     *          @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     * )
     * @param Quotation $quotation
     * @return QuotationResource|JsonResponse
     */
    public function show(
        Quotation $quotation
    ): JsonResponse|QuotationResource
    {
        event(new LockQuotationEvent($quotation, request()->user()));

        // Load everything except items first
        $quotation->load([
            'orderedBy',
            'orderedBy.profile',
            'context',
            'services',
            'lockedBy',
            'delivery_address',
            'invoice_address',
            'delivery_address.country',
            'invoice_address.country',
            'author',
            'mailQueues'
        ]);

        // Run calculations (this loads items internally)
        $calculatedQuotation = (new CalculationAction($quotation))->calculate();

        // Now load the item relationships that depend on updated data
        $calculatedQuotation->load([
            'items.media',
            'items.media.tags',
            'items.services',
            'items.addresses',
            'items.children',
            'items.children.addresses',
        ]);

        return QuotationResource::make($calculatedQuotation)
            ->hideChildren(
                [
                    'context' => [
                        'description', 'config'
                    ],
                    'customer' => [
                        'username',
                        'email_verified_at', 'created_at',
                        'updated_at', 'custom_field',
                        'bio', 'dob',
                        'permission', 'roles', 'teams'
                    ],
                    'lockedBy' => [
                        'username',
                        'email_verified_at', 'created_at',
                        'updated_at', 'custom_field',
                        'bio', 'dob',
                        'permission', 'roles', 'teams'
                    ]
                ]
            )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null,
                'meta' => [
                    'next' => $quotation
                        ->query()
                        ->where([
                            ['id', '>', $quotation->getAttribute('id')],
                            ['type', '=', 'false']
                        ])
                        ->min('id'),
                    'prev' => $quotation
                        ->query()
                        ->where([
                                ['id', '<', $quotation->getAttribute('id')],
                                ['type', '=', 'false']
                            ]
                        )->max('id'),
                    'total' => $quotation
                        ->query()
                        ->where('type', false)
                        ->count(),
                    'current' => $quotation->getAttribute('id')
                ]
            ]);
    }

    /**
     * @OA\post(
     *     tags={"Quotation"},
     *     path="/api/v1/mgr/quotations",
     *     summary="get company address list",
     *     security={{ "Bearer":{} }},
     *   @OA\RequestBody(
     *      required=true,
     *      description="insert setting data",
     *      @OA\JsonContent(ref="#/components/schemas/QuotationStoreRequest"),
     *   ),
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/OrderResource")),
     *          @OA\Property(type="string", title="message", description="message", property="message", example="null"),
     *          @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     * )
     * @param QuotationStoreRequest $request
     * @return QuotationResource
     */
    public function store(
        QuotationStoreRequest $request
    ): QuotationResource
    {
        return QuotationResource::make(Quotation::create($request->validated()))
            ->hide(
                ['shipping_cost']
            )->hideChildren(
                [
                    'context' => [
                        'description', 'config'
                    ],
                    'customer' => [
                        'username',
                        'email_verified_at', 'created_at',
                        'updated_at', 'custom_field',
                        'bio', 'dob',
                        'permission', 'roles', 'teams'
                    ],
                    'lockedBy' => [
                        'username',
                        'email_verified_at', 'created_at',
                        'updated_at', 'custom_field',
                        'bio', 'dob',
                        'permission', 'roles', 'teams'
                    ]
                ]
            )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * @OA\Put(
     *     tags={"Quotation"},
     *     path="/api/v1/mgr/quotations/{1}",
     *     summary="get company address list",
     *     security={{ "Bearer":{} }},
     *   @OA\RequestBody(
     *      required=true,
     *      description="insert setting data",
     *      @OA\JsonContent(ref="#/components/schemas/QuotationStoreRequest"),
     *   ),
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/OrderResource")),
     *          @OA\Property(type="string", title="message", description="message", property="message", example="Quotation updated successfully"),
     *          @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     * )
     * @param QuotationUpdateRequest $request
     * @param Quotation $quotation
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(
        QuotationUpdateRequest $request,
        Quotation              $quotation
    ): JsonResponse
    {
        $attributes = $request->all();

        if ($request->address && !$request->delivery_multiple) {
            $quotation->delivery_address()->detach();
            $quotation->delivery_address()->sync([$request->address => [
                'type' => AddressType::DELIVERY->value,
                'full_name' => $request->address_full_name,
                'company_name' => $request->address_company_name,
                'phone_number' => $request->address_phone_number,
                'tax_nr' => $request->address_tax_nr,
                'team_address' => $request->address_team_address,
                'team_id' => $request->address_team_id,
                'team_name' => $request->address_team_name
            ]]);
        }else {
            $quotation->delivery_address()->detach();
        }

        if ($request->invoice_address) {
            $quotation->invoice_address()->detach();
            $quotation->invoice_address()->attach([$request->invoice_address => [
                'type' =>  AddressType::INVOICE->value,
                'full_name' => $request->invoice_address_full_name,
                'company_name' => $request->invoice_address_company_name,
                'phone_number' => $request->invoice_address_phone_number,
                'tax_nr' => $request->invoice_address_tax_nr,
                'team_address' => $request->invoice_team_address,
                'team_id' => $request->invoice_team_id,
                'team_name' => $request->invoice_team_name
            ]]);
        }else {
            $quotation->invoice_address()->detach();
        }

        $quotation->fill($request->all());

        if ($attributes['st'] === Status::NEW->value) {
            $quotation->items()
                ->whereNot('st', Status::CANCELED->value)
                ->update([
                'st' => Status::NEW->value
            ]);
        }

        if ($attributes['st'] === Status::DRAFT->value) {
            $quotation->items()->update([
                'st' => Status::DRAFT->value
            ]);
        }


        if ($quotation->save()) {

            if ($quotation->type === true) {
                $quotation->updateOrFail(['st' => Status::NEW, 'created_from' => OrderOrigin::FromQuotation]);

                $this->dispatcher->dispatch(
                    new QuotationAcceptedEvent($quotation, tenant(), auth()->user())
                );
            }
            return response()->json([
                'message' => __('Quotation updated successfully'),
                'status' => Response::HTTP_OK,

            ], Response::HTTP_OK);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('Bad request'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @OA\Delete (
     *     tags={"Quotation"},
     *     path="/api/v1/mgr/quotations/{1}",
     *     summary="get company address list",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="string", title="message", description="message", property="message", example="Quotation has been removed successfully."),
     *          @OA\Property(type="string", title="status", description="status", property="status", example="400"),
     *      )),
     * )
     * Remove the specified resource from storage.
     *
     * @param Quotation $quotation
     * @return JsonResponse
     */
    public function destroy(
        Quotation $quotation
    ): JsonResponse
    {
        if ($quotation->delete()) {
            event(new DeleteQuotationEvent($quotation , auth()->user()));
            return response()->json([
                'message' => __('Quotation has been removed successfully.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }
        /**
         * error response
         */
        return response()->json([
            'message' => __('Bad request'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @OA\Get(
     *     tags={"Quotation"},
     *     path="/api/v1/mgr/quotations/{order}/history",
     *     summary="get company address list",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/OrderActivityResource")),
     *          @OA\Property(type="string", title="message", description="message", property="message", example="Category has been created successfully"),
     *          @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     * )
     * @param Quotation $quotation
     * @return mixed
     */
    public function history(
        Quotation $quotation
    )
    {
        $orderHistory = $quotation
            ->history()
            ->with('user.profile')
            ->orderBy('id', 'desc')
            ->paginate($this->per_page);

        return QuotationActivityResource::collection($orderHistory)
            ->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * @param Request $request
     * @param Quotation $quotation
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function decline(Request $request, Quotation $quotation)
    {

        if (!auth()->user()->can('quotations-decline-update')) {
            throw ValidationException::withMessages([
                'quotation_decline' => __('Not permitted action.')
            ]);
        }

        if ($quotation->st !== Status::WAITING_FOR_RESPONSE->value) {
            return response()->json(
                [
                    'message' => 'sorry, can\'t decline order',
                    'status' => Response::HTTP_CONFLICT
                ],
                Response::HTTP_CONFLICT
            );
        }

        $quotation->update(['st' => Status::CANCELED->value]);

        // update quotation items status to be canceled
        $items_ids = $quotation->items()->get(['items.id'])->pluck('id');
        Item::whereIn('id', $items_ids)->update(['st' => Status::CANCELED->value]);

        // handel user (quotation owner)
        if ($quotation->connection === 'cec') {
            $user = User::find($quotation->user_id);
        } else {
            $user = TenantsUser::find($quotation->user_id);
        }

        try {
            Mail::to($user?->email)->send(new DeclineMail());
        } catch (Throwable $th) {
            return response()->json([
                'message' => 'quotation has been canceled but can\'t send notification email to user',
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => 'quotation has been canceled',
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    /**
     *
     * @return array
     */
    public function scope(): array
    {
        return [
            "status" => new OrderStatusScope(),
            "search" => new QuotationSearchScope(),
        ];
    }
}
