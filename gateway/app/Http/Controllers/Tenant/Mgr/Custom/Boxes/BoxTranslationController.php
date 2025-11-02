<?php

namespace App\Http\Controllers\Tenant\Mgr\Custom\Boxes;

use App\Http\Controllers\Controller;
use App\Http\Resources\Boxes\BoxResource;
use App\Models\Tenants\Box;
use Symfony\Component\HttpFoundation\Response;

class BoxTranslationController extends Controller
{
    /**
     * @param int $box
     * @return mixed
     */
    public function __invoke(int $box): mixed
    {
        $categories = Box::query()
            ->where([['row_id', $box], ['iso', '!=', app()->getLocale()]])
            ->get(['id', 'row_id', 'iso', 'name', 'description']);
        return BoxResource::collection(
            $categories
        )->hide([
            'slug', 'sort', 'sqm', 'input_type', 'incremental',
            'media', 'select_limit', 'option_limit', 'base_id',
            'is_parent', 'options', 'created_by',
            'created_at', 'updated_at', 'children'
        ])->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }
}
