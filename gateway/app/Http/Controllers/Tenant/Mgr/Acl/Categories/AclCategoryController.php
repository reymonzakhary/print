<?php

namespace App\Http\Controllers\Tenant\Mgr\Acl\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\Categories\AclCategoryResource;
use App\Models\Tenant\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant ACL
 * @subgroup ACL Categories
 */
class AclCategoryController extends Controller
{

    /**
     * List ACL categories
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     *	"data": [
     *		{
     *			"id": 1,
     *			"name": "lol",
     *			"description": "lol",
     *			"slug": "lol",
     *			"iso": "en",
     *			"children": []
     *		}
     *	],
     *	"message": null,
     *	"status": 200
     *}
     *
     * @return AnonymousResourceCollection
     */
    public function __invoke()
    {
        return AclCategoryResource::collection(
            Category::tree()
                ->parents()
                ->where('iso', app()->getLocale())
                ->with('children')
                ->select('name', 'row_id','description', 'slug', 'iso')
                ->get()

        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }
}
