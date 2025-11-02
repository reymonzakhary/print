<?php

namespace App\Http\Controllers\Tenant\Mgr\Tags;

use App\Events\Tags\CreateTagEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tags\StoreTagRequest;
use App\Http\Requests\Tags\UpdateTagRequest;
use App\Http\Resources\Tags\TagResource;
use App\Models\Tenants\Tag;
use App\Scoping\Scopes\Tags\SearchNameScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Tags
 */
class TagController extends Controller
{

    /**
     * List Tags
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @queryParam search string search with name space. Example: core
     * 
     * @response 200
     * {
     *  "data": [
     *      {
	 *		"id": 1,
	 *		"name": "test",
	 *		"slug": "test",
	 *		"hex": "test"
	 *	    },
     *    ],
     * "links":{},
     * "meta":{},
     * "status":200,
     * "message": null
     * }
     * 
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return TagResource::collection(Tag::withScopes($this->scope())->where('iso', app()->getLocale())->paginate())
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Store Tag 
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @bodyParam name string required name of tag. Example: newTag
     * @bodyParam hex string 
     * 
     * @response 201
     * {
     * 	"data": {
     * 		"id": 5,
     * 		"name": "test",
     * 		"slug": "test",
     * 		"hex": "test"
     * 	},
     * 	"message": "Items added successfully",
     * 	"status": 201
     * }
     * 
     * @response 422
     * {
     * 	"message": "The name field is required.",
     * 	"errors": {
     * 		"name": [
     * 			"The name field is required."
     * 		]
     * 	}
     * }
     * @param StoreTagRequest $request
     * @return TagResource
     */
    public function store(StoreTagRequest $request)
    {
        $tag = Tag::create($request->validated());
        event(new CreateTagEvent($tag, app()->getLocale()));
        return TagResource::make($tag)
            ->additional([
                'message' => __('Items added successfully'),
                'status' => Response::HTTP_CREATED
            ]);
    }

    /**
     * show Tag
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @urlParam tag_id integer required The ID of the tag.
     * 
     * @response 200
     * {
     * 	"data": {
     * 		"id": 1,
     * 		"name": "test",
     * 		"slug": "test",
     * 		"hex": "test"
     * 	},
     * 	"status": 200,
     * 	"message": null
     * }
     * @param Tag $tag
     * @return TagResource
     */
    public function show(Tag $tag)
    {
        return TagResource::make($tag)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Update Tag 
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @urlParam tag_id integer required The ID of the tag.
     * 
     * @bodyParam name string required name of tag. Example: newTag
     * 
     * @response 200
     * {
     * 	"data": {
     * 		"id": 5,
     * 		"name": "test",
     * 		"slug": "test",
     * 		"hex": "test"
     * 	},
     * 	"message": "tag.updated",
     * 	"status": 200
     * }
     * 
     * @response 422
     * {
     * 	"message": "The name field is required.",
     * 	"errors": {
     * 		"name": [
     * 			"The name field is required."
     * 		]
     * 	}
     * }
     * @param Tag              $tag
     * @param UpdateTagRequest $request
     * @return TagResource
     */
    public function update(Tag $tag, UpdateTagRequest $request)
    {
        $tag->update($request->validated());
        return TagResource::make($tag)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => __('tag.updated'),
            ]);
    }

    /**
     * Delete Tag
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @urlParam tag_id integer required The ID of the tag.
     * 
     * @response 202
     * {
     * 	"data": {
     * 		"message": "Tag was been deleted",
     * 		"status": 202
     * 	}
     * }
     * 
     * @param Tag $tag
     * @return JsonResponse
     */
    public function destroy(Tag $tag)
    {
        Tag::where('row_id', $tag->id)->get()->map(function ($t) {
            $t->delete();
        });
        return response()->json([
            'data' => [
                'message' => __('Tag was been deleted'),
                'status' => Response::HTTP_ACCEPTED,
            ]
        ], Response::HTTP_ACCEPTED);
    }

    /**
     * @return array
     */
    public function scope(): array
    {
        return [
            "search" => new SearchNameScope(),
        ];
    }
}
