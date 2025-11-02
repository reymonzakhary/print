<?php

namespace App\Http\Controllers\System\V2\Mgr\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\Categories\PrintBoopsResource;
use App\Http\Resources\Categories\SystemManifestResource;
use App\Services\System\Categories\CategoryService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class ManifestController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param CategoryService $categoryService
     */
    public function __construct(
        protected CategoryService $categoryService
    ){}

    /**
     * @throws GuzzleException
     */
    public function show(
        string $category
    )
    {
        if($category === 'undefined') {
            return response()->json([
                'message' => __("Undefined is not a category!"),
                'status' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->categoryService->obtainSystemCategoryManifest($category)?
            SystemManifestResource::make($this->categoryService->obtainSystemCategoryManifest($category)):
            [
                "data" => []
            ];
    }

    /**
     * Store a new system category manifest.
     *
     * @param Request $request The request containing the data for the new system category manifest
     * @param string $category The category to associate with the system category manifest
     * @return SystemManifestResource|JsonResponse
     * @throws GuzzleException
     */
    public function store(
        Request $request,
        string $category
    ): SystemManifestResource|JsonResponse
    {
        $proxy = $this->categoryService->storeSystemCategoryManifest($request->all(), $category);
        if(!data_get($proxy, '_id.$oid')) {
          return response()->json([
              'message' => __($proxy['message']),
              'status' => optional($proxy)['status']?? Response::HTTP_UNPROCESSABLE_ENTITY
          ], optional($proxy)['status']?? Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        return SystemManifestResource::make(
            $proxy
        )->additional([
            'message' => __('Manifest has been created successfully!'),
            'status' => Response::HTTP_CREATED
        ]);
    }

    /**
     * Update an existing system category manifest.
     *
     * @param Request $request The request containing the data for updating the system category manifest
     * @param string $category The category associated with the system category manifest to update
     * @return JsonResponse JSON response indicating success or failure of the update operation
     * @throws GuzzleException
     */
    public function update(
        Request $request,
        string $category,
    )
    {
        $proxy = $this->categoryService->updateSystemCategoryManifest($request->all(), $category);
        if(optional($proxy)['status'] !== Response::HTTP_OK) {
            return response()->json([
                'message' => $proxy['message'],
                'status' => $proxy['status']
            ], $proxy['status']);
        }
        return response()->json([
            'message' => __('Manifest has been updated successfully!'),
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Retrieve the linked supplier manifest for a given category and supplier ID.
     *
     * @param string $category The category to retrieve the linked supplier manifest for.
     * @param string $supplier_id The ID of the supplier.
     * @return JsonResponse|JsonResource
     * @throws GuzzleException
     */
    public function linked(
        string $category,
        string $supplier_id
    )
    {
        $proxy = $this->categoryService->obtainLinkedSupplierManifest($category, $supplier_id);

        if(data_get($proxy, 'status') === Response::HTTP_UNPROCESSABLE_ENTITY) {
            return response()->json([
                'message' => __('Manifest not found!'),
                'status' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
        return PrintBoopsResource::make(
            $proxy
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }


        /**
     * Retrieve the linked supplier manifest for a given category and supplier ID.
     *
     * @param string $category The category to retrieve the linked supplier manifest for.
     * @param string $supplier_id The ID of the supplier.
     * @return JsonResponse|JsonResource
     * @throws GuzzleException
     */
    public function supplierManifest(
        string $category,
        string $supplier_id
    )
    {
        $proxy = $this->categoryService->obtainSupplierManifest($category, $supplier_id);

        if(data_get($proxy, 'status') === Response::HTTP_UNPROCESSABLE_ENTITY) {
            return response()->json([
                'message' => __('Manifest not found!'),
                'status' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
        return PrintBoopsResource::make(
            $proxy
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }
}
