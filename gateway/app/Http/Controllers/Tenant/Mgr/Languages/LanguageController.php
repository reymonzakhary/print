<?php

namespace App\Http\Controllers\Tenant\Mgr\Languages;

use App\Http\Controllers\Controller;
use App\Http\Resources\Languages\LanguageResource;
use App\Models\Tenants\Language;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class LanguageController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function __invoke()
    {
        return LanguageResource::collection(Language::all())
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK,
            ]);
    }
}
