<?php

namespace App\Http\Controllers\Tenant\Mgr\Orders;

use App\Actions\PriceAction\CalculationAction;
use App\Enums\OrderOrigin;
use App\Enums\Status as EnumsStatus;
use App\Events\Tenant\Order\ArchiveOrderForCustomerEvent;
use App\Events\Tenant\Order\CreateOrderForCustomerEvent;
use App\Events\Tenant\Order\LockOrderEvent;
use App\Events\Tenant\Order\UnlockOrderEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderStoreRequest;
use App\Http\Requests\Order\OrderUpdateRequest;
use App\Http\Resources\Orders\OrderActivityResource;
use App\Http\Resources\Orders\OrderResource;
use App\Mail\Tenant\Order\OrderConfirmationMail;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use App\Repositories\OrderRepository;
use App\Scoping\Scopes\Orders\OrderArchivedScope;
use App\Scoping\Scopes\Orders\OrderSearchScope;
use App\Scoping\Scopes\Orders\OrderStatusScope;
use App\Utilities\Cache\RedisCache;
use App\Utilities\Order\OrderHasher;
use App\Utilities\Order\Transaction\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

final class OrderController extends Controller
{
    /**
     * @var OrderRepository
     */
    protected OrderRepository $order;

    /**
     * default hiding field from response
     */
    protected array $hide;

    /**
     * default total result in one page
     */
    protected int $per_page = 15;

