<?php

namespace Modules\Cms\Http\Controllers\Resources;

use Alexusmai\LaravelFileManager\Events\FilesUploaded;
use App\Models\Tenant\Language;
use App\Models\Tenant\Media\FileManager;
use App\Services\Categories\BoopsService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Modules\Cms\Entities\Resource;
use Modules\Cms\Events\Resources\CreateResourceEvent;
use Modules\Cms\Events\Resources\DeleteResourceEvent;
use Modules\Cms\Events\Resources\LockResourceEvent;
use Modules\Cms\Events\Resources\UpdateResourceEvent;
use Modules\Cms\Http\Requests\Resources\StoreResourceRequest;
use Modules\Cms\Http\Requests\Resources\UpdateResourceRequest;
use Modules\Cms\Http\Traits\Tags;
use Modules\Cms\Transformers\Resources\ResourceIndexResource;
use Modules\Cms\Transformers\Resources\ResourceResource;
use App\Http\Resources\Categories\CategoryResource;
use App\Http\Resources\Categories\PrintBoopsResource;
use Modules\Cms\Entities\Variable;
use Modules\Cms\Enums\BlockKeysEnum;
use Modules\Cms\Enums\BlockTypesEnum;
use Modules\Cms\Enums\ProductTypesEnum;
use Symfony\Component\HttpFoundation\Response;

class ResourceController extends Controller
{
    use Tags;

    public function __construct(private BoopsService $boopsService)
    {  }

    /**
     * Display a listing of the resource.
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $resources = Resource::where('language', app()->getLocale())->with(['createdby', 'children.createdby'])
            ->latest()
            ->isParent()
            ->get();

        $children = Resource::where('language', app()->getLocale())->with(['createdby'])
            ->whereIn('base_id', $resources->pluck('id')->toArray())
            ->get();

        return ResourceIndexResource::collection(
            $resources->merge($children)
        );
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreResourceRequest $request
     * @return ResourceResource
     * @throws GuzzleException
     */
    public function store(
        StoreResourceRequest $request
    ): ResourceResource
    {
        $language = Language::where('iso', app()->getLocale())->firstORFail();

        $data = $request->validated();

        if ($request->resource_type == ProductTypesEnum::PRODUCT->value) {
            $data['content'][] = [
                'key' => is_numeric($request->category)? BlockKeysEnum::CATEGORY->value: BlockKeysEnum::BOOPS->value,
                'type' => BlockTypesEnum::CATEGORY->value,
                'value' => $request->category,
            ];
        }

        $resource = Resource::create($data);
        $user = auth()->user();
        event(new CreateResourceEvent($resource, $user, $language));

        if ($resource->isCustomCategory()) {
            $category = CategoryResource::make(\App\Models\Tenant\Category::find($resource->category));
        } else if (!$resource->isCustomCategory() && $resource->category) {
            $obtainedCategory = $this->boopsService->obtainCategoryBoops($resource->category);

            if (optional($obtainedCategory)['status'] !== Response::HTTP_NOT_FOUND) {
                $category = PrintBoopsResource::make($obtainedCategory[0]);
            }
        }

        $resource->category = $category??null;
        return ResourceResource::make($resource);
    }

