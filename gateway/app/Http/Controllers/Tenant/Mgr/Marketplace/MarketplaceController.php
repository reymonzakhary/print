<?php

namespace App\Http\Controllers\Tenant\Mgr\Marketplace;

use App\Enums\Status;
use App\Foundation\ContractManager\Facades\ContractManager;
use App\Http\Controllers\Controller;
use App\Http\Resources\Categories\FinderPrintCategoryResource;
use App\Http\Resources\Options\PrintOptionResource;
use App\Services\Tenant\Categories\OptionService;
use App\Services\Tenant\Finder\Categories\CategoryService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MarketplaceController extends Controller
{

    /**
     * @param CategoryService $categoryService
     * @param OptionService $optionService
     */
    public function __construct(
        public CategoryService $categoryService,
        public OptionService $optionService

    ){}

    /**
     * @param Request $request
     * @return mixed
     * @throws GuzzleException
     */
    public function categories(
        Request $request
    ): mixed
    {

        $request->merge([
            'contracted' => ContractManager::getReceiverConnections(filters: [
                'receiver_connection' => fn($contract) => $contract->receiver_connection !== "cec",
                'st' => Status::ACCEPTED->value
            ])
        ]);

        return FinderPrintCategoryResource::collection(
            $this->categoryService->obtainFinderSearchCategories($request->all())
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    public function options(
        Request $request
    )
    {
        return PrintOptionResource::collection($this->optionService->obtainFinderSearchOption($request->all()))
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK
            ]);

    }
}
