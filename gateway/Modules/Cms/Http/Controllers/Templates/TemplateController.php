<?php

namespace Modules\Cms\Http\Controllers\Templates;

use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Cms\Entities\Resource;
use Modules\Cms\Entities\Template;
use Modules\Cms\Http\Requests\Templates\CreateTemplateRequest;
use Modules\Cms\Http\Requests\Templates\UpdateTemplateRequest;
use Modules\Cms\Http\Traits\Tags;
use Modules\Cms\Scoping\Scopes\FolderScope;
use Modules\Cms\Transformers\Resource\TemplateResource;
use Modules\Cms\Transformers\Templates\TemplateIndexResource;

class TemplateController extends Controller
{
    use Tags;

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(
        Request $request
    )
    {
        return TemplateIndexResource::collection(
            Template::with('variables')->withScopes($this->scope($request))->get()
        )->additional([
            'status' => Response::HTTP_OK,
            'message' => null
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param string                $locale
     * @param CreateTemplateRequest $request
     * @return TemplateResource
     */
    public function store(
        CreateTemplateRequest $request
    )
    {
        $template = Template::create($request->validated());
        return TemplateResource::make(
            $template
        )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Show the specified resource.
     * @param string   $locale
     * @param Template $template
     * @return Application|Factory|View|\Laravel\Lumen\Application|TemplateResource
     */
    public function show(
        Template $template
    )
    {
        return TemplateResource::make($template->load('variables'))
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateTemplateRequest $request
     * @param Template              $template
     * @return JsonResponse|TemplateResource
     */
    public function update(
        UpdateTemplateRequest $request,
        Template              $template
    )
    {
        $dataToStore = $request->validated();
        if ($request->file('content')) {
            $dataToStore['content'] = htmlentities($request->file('content')->getContent());
        }

        if ($template->update($dataToStore)) {
            if ($request->file('content')) {
                // randerChunk
                // get chunks
                // set chunks
                // flat html
                $tags = $this->getTags($dataToStore['content']);
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
            return TemplateResource::make($template->load('variables'))
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
                'message' => __('Template couldn\'t be updated')
            ]
        ], Response::HTTP_BAD_REQUEST);

    }

    /**
     * Remove the specified resource from storage.
     * @param string   $locale
     * @param Template $template
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(
        Template $template
    )
    {

        Resource::onlyTrashed()->where('template_id', $template->id)->update(['template_id' => null]);

        if ($template->resources()->count() > 0) {
            /**
             * success response
             */
            return response()->json([
                'data' => [
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => __('Template couldn\'t be deleted is already in use by another resources!')
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        // delete all variables
        $template->variables()->delete();
        if ($template->delete()) {
            /**
             * success response
             */
            return response()->json([
                'data' => [
                    'status' => Response::HTTP_OK,
                    'message' => __('Template deleted with success')
                ]
            ], Response::HTTP_OK);
        }
        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => __('Template couldn\'t be deleted')
            ]
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return array
     */
    public function scope(Request $request)
    {

        return [
            "folder_id" => new FolderScope($request),
        ];
    }
}
