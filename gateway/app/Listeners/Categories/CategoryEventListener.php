<?php

namespace App\Listeners\Categories;

use App\Events\Tenant\Categories\CategoryExportExcel;
use App\Events\Tenant\Categories\CategoryImportExcel;
use App\Events\Tenant\Categories\DeleteCategoryEvent;
use App\Events\Tenant\Categories\FinishedCategoryProductsExport;
use App\Events\Tenant\Categories\FinishedCategoryProductsImport;
use App\Events\Tenant\Categories\OnDeletedCategoryEvent;
use App\Foundation\Media\FileManager;
use App\Foundation\Media\MediaType;
use App\Models\Tenant\Media\FileManager as FileManagerModel;
use App\Services\Suppliers\SupplierCategoryService;
use App\Services\Suppliers\SupplierProductService;
use App\Services\Tenant\Categories\SupplierCategoryService as CategoryService;
use App\Services\Tenant\FM\FileManagerService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CategoryEventListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        public SupplierProductService  $supplierProductService,
        public FileManagerService      $fileManagerService,
        public FileManager             $fm,
        public SupplierCategoryService $supplierCategoryService
    )
    {

    }

    /**
     * @param $event
     * @throws GuzzleException
     */
    public function onExportCategory($event)
    {
        $res = $this->supplierProductService->exportCategory($event->tenant, $event->category, $event->type);
        $collection = "categoryExport";
        $path = str_replace(['tenancy/', $event->tenant.'/'], ["",""], $res['path']);
        $memeType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
        $ext = "xlxs";

        $fileManager = FileManagerModel::updateOrCreate([
            'user_id' => $event->user->id,
            'path' => $path,
            'name' => $res['name'],
            'collection' => $collection,
        ], [
            'user_id' => $event->user->id,
            'name' => $res['name'],
            'disk' => "tenancy",
            'group' => MediaType::getGroupType($memeType),
            'path' => $path,
            'ext' => $ext,
            'type' => $memeType,
            'size' => $res['size'],
            'collection' => $collection,

        ]);
        event(new FinishedCategoryProductsExport($event->category, $path));
    }

    public function onImportCategory($event)
    {
        $excelData = $this->fileManagerService->readExcel(['path' => $event->tenant . "/" . $event->request['path']]);
        $this->supplierProductService->importCategory($event->tenant, $event->category, (array)array_merge($excelData, $event->request));
        event(new FinishedCategoryProductsImport($event->category));
    }

    public function onDeleteCategory($event)
    {
        $response = app(CategoryService::class)->deleteCategory($event->category, $event->tenant);

        if(optional($response)['status'] === Response::HTTP_OK) {
            event(new OnDeletedCategoryEvent($event->category));
        }
    }

    /**
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            CategoryExportExcel::class,
            'App\Listeners\Categories\CategoryEventListener@onExportCategory'
        );
        $events->listen(
            CategoryImportExcel::class,
            'App\Listeners\Categories\CategoryEventListener@onImportCategory'
        );
        $events->listen(
            DeleteCategoryEvent::class,
            'App\Listeners\Categories\CategoryEventListener@onDeleteCategory'
        );
    }

    public function failed($event, $exception)
    {
        Log::info("CategoryEventListener => .....", [$exception, $event]);
    }
}
