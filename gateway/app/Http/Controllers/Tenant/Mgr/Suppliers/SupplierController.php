<?php

namespace App\Http\Controllers\Tenant\Mgr\Suppliers;

use App\Foundation\ContractManager\Facades\ContractManager;
use App\Http\Controllers\Controller;
use App\Http\Resources\Categories\PrintBoopsResource;
use App\Http\Resources\Categories\PrintCategoryResource;
use App\Http\Resources\Suppliers\HostNameResource;
use App\Models\Contract;
use App\Models\Domain;
use App\Models\Supplier;
use App\Models\Tenants\Context;
use App\Models\Website;
use App\Services\Suppliers\SupplierCategoryService;
use App\Utilities\Traits\ConsumesExternalServices;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Hyn\Tenancy\Environment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Suppliers
 */
class SupplierController extends Controller
{
    use ConsumesExternalServices;

    protected array $hide = [];

    protected SupplierCategoryService $categoryService;

    public function __construct(
        SupplierCategoryService $categoryService
    )
    {
        $this->categoryService = $categoryService;
    }


    /**
     * List Suppliers
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
	 * "data": [
	 * 	{
	 * 		"id": 1,
	 * 		"uuid": "9a991fd5-ad09-40a8-88a8-95d27adc8f46",
	 * 		"host_id": "ba9ae62c-9368-4023-9802-391ce72448c6",
	 * 		"name": "test.prindustry.test",
	 * 		"logo": "http:\/\/prindustry.test\/storage\/suppliers\/9a991fd5-ad09-40a8-88a8-95d27adc8f46.jpg",
	 * 		"created_at": "2024-03-25T17:20:06.000000Z",
	 * 		"updated_at": "2024-03-25T17:20:22.000000Z",
	 * 		"supplier_info": {
	 * 			"coc": null,
	 * 			"name": "test",
	 * 			"email": "test@chd.test",
	 * 			"domain": null,
	 * 			"gender": "male",
	 * 			"tax_nr": null,
	 * 			"company_name": "test"
	 * 		}
	 * 	},
     * ],
     * "status": 200,
     * "message": null
     * }
     *
     * @return mixed
     */
    public function index()
    {

        $currentTenant = tenant(); // Store the current tenant
        $currentHost = domain();

        // Paginate websites to process in chunks
        $websites = Website::getEnabledSuppliersExceptMe()->paginate(10); // Adjust items per page as needed

        $websites->getCollection()->transform(function ($website) use ($currentTenant, $currentHost) {
            $hostname = $website->hostname;

            $hostname->uuid = $website->uuid;
            $hostname->external = $website->external;
            $hostname->supplier = $website->supplier;
            $hostname->website_id = $website?->id;
            $hostname->contract = ContractManager::getContractWithSupplier(Domain::class, $website->hostname->id);

            // Switch to the tenant's database
            app(Environment::class)->tenant($website);

            // Fetch the address in the tenant context
            $hostname->address = Context::where('name', 'mgr')
                ->with('addresses.country')
                ->first()?->addresses->first();

            // Attach hostname data to the website
            $website = $hostname;

            // Switch back to the original tenant
            app(Environment::class)->tenant($currentTenant);

            return $website;
        });

        // Switch back to the original tenant
        app(Environment::class)->tenant($currentTenant);

        // Return paginated resources
        return HostNameResource::collection($websites)
            ->hide(
                ['config']
            )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Show Supplier
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     *    "data": {
     *        "id": 1,
     *        "uuid": "9a991fd5-ad09-40a8-88a8-95d27adc8f46",
     *        "name": "test.prindustry.test",
     *        "logo": "http:\/\/prindustry.test\/storage\/",
     *        "config": null,
     *        "created_at": "2024-03-25T17:20:06.000000Z",
     *        "updated_at": "2024-03-25T17:20:06.000000Z"
     *    },
     *    "status": 200,
     *    "message": null
     * }
     *
     * @param int $id
     * @return HostNameResource|JsonResponse
     */
    public function show(
        int $id
    )
    {
        /** Load the related website database */
        $website = Website::with('hostname')->where('id', $id)->firstOrFail();
        /** check if am the requester */
        if ($website?->uuid === request()->tenant->uuid) {
            return response()->json([
                'data' => [],
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
        }

        $supplier = $website->hostname;
        $supplier->website_id = $website->id;
        $supplier->external = $website->external;
        $supplier->supplier = $website->supplier;
        $supplier->configure = $website->configure;
        $supplier->contract = ContractManager::getContractWithSupplier(Domain::class, $website->hostname->id);
        // Switch to the tenant's database
        app(Environment::class)->tenant($website);

        // Fetch the address in the tenant context
        $supplier->address = Context::where('name', 'mgr')
            ->with('addresses.country')
            ->first()?->addresses->first();

        /** Return the request supplier data */
        return HostNameResource::make(
            $supplier
        )
            ->hide(
                $this->hide
            )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * List Supplier Categories
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param Website $website
     * @param Request $request
     * @return string
     * @throws GuzzleException
     */
    public function categories(
        Website $website,
        Request  $request
    )
    {
        return PrintCategoryResource::collection(
            $this->categoryService->obtainCategories($website?->uuid, $request->all())
        )->additional([
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Show Supplier Category
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     *
     */
    public function category(
        Website $website,
        string   $category
    )
    {
        $proxy = $this->categoryService->obtainSingleCategory($website?->uuid, $category);
        if (!empty($proxy) || request()->tenant->uuid !== $website?->uuid) {
            $cat = json_decode(optional($proxy)['category'], true);
            $boop = json_decode(optional($proxy)['boop'], true);
            $boops = optional($boop)['boops'];
            $data = array_merge($cat, $boops);

            return PrintBoopsResource::make(
                $data
            )->additional([
                'status' => Response::HTTP_OK
            ]);
        } else {
            return response()->json([
                'data' => null,
                'message' => "No data in this Category",
                'status' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @param Supplier $supplier
     * @param string   $category
     * @return string
     * @throws GuzzleException
     */
    /**
     * @OA\post(
     *   tags={"Supplier category"},
     *   path="/api/v1/mgr/suppliers/{supplier_ref_id}/categories/{category}/link",
     *   summary="Supplier category",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/PrintBoopsResource")),
     *          @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found"),
     *   @OA\Response(response=422, description="Unprocessable Entity",
     *     @OA\JsonContent(
     *          @OA\Property(format="string", title="message", example="No data in this Category", description="message", property="message"),
     *          @OA\Property(format="string", title="status", example="422", description="status", property="status"),
     *     ),
     *   )
     * )
     */
    public function link(
        Request  $request,
        Website $website,
        string   $category
    )
    {
        try {
            $response = $this->categoryService
                ->obtainLinkCategorySupplier($request->only('name'), $website->uuid, $category);

            if (is_array($response)) {
                return PrintBoopsResource::make($response)->additional([
                    "message" => __("Category has been updated successfully"),
                    "status" => Response::HTTP_CREATED,
                ]);
            }
            $response = json_decode($response->getBody()->getContents());
            return response()->json([
                'message' => $response->message,
                "status" => $response->status
            ], $response->status);
        } catch (Exception $e) {
            /**
             * error response
             */
            return response()->json([
                'message' => __('categories.bad_request'),
                'status' => Response::HTTP_BAD_REQUEST

            ], Response::HTTP_BAD_REQUEST);
        }

    }
}
