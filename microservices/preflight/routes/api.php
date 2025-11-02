<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Actions\GetPositionAction;
use App\Actions\GetPdfDataAction;
use App\Actions\GetExcelDataAction;
use App\Actions\FindAndReplsceActions;
use App\Actions\AddLayersActions;
use App\Actions\FindAndReplsceDegreeActions;
use App\Actions\CertificateActions;
use App\Actions\DegreeActions;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('pdf/stamp', [\App\Http\Controllers\pdfController::class, '__invoke']);

// Route::post('pdf', GetPositionAction::class);
Route::post('pdf3', function (Request $request)
{
    return GetPositionAction::run($request);
});

Route::post('pdf1', function (Request $request)
{
    return GetPdfDataAction::run($request);
});

Route::post('pdf2', function (Request $request)
{
    return GetExcelDataAction::run($request);
});

Route::post('pdf11', function (Request $request)
{
    return FindAndReplsceActions::run($request);
});
Route::post('pdf55', function (Request $request)
{
    return FindAndReplsceDegreeActions::run($request);
});
Route::post('pdf4', function (Request $request)
{
    return AddLayersActions::run($request);
});

Route::post('pdf', function (Request $request)
{
    return CertificateActions::run($request);
});
Route::post('degree', function (Request $request)
{
    return DegreeActions::run($request);
});