    /**
     * Show the specified resource.
     * @param int $resource
     * @return ResourceResource
     * @throws GuzzleException
     */
    public function show(
        int $resource
    ): ResourceResource
    {

        $resource = Resource::withTrashed()->with('resourceType')->where([['language', app()->getLocale()], ['resource_id', $resource]])->firstOrFail();

        $tags = $resource?->template?
            $this->getTags($resource->template->content)
            : [];
        optional($resource)->template?->variables()->delete();
        optional($resource)->template?->chunks()->detach();
        foreach ($tags as $key => $variable) {
            foreach ($variable as $vars) {
                if ($key === 'chunks') {
                    optional($resource)->template->{$key}()->attach(optional($vars)['details']['id']);
                } else {
                    optional($resource)->template->{$key}()->create(optional($vars)['details']);

                }
            }
        }

        $user = auth()->user();
        event(new LockResourceEvent($resource, $user));
        $content = collect();

        if (empty($resource->content)) {
            $content = $resource?->template?->variables->map(function ($var) {
                return [
                    "key" => $var->name,
//                    "type" => $var->input_type,
                    "value" => null
                ];
            });
        }else {
            collect($resource->content)->each(function ($item) use ($resource, $content) {
                if (optional($item) && $resource->template?->variables->where('name', optional($item)['key'])->first()) {
                    $content->push($item);
                }
            });

            $obj = collect($resource->content)->where('type', BlockTypesEnum::CATEGORY->value);
            $content = $content->merge($obj->toArray());

            $variable = Variable::firstWhere([
//                ['type', optional($obj->first())['key'] == 'boops'? 'print_category': 'custom_category'],
                ['key', optional($obj->first())['key']],
            ]);
            if ($variable) {
                $resource->template?->variables->push($variable);
            }
        }

        $resource->update(['content' => $content?->toArray()??[]]);
        return ResourceResource::make($resource);
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateResourceRequest $request
     * @param int                   $resource
     * @return ResourceResource
     */
    public function update(
        UpdateResourceRequest $request,
        int                   $resource
    ): ResourceResource
    {
        $language = Language::where('iso', app()->getLocale())->firstOrFail();
        $resource = $request->resource;
        $requested = $request->validated();
        if ($request->image) {
            $image = $this->getFileByPath(optional($request->image)['path']);
            if ($image) {
                $image['model_type'] = Resource::class;
                $image['collection'] = 'main';
                $image['model_id'] = $resource->resource_id;
                $image['user_id'] = $request->user()->id;
                FileManager::where([
                    ['model_type', 'Modules\Cms\Entities\Resource'],
                    ['collection', 'main'],
                    ['model_id', $resource->resource_id]
                ])
                    ->delete();
                FileManager::create($image);
            }
        }
        if (optional($request->validated())['content']) {
            $content = optional($request->validated())['content'];
            foreach ($content as $object) {
                if (optional($object)['type'] === 'file') {
                    $image = $this->getFileByPath(optional(optional($object)['value'])['path']);
                    if ($image) {
                        $image['model_type'] = 'Modules\Cms\Entities\Resource';
                        $image['collection'] = optional($object)['key'];
                        $image['model_id'] = $resource->base_id;
                        $image['user_id'] = $request->user()->id;
                        FileManager::where([
                            ['model_type', 'Modules\Cms\Entities\Resource'],
                            ['collection', $object['key']],
                            ['model_id', $resource->base_id]
                        ])
                            ->delete();
                        FileManager::create($image);
                    } else {
                        FileManager::where([
                            ['model_type', 'Modules\Cms\Entities\Resource'],
                            ['collection', $object['key']],
                            ['model_id', $resource->base_id]
                        ])
                            ->delete();
                    }
                }
            }
        }

        if ($request->resource_type == ProductTypesEnum::PRODUCT->value) {

            $requested['content'] = array_filter($requested['content'], fn ($item) => ($item['key'] != BlockKeysEnum::BOOPS->value && $item['key'] != BlockKeysEnum::CATEGORY->value));

            $requested['content'][] = [
                'key' => is_numeric($request->category)? BlockKeysEnum::CATEGORY->value: BlockKeysEnum::BOOPS->value,
                'type' => is_numeric($request->category)?
                    BlockTypesEnum::INTEGER->value:
                    BlockTypesEnum::STRING->value,
                'value' => $request->category,
            ];
        } else {
            $requested['content'] = collect($requested['content'])->filter(function ($item) {
                return $item['key'] != BlockKeysEnum::CATEGORY->value || $item['key'] != BlockKeysEnum::BOOPS->value;
            })->toArray();
        }

        event(new FilesUploaded($request));

        if ($resource->update($requested)) {
            $user = auth()->user();
            event(new UpdateResourceEvent($resource, $user, $language));
        }
        return ResourceResource::make($resource);

    }

    /**
     * @param $path
     * @return array
     */
    public function getFileByPath(
        $path
    ): array
    {
        $params = explode('/', $path);
        $name = array_pop($params);
        $path = implode('/', $params);
        return optional(FileManager::where([['path', $path], ['name', $name]])->first())->toArray() ?? [];
    }

    /**
     * Remove the specified resource from storage.
     * @param int $resource
     * @return JsonResponse
     */
    public function destroy(
        int $resource
    ): JsonResponse
    {

        if($resource = Resource::where('id', $resource)->first()) {
            $resources = Resource::where('resource_id' , $resource->resource_id)->with('children')->get()->toTree();
            if($this->deleteRecursive($resources)) {
                return response()->json([
                    'data' => [
                        'status' => Response::HTTP_OK,
                        'message' => __('Resource deleted with success')
                    ]
                ], Response::HTTP_OK);
            }
        }

        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => __('Resource not found, please try again later.')
            ]
        ], Response::HTTP_NOT_FOUND);
    }


    private function deleteRecursive($resources): bool
    {
        $resources->map(function ($recursive) {
           if($recursive->hasChildren()) {
               $this->deleteRecursive($recursive->children()->get());
           }
           $recursive->update(['deleted_by' => auth()->id()]);
           $recursive->delete();

        });

        return true;
    }
}
