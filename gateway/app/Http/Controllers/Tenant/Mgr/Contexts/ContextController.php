<?php

namespace App\Http\Controllers\Tenant\Mgr\Contexts;

use App\Http\Controllers\Controller;
use App\Http\Resources\Context\ContextResource;
use App\Http\Resources\Users\UserResource;
use App\Models\Tenants\Context;
use App\Repositories\ContextRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ContextController extends Controller
{
    /**
     * @var ContextRepository
     */
    protected ContextRepository $context;

    /**
     * default hiding field from response
     */
    protected array $hide;

    /**
     * default total result in one page
     */
    protected int $per_page = 10;

    /**
     * UserController constructor.
     * @param Request $request
     * @param Context $context
     */
    public function __construct(
        Request $request,
        Context $context
    )
    {
        $this->context = new ContextRepository($context);
        /**
         * default hidden field
         */
        $this->hide = [
            $request->get('config') ?? 'profile'
        ];

        /**
         * default number of pages
         */
        $this->per_page = $request->get('per_page') ?? $this->per_page;
    }

    /**
     * Obtain paginated contexts
     * @return mixed
     */
    public function index()
    {
        /** @var Context obtain  $contexts */
        $contexts = $this->context->all($this->per_page);

        /**
         * check if we have contexts
         */
        if ($contexts->items()) {
            return ContextResource::collection($contexts)->hide(
                $this->hide
            )->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
        }
        /**
         * error response
         */
        return response()->json([
            'message' => __('contexts.no_context_available'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }

    public function showUsers(
        Context $context
    )
    {

        return UserResource::collection($context->users()->get())->hide([]);

    }
}
