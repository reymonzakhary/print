<?php

namespace App\Http\Controllers\Tenant\Mgr\Custom\Options;

use App\Http\Controllers\Controller;
use App\Http\Resources\Options\OptionResource;
use App\Models\Tenants\Option;
use Symfony\Component\HttpFoundation\Response;

class OptionTranslationController extends Controller
{
    /**
     * @param int $option
     * @return mixed
     */
    public function __invoke(int $option): mixed
    {
        $options = Option::query()
            ->where([['row_id', $option], ['iso', '!=', app()->getLocale()]])
            ->get(['id', 'row_id', 'iso', 'name', 'description']);
        return OptionResource::collection(
            $options
        )->hide([
            'slug',
            'box_id',
            'input_type',
            'incremental_by',
            'min',
            'max', 'width', 'height', 'length', 'unit',
            'display_price', 'price', 'price_switch', 'sort',
            'secure', 'parent_id', 'base_id', 'published',
            'published_at', 'published_by', 'created_by',
            'created_at', 'updated_at', 'children', 'media'
        ])->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }
}
