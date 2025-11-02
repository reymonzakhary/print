<?php

namespace App\Http\Controllers\Tenant\Mgr\Blueprints;

use App\Enums\BlueprintNamespaces;
use App\Http\Controllers\Controller;
use App\Http\Requests\Blueprints\AttachToProduct;
use App\Http\Requests\Blueprints\BlueprintStoreRequest;
use App\Http\Requests\Blueprints\BlueprintUpdateRequest;
use App\Http\Resources\Blueprints\BlueprintResource;
use App\Models\Tenants\Blueprint;
use App\Models\Tenants\Product;
use App\Scoping\Scopes\Blueprints\BlueprintByGroupScope;
use App\Scoping\Scopes\Blueprints\BlueprintByNsScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;

class BlueprintController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return BlueprintResource::collection(Blueprint::withScopes($this->scope())->get())
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * @param Blueprint $blueprint
     * @return BlueprintResource
     */
    public function show(
        Blueprint $blueprint
    ): BlueprintResource
    {
        return BlueprintResource::make($blueprint->load('products'))
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK
            ]);

    }

    /**
     * @param BlueprintStoreRequest $request
     * @return BlueprintResource
     */
    public function store(
        BlueprintStoreRequest $request
    ): BlueprintResource
    {
        return BlueprintResource::make(
            Blueprint::create($request->validated())
        )->additional([
            'message' => __('Blue print has been created successfully.'),
            'status' => Response::HTTP_CREATED
        ]);
    }

    /**
     * @param Blueprint              $blueprint
     * @param BlueprintUpdateRequest $request
     * @return JsonResponse
     */
    public function update(
        Blueprint              $blueprint,
        BlueprintUpdateRequest $request
    ): JsonResponse
    {
        if ($blueprint->update($request->validated())) {
            return response()->json([
                'message' => __('Blueprint has been updated successfully.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __('We couldn\'t handle your request, please try again later.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param Blueprint $blueprint
     * @return JsonResponse
     */
    public function destroy(
        Blueprint $blueprint
    ): JsonResponse
    {
        if ($blueprint->delete()) {
            return response()->json([
                'message' => __('Blueprint has been deleted successfully.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __('We couldn\'t handle your request, please try again later.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);

    }

    public function attachToProduct(
        Blueprint       $blueprint,
        Product         $product,
        AttachToProduct $request)
    {
        $pivots = $request->validated();

        $blueprintable = DB::table('blueprintables')
            ->select('blueprintable_id', 'blueprint_id')->where([
                ['blueprintable_id', $product->row_id],
                ['blueprint_id', $blueprint->id]
            ])->exists();

        $class_name = get_class($product);

        $reflection_class = new ReflectionClass($class_name);

        $className = $reflection_class->getName();


        if ($blueprintable) {
            return response()->json([
                'message' => __('Blueprint Already attached to the product'),
                'status' => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        // $product->blueprint()->sync([$blueprint->id => $pivots])
        if (DB::table('blueprintables')->insert(
            array_merge(
                [
                    'blueprintable_id' => $product->row_id,
                    'blueprint_id' => $blueprint->id,
                    'blueprintable_type' => $className
                ],
                $pivots
            ))
        ) {
            return response()->json([
                'message' => __('Product Attached to  BluePrint'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __('we can\'t Attach Product to BluePrint'),
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    public function deAttachProduct(
        Blueprint $blueprint,
        Product   $product
    )
    {

        $blue = DB::table('blueprintables')->where([
            ['blueprint_id', $blueprint->id],
            ['blueprintable_id', $product->row_id],
        ])->delete();

        return response()->json([
            'message' => __('Product De-Attached to BluePrint'),
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    public function namespaces()
    {
        return BlueprintNamespaces::grouped();
    }

    private function scope()
    {
        return [
            "group" => new BlueprintByGroupScope(),
            "ns" => new BlueprintByNsScope()
        ];
    }
}
