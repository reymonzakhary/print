<?php

namespace App\Http\Controllers\Tenant\Mgr\Dashboard;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Order;
use App\Models\Tenants\Quotation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'orders' => collect(Order::where('type', true)->select('st', DB::raw('count(*) as total'))
                    ->groupBy('st')
                    ->pluck('total', 'st'))->mapWithKeys(function ($count, $status) {
                    $label = Status::getStatusByCode((int)$status);
                    return [$label->name => $count];
                }),
                'quotations' => collect(Quotation::where('type', false)->select('st', DB::raw('count(*) as total'))
                    ->groupBy('st')
                    ->pluck('total', 'st'))->mapWithKeys(function ($count, $status) {
                    $label = Status::getStatusByCode((int)$status);
                    return [$label->name => $count];
                })
            ],
            'message' => '',
            'status' => Response::HTTP_OK
        ]);
    }
}
