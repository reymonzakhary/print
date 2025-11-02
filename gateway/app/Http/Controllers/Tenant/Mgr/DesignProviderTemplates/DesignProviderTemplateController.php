<?php

namespace App\Http\Controllers\Tenant\Mgr\DesignProviderTemplates;

use Alexusmai\LaravelFileManager\Events\FilesUploaded;
use Alexusmai\LaravelFileManager\Events\FilesUploading;
use App\Events\Tenant\DesignTemplate\CreateTemplateEvent;
use App\Events\Tenant\FM\FinishedExtractingDesignProviderTemplate;
use App\Facades\DesignProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\DesignProviderTemplates\StoreDesignProviderTemplateRequest;
use App\Http\Requests\DesignProviderTemplates\UpdateDesignProviderTemplateRequest;
use App\Http\Resources\DesignProviderTemplates\DesignProviderTemplateResource;
use App\Models\Tenants\DesignProviderTemplate;
use App\Repositories\DesignProviderTemplateRepository;
use App\Scoping\Scopes\DesingProviderTemplates\DesingProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class DesignProviderTemplateController extends Controller
{
    /**
     * @var DesignProviderTemplateRepository
     */
    protected DesignProviderTemplateRepository $template;

    /**
     * default total result in one page
     */
    protected int $per_page = 10;

    /**
     * UserController constructor.
     * @param Request                $request
     * @param DesignProviderTemplate $template
     */
    public function __construct(
        Request                $request,
        DesignProviderTemplate $template
    )
    {
        $this->template = new DesignProviderTemplateRepository($template);
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

        if ($template = $this->template->all()) {
            return DesignProviderTemplateResource::collection(
                DesignProviderTemplate::withScopes(
                    $this->scope()
                )->with('designProvider')->paginate($this->per_page)
            )->hide(
                $this->hide + ['assets']
            )->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
        }
        /**
         * error response
         */
        return response()->json([
            'message' => __('designProviderTemplates.no_designProviderTemplate_available'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }

    /**
     * obtain single designProvider
     * @param DesignProviderTemplate $template
     * @return DesignProviderTemplateResource
     */
    public function show(
        DesignProviderTemplate $template
    )
    {
        return DesignProviderTemplateResource::make($template)
            ->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * @param StoreDesignProviderTemplateRequest $request
     * @return DesignProviderTemplateResource
     */
    public function store(
        StoreDesignProviderTemplateRequest $request
    )
    {
        $designProviderTemplate = DesignProvider::store($request->provider);

        return DesignProviderTemplateResource::make(
            $designProviderTemplate
        )
            ->hide($this->hide + ['assets'])
            ->additional([
                'status' => Response::HTTP_CREATED,
                'message' => null
            ]);
    }

    /**
     * @param UpdateDesignProviderTemplateRequest $request
     * @param int                                 $id
     * @return JsonResponse
     */
    public function update(
        UpdateDesignProviderTemplateRequest $request,
        int                                 $id
    )
    {
        if (
            DesignProvider::update($request->provider, $request->templateModel)
        ) {
            return response()->json([
                'message' => __('designProviderTemplates.designProviderTemplate_updated'),
                'status' => Response::HTTP_OK

            ], Response::HTTP_OK);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('designProviderTemplates.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(
        int $id
    )
    {
        if ($designProviderTemplate = DesignProviderTemplate::find($id)) {
            Storage::disk('tenant')->delete("Providers/{$designProviderTemplate->designProvider->name}/templates/{$designProviderTemplate->name}");
            $designProviderTemplate->removeMedia('design-provider-templates');
            if ($this->template->delete($id)) {
                /**
                 * error response
                 */
                return response()->json([
                    'message' => __('designProviderTemplates.designProviderTemplate_removed'),
                    'status' => Response::HTTP_OK
                ], Response::HTTP_OK);
            }
        }
        /**
         * error response
         */
        return response()->json([
            'message' => __('designProviderTemplates.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

    /*
     *
     */
    public function scope()
    {
        return [
            "design_provider" => new DesingProvider()
        ];
    }
}
