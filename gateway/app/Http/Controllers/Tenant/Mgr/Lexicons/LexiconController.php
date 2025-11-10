<?php

namespace App\Http\Controllers\Tenant\Mgr\Lexicons;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lexicons\UpdateLexiconRequest;
use App\Http\Resources\Lexicons\LexiconResource;
use App\Models\Tenant\Lexicon;
use App\Scoping\Scopes\Lexicons\LexiconAreaScope;
use App\Scoping\Scopes\Lexicons\LexiconLanguageScope;
use App\Scoping\Scopes\Lexicons\LexiconNamespaceScope;
use App\Scoping\Scopes\Lexicons\LexiconTemplateScope;
use Illuminate\Http\JsonResponse;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

class LexiconController extends Controller
{
    /**
     * Retrieve the list of scopes available for the application.
     *
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function index(): JsonResponse
    {

        $paginated = Lexicon::query()
            ->withScopes($this->scopes())
            ->paginate(request()->get('per_page', 10));

        // Load all filtered entries (single extra query)
        $full = Lexicon::query()
            ->withScopes($this->scopes())
            ->select('namespace', 'area', 'language');

        // Metadata from full set
        $namespaces = $full->pluck('namespace')->unique()->values();
        $area = $full
            ->where('namespace', request()->get('namespace', 'mail'))
            ->pluck('area')
            ->unique()
            ->values();
        $languages = $full->pluck('language')->unique()->values();

        // Transform paginated page
        $transformed = LexiconResource::collection($paginated->getCollection());
        $grouped = $transformed->groupBy('namespace');

        // Final response
        return response()->json([
            'data' => $grouped,
            'message' => null,
            'status' => Response::HTTP_OK,
            'namespaces' => $namespaces,
            'area' => $area,
            'languages' => $languages,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ]
        ]);

    }

    /**
     * Summary of update
     * @param UpdateLexiconRequest $request
     * @param Lexicon $lexicon
     * @return JsonResponse
     */
    public function update(
        UpdateLexiconRequest $request,
        Lexicon $lexicon
    ): JsonResponse
    {
        $lexicon->update( $request->validated() );

        return response()->json([
            "message" => __("lexicon updated successfully."),
            "status" => Response::HTTP_OK
        ]);
    }

    /**
     * Retrieve the list of scopes available for the application.
     *
     * @return array
     */
    private function scopes(): array
    {
        return [
            'language' => new LexiconLanguageScope(),
            'template' => new LexiconTemplateScope(),
            'namespace' => new LexiconNamespaceScope(),
            'area' => new LexiconAreaScope(),
        ];
    }
}
