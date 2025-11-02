<?php

namespace App\Http\Controllers\Tenant\Mgr\DesignProviders;

use App\Http\Controllers\Controller;
use App\Http\Requests\DesignProviders\UpdateDesignProviderRequest;
use App\Http\Resources\DesignProviders\DesignProviderResource;
use App\Models\Tenants\DesignProvider;
use App\Repositories\DesignProviderRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DesignProviderController extends Controller
{
    /**
     * @var DesignProviderRepository
     */
    protected DesignProviderRepository $designProvider;

    /**
     * default total result in one page
     */
    protected int $per_page = 10;

    /**
     * UserController constructor.
     * @param Request        $request
     * @param DesignProvider $designProvider
     */
    public function __construct(
        Request        $request,
        DesignProvider $designProvider
    )
    {
        $this->designProvider = new DesignProviderRepository($designProvider);
        /**
         * default hidden field
         */
        $this->hide = [

        ];

        /**
         * default number of pages
         */
        $this->per_page = $request->get('per_page') ?? $this->per_page;
    }

    /**
     * Obtain paginated designProvider
     * @return mixed
     */
    public function index()
    {
        /**
         * check if we have designProvider
         */
        return DesignProviderResource::collection($this->designProvider->all())->hide(
            $this->hide
        )->additional([
            'status' => Response::HTTP_OK,
            'message' => null
        ]);
    }

    /**
     * obtain single designProvider
     * @param DesignProvider $designProvider
     * @return DesignProviderResource
     */
    public function show(
        DesignProvider $designProvider
    )
    {
        return DesignProviderResource::make($designProvider)
            ->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * @param UpdateDesignProviderRequest $request
     * @param int                         $id
     * @return DesignProviderResource|JsonResponse
     */
    public function update(
        UpdateDesignProviderRequest $request,
        int                         $id
    )
    {
        if (
            $this->designProvider->update(
                $id,
                $request->validated()
            )
        ) {
            return response()->json([
                'message' => __('designProviders.designProvider_updated'),
                'status' => Response::HTTP_OK

            ], Response::HTTP_OK);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('designProviders.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }


}
