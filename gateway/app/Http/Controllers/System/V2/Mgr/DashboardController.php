<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\V2\Mgr;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class DashboardController extends Controller
{
    /**
     * Returns a general info and some statistics about the system.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [],
            'message' => __('System info has been retrieved successfully'),
            'status' => Response::HTTP_OK
        ]);
    }
}