    /**
     * @var bool
     */
    protected bool $type = false;

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
     * @param Request $request
     * @param Order   $order
     */
    public function __construct(
        Request $request,
        Order   $order,
        private readonly OrderHasher $hasher
    )
    {
        $this->order = new OrderRepository($order);

        /**
         * default number of pages
         */
        $this->per_page = $request->get('per_page') ?? $this->per_page;
        $this->hide[] = $request->get('include_items') ?? 'items';
        $this->column = in_array($request->input('sort_by'), ['id', 'created_at', 'updated_at']) ?$request->input('sort_by'): 'created_at';
        $this->sort = $request->input('sort_order') === 'desc' ? 'desc': 'asc';
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection|mixed
     */
    public function index(): mixed
    {
        event(new UnlockOrderEvent(request()->user()));
        $orders = $this->order->all(
            $this->per_page,
            scopes: $this->scope(),
            order_by: $this->column,
            order_dir: $this->sort
        );

        /**
         * check if we have users
         */
        if ($orders->items()) {
            return OrderResource::collection($orders)
                ->hide(
                    $this->hide + ['shipping_cost']
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
         * empty orders
         */
        return OrderResource::collection($orders)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => __('orders.no_order_available')
            ]);
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @param TransactionService $transaction_service
     *
     * @return OrderResource|JsonResponse
     */
    public function show(
        int $id,
        TransactionService $transaction_service
    ): JsonResponse|OrderResource
    {
        if ($order = $this->order->show($id)) {
            if(!$order->archived){
                event(new LockOrderEvent($order, request()->user()));
            }

            // Load everything except items first

            $order->load([
                'orderedBy',
                'orderedBy.profile',
                'context',
                'services',
                'lockedBy',
                'delivery_address',
                'invoice_address',
                'delivery_address.country',
                'invoice_address.country',
                'author'
            ]);

            // Run calculations (this loads items internally)
            $calculatedOrder = (new CalculationAction($order))->calculate();

            /* TEMP for backward compatability */
            $transaction_service->createTransactionIfOrderInApplicableStateForAutoTransactionCreation($calculatedOrder);
            $transaction_service->provisionTransactionsBasedOnOrder($calculatedOrder);
            /* End Temp */

            // Now load the item relationships that depend on updated data
            $calculatedOrder->load([
                'items.media',
                'items.media.tags',
                'items.services',
                'items.addresses',
                'items.children',
                'items.children.addresses',
            ]);

            return OrderResource::make($calculatedOrder)
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
                        'next' => $this->order->getNextIndexOfOrder($order),
                        'prev' => $this->order->getPreviousIndexOfOrder($order),
                        'total' => $this->order->getTotalOrders(),
                        'current' => $order->id
                    ]
                ]);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('orders.not_found'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param OrderStoreRequest $request
     * @return OrderResource|JsonResponse
     */
    public function store(
        OrderStoreRequest $request
    ): JsonResponse|OrderResource
    {

        if ($order = $this->order->create($request->validated())) {
            event(new LockOrderEvent($order, request()->user()));

            return OrderResource::make(
                $order
            )->hide(
                ['shipping_cost']
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
                    ]
                ]
            )
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => null,
                ]);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __("We couldn't handle your create request!"),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param OrderUpdateRequest $request
     * @param Order $order
     * @param TransactionService $transaction_service
     *
     * @return JsonResponse
     */
    public function update(
        OrderUpdateRequest $request,
        Order $order,
        TransactionService $transaction_service
    ): JsonResponse {
        if ($request->input('address') && !$request->input('delivery_multiple')) {
            $order->delivery_address()->detach();
            $order->delivery_address()->sync([
                $request->input('address') => [
                    'type' => $request->input('address_type'),
                    'full_name' => $request->input('address_full_name'),
                    'company_name' => $request->input('address_company_name'),
                    'phone_number' => $request->input('address_phone_number'),
                    'tax_nr' => $request->input('address_tax_nr'),
                    'team_address' => $request->input('address_team_address'),
                    'team_id' => $request->input('address_team_id'),
                    'team_name' => $request->input('address_team_name'),
                ]
            ]);
        } else {
            $order->delivery_address()->detach();
        }
        if ($request->input('invoice_address')) {
            $order->invoice_address()->detach();
            $order->invoice_address()->attach([
                $request->input('invoice_address') => [
                    'type' => $request->input('invoice_address_type'),
                    'full_name' => $request->input('invoice_address_full_name'),
                    'company_name' => $request->input('invoice_address_company_name'),
                    'phone_number' => $request->input('invoice_address_phone_number'),
                    'tax_nr' => $request->input('invoice_address_tax_nr'),
                    'team_address' => $request->input('invoice_team_address'),
                    'team_id' => $request->input('invoice_team_id'),
                    'team_name' => $request->input('invoice_team_name'),
                ]
            ]);
        } else {
            $order->invoice_address()->detach();
        }
        $order_status_before_update = $order->st;
        $is_order_editing = $order->editing;
        if ($request->archived  && !$order->archived) {
            event(new ArchiveOrderForCustomerEvent($order, $order->orderedBy ));
        }
        $order->fill($request->all());

        if ($request->input('st') === EnumsStatus::NEW->value) {
            collect($order->items()->get())->map(
                function (Item $item) use ($order): void {
                    if (
                        in_array($item->getAttribute('st'), [
                            EnumsStatus::NEW->value,
                            EnumsStatus::DRAFT->value
                        ], true)
                    ) {
                        $item->updateOrFail([
                            'st' => EnumsStatus::NEW->value
                        ]);
                    }
                }
            );

            if ($order_status_before_update === EnumsStatus::DRAFT->value) {
                event(new CreateOrderForCustomerEvent($order , $order->orderedBy));
            }
        }


        if (!$order->save()) {
            return response()->json([
                'message' => __("We couldn't handle your update request!"),
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY

            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Calculate the order for transaction creation
        $calculatedOrder = (new CalculationAction($order))->calculate();

        $transaction_service->createTransactionIfOrderInApplicableStateForAutoTransactionCreation($calculatedOrder);
        $transaction_service->provisionTransactionsBasedOnOrder($calculatedOrder);
         if ($is_order_editing && !$request->input('editing')) { // Only Fire when order is not in editing state (Done Editing Order )
                $old_hashed_value = RedisCache::get("hasher_order_" . $order->id);
                if (! $this->hasher->verify($order, $old_hashed_value)){  // Order Has Changes
                    Mail::to($order->orderedBy->email)->queue(new OrderConfirmationMail($order));
                    RedisCache::forget("hasher_order_" . $order->id);

                }
            } else if (!$is_order_editing && $request->input('editing')) {
                $hashed_value = $this->hasher->generate($order);
                RedisCache::forever("hasher_order_" . $order->id, $hashed_value);
            }
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => __('Order updated successfully'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(
        int $id
    ): JsonResponse
    {

        if ($this->order->delete($id)) {
            return response()->json([
                'message' => __('Order has been removed successfully.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }
        /**
         * error response
         */
        return response()->json([
            'message' => __('orders.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param Order $order
     * @return mixed
     */
    public function history(
        Order $order
    )
    {
        $orderHistory = $order->history()->with('user')->orderBy('id', 'desc')->paginate($this->per_page);

        return OrderActivityResource::collection($orderHistory)
            ->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     *
     * @return array
     */
    public function scope()
    {
        return [
            "archived" => new OrderArchivedScope(),
            "status" => new OrderStatusScope(),
            "search" => new OrderSearchScope(),
        ];
    }
}
