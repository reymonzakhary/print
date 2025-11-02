<?php

namespace Modules\Cms\Http\Controllers\Chunks;

use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Cms\Entities\Chunk;
use Modules\Cms\Http\Requests\Chunks\StoreChunkRequest;
use Modules\Cms\Http\Requests\Chunks\UpdateChunkRequest;
use Modules\Cms\Http\Traits\Tags;
use Modules\Cms\Transformers\Chunks\ChunkIndexResource;
use Modules\Cms\Transformers\Resource\ChunkResource;

class ChunkController extends Controller
{
    use Tags;

    /**
     * Display a listing of the resource.
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return ChunkIndexResource::collection(
            Chunk::get()
        )->additional([
            'status' => Response::HTTP_OK,
            'message' => null
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @param string            $local
     * @param StoreChunkRequest $request
     * @return ChunkResource
     */
    public function store(
        StoreChunkRequest $request
    )
    {
        return ChunkResource::make(
            Chunk::create($request->validated())
        )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Show the specified resource.
     * @param string $local
     * @param Chunk  $chunk
     * @return ChunkResource
     */
    public function show(
        Chunk $chunk
    )
    {
        return ChunkResource::make(
            $chunk
        )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int     $id
     * @return JsonResponse|ChunkResource
     */
    public function update(
        UpdateChunkRequest $request,
        Chunk              $chunk
    )
    {

        $dataToStore = $request->validated();
        if ($request->file('content')) {
            $dataToStore['content'] = htmlentities($request->file('content')->getContent());
            foreach ($chunk->templates as $template) {

                $tags = $this->getTags(html_entity_decode($template->content));
                $template->variables()->delete();
                $template->chunks()->detach();
                foreach ($tags as $key => $variable) {
                    foreach ($variable as $vars) {
                        if ($key === 'chunks') {
                            $template->{$key}()->attach(optional($vars)['details']['id']);
                        } else {
                            $template->{$key}()->create(optional($vars)['details']);

                        }
                    }
                }
            }
        }

        if ($chunk->update($dataToStore)) {
            return ChunkResource::make($chunk)
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => null
                ]);
        }

        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => __('Chunk couldn\'t be updated')
            ]
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified resource from storage.
     * @param string $local
     * @param Chunk  $chunk
     * @return Renderable|JsonResponse
     * @throws Exception
     */
    public function destroy(
        Chunk $chunk
    )
    {
        if ($chunk->delete()) {
            /**
             * success response
             */
            return response()->json([
                'data' => [
                    'status' => Response::HTTP_OK,
                    'message' => __('Chunk deleted with success')
                ]
            ], Response::HTTP_OK);
        }
        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => __('Chunk couldn\'t be deleted')
            ]
        ], Response::HTTP_BAD_REQUEST);
    }
}
