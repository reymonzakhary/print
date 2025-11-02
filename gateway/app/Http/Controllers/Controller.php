<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="3.0.0:0",
 *      title="CEC API Documentation",
 *      description="Access manager system",
 *      @OA\Contact(
 *          email="reymon@prindustry.com"
 *      ),
 *      @OA\License(
 *          name="Nginx 1.20.1",
 *          url="https://nginx.org/en/download.html"
 *      )
 * )
 *
 *
 * @OA\SecurityScheme(
 *       scheme="Bearer",
 *       securityScheme="Bearer",
 *       type="apiKey",
 *       in="header",
 *       name="Authorization",
 * )
 */
class Controller extends BaseController
{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
