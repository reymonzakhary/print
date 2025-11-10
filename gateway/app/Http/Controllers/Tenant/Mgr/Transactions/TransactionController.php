<?php

namespace App\Http\Controllers\Tenant\Mgr\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Resources\Transactions\TransactionResource;
use App\Models\Tenants\Transaction;
use App\Scoping\Scopes\Transactions\TransactionCompanyScope;
use App\Scoping\Scopes\Transactions\TransactionContractScope;
use App\Scoping\Scopes\Transactions\TransactionDueDateScope;
use App\Scoping\Scopes\Transactions\TransactionInvoiceNrScope;
use App\Scoping\Scopes\Transactions\TransactionOrderScope;
use App\Scoping\Scopes\Transactions\TransactionPaymentMethodScope;
use App\Scoping\Scopes\Transactions\TransactionSearchScope;
use App\Scoping\Scopes\Transactions\TransactionStatusScope;
use App\Scoping\Scopes\Transactions\TransactionTeamScope;
use App\Scoping\Scopes\Transactions\TransactionTypeScope;
use App\Scoping\Scopes\Transactions\TransactionUserScope;
use App\Utilities\Order\Transaction\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Transactions
 */
class TransactionController extends Controller
{

    /**
     * default hiding field from response
     * example hide=id,invoice_nr
     */
    protected array $hide;

    /**
     * default sorting
     */
    protected string $sort = 'DESC';

    /**
     * Load transaction relations dynamically
     */
    protected array $load;

    /**
     * default total result in one page
     */
    protected int $per_page = 10;

    /**
     * TransactionController constructor.
     * @param Request $request
     */
    public function __construct(
        Request $request
    )
    {
        $this->hide = explode(',', $request->get('hide'));
        $this->load = !is_null($request->get('load')) ? explode(',', $request->get('load')) : [];
        $this->sort = $request->get('sort') ?? $this->sort;
        $this->per_page = (int) $request->get('per_page') ?? $this->per_page;
    }

    /**
     * List Transactions
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     *  "data": [
     *      {
	 *		"id": 1,
	 *	    },
     *    ],
     * "links":{},
     * "meta":{},
     * "status":200,
     * "message": "Transactions retrieved successfully."
     * }
     *
     */
    public function index()
    {
        $transactions = request()->get('search')
            ? $this->getPaginatedTransactions()
            : $this->fixMalformedTransactions($this->getPaginatedTransactions());

        return TransactionResource::collection($transactions)
            ->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => __('Transactions retrieved successfully.')
            ]);
    }

    /**
     * show Transaction
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam transaction_id integer required The ID of the transaction.
     *
     * @response 200
     * {
     *    "data": {
     *        "id": 1,
     *    },
     *    "status": 200,
     *    "message": "Transaction retrieved successfully."
     * }
     * @param Transaction $transaction
     * @return TransactionResource
     */
    public function show(
        Transaction $transaction
    ): TransactionResource
    {
        return TransactionResource::make($transaction->load($this->load))
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => __('Transaction retrieved successfully.')
            ]);
    }

    /**
     * @return array
     */
    public function scope(): array
    {
        return [
            "status" => new TransactionStatusScope(),
            "invoice_nr" => new TransactionInvoiceNrScope(),
            "payment_method" => new TransactionPaymentMethodScope(),
            "order" => new TransactionOrderScope(),
            "user" => new TransactionUserScope(),
            "team" => new TransactionTeamScope(),
            "company" => new TransactionCompanyScope(),
            "contract" => new TransactionContractScope(),
            "due_date" => new TransactionDueDateScope(),
            "type" => new TransactionTypeScope(),
            "search" => new TransactionSearchScope(),
        ];
    }

    /**
     * Scan a collection of transactions for malformed product data,
     * and regenerate any transaction where issues are found.
     *
     * This typically addresses cases where product `shipping_cost` is a string,
     * which would otherwise cause type errors during rendering or calculations.
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $transactions
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */    
    private function fixMalformedTransactions(
        LengthAwarePaginator $transactions
    ): LengthAwarePaginator
    {
        $malformedTransactions = collect([]);


        foreach($transactions as $transaction) {
            // Check each product in the transaction's custom_field
            foreach($transaction->custom_field->pick('products') as $product) {
                // If the shipping_cost is a string, flag the transaction as malformed
                if(is_string($product['shipping_cost'])) {
                    $malformedTransactions->push($transaction);
                    break;  //no need to check more
                }
            }
        }

        if($malformedTransactions->count()) {    //If any malformed transactions?   
            $transactionService = new TransactionService;
            foreach($malformedTransactions as $transaction)
            {
                $order = $transaction->order;   //get the order
                $transaction->forceDelete();    //remove the old transaction
                $transactionService->performFreshCreationForTheTransaction($order); // regenerate a new one,
            }
            $transactions = $this->getPaginatedTransactions();  //and refetch'em again
        }
        return $transactions;
    }


    /**
     * Build the paginated query for fetching transactions.
     *
     * Applies filters, sorting, eager loading, and pagination.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    private function getPaginatedTransactions(): LengthAwarePaginator
    {
        return Transaction::query()
            ->when($this->load, function ($query) {
                $query->with($this->load);
            })
            ->withScopes($this->scope())
            ->orderBy('transactions.id', $this->sort)
            ->paginate($this->per_page);
            
    }
}
