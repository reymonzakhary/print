<?php

namespace App\Http\Controllers\Tenant\Mgr\Categories;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\StoreBoopsRequest;
use App\Http\Requests\Categories\UpdateBoopsRequest;
use App\Http\Resources\Categories\PrintBoopsResource;
use App\Services\Categories\BoopsService;
use App\Services\Tenant\Categories\SupplierCategoryService as CategoryService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BoopsController extends Controller
{

    /**
     * @var BoopsService
     */
    protected BoopsService $boopsService;

    /**
     * default hiding field from response
     */
    protected array $hide;

    /**
     * default total result in one page
     */
    protected int $per_page = 10;

    /**
     * ContextController constructor.
     * @param Request      $request
     * @param BoopsService $boopsService
     */
    public function __construct(
        Request      $request,
        BoopsService $boopsService
    )
    {
        $this->boopsService = $boopsService;

        /**
         * default number of pages
         */
        $this->per_page = $request->get('per_page') ?? $this->per_page;
    }

    /**
     * @param Request $request
     * @param string  $category
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function index(
        Request $request,
        string  $category
    )
    {
        $proxy = $this->boopsService->obtainCategoryBoops($category);
        if (isset($proxy) && isset($proxy['message']) && isset($proxy['status']) && !in_array($proxy['status'], [200, 201])) {
            return response()->json([
                'message' => "Error",
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }
        return response()->json([
            'data' => PrintBoopsResource::make($proxy),
            'message' => null,
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);

    }

    /**
     * @param StoreBoopsRequest $request
     * @param string            $category
     * @return PrintBoopsResource
     * @throws GuzzleException
     */
    public function store(
        StoreBoopsRequest $request,
        string            $category
    )
    {
        return PrintBoopsResource::make($this->boopsService->obtainCreateCategoryBoops($category, $request->validated()));
    }

    /**
     * @param UpdateBoopsRequest $request
     * @param string $category
     * @return PrintBoopsResource|string
     */
    public function update(
        UpdateBoopsRequest $request,
        string             $category,
    )
    {
        $response =  app(CategoryService::class)->updateCategoryBoops($request->validated(),$category);

        if (! in_array($response['status'], [Response::HTTP_OK], Response::HTTP_CREATED)) {
            return response()->json([
                'message' => $response['message'],
                "status" => $response['status']
            ], $response['status']);
        }

        return PrintBoopsResource::make($response['data'])
            ->additional([
            "message" => __("Category has been updated successfully"),
            "status" => Response::HTTP_OK,
        ]);

    }

    /**
     * Add boxes and options if not exists
     * @param UpdateBoopsRequest $request
     * @param string $category
     * @return JsonResponse|PrintBoopsResource
     * @throws GuzzleException
     */
    public function openProduct(
        UpdateBoopsRequest $request,
        string             $category,
    )
    {
        $proxy = $this->boopsService->obtainOpenProductCategoryBoops($category, $request->validated());

        if (isset($proxy['data'])) {
            return PrintBoopsResource::make($proxy['data'])->additional([
                'message' => __('Boops has been updated successfully.'),
                'status' => Response::HTTP_OK
            ]);
        } elseif (isset($proxy['message'])) {
            return response()->json([
                'message' => __($proxy['message']),
                'status' => $proxy['status']
            ], $proxy['status']);
        }
        return response()->json([
            'message' => __("We can't handle this request"),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

}
