<?php

use Alexusmai\LaravelFileManager\Services\ConfigService\ConfigRepository;
use App\Http\Controllers\Tenant\Mgr\FileManager\FileManagerController;
use Illuminate\Support\Facades\Route;

$config = resolve(ConfigRepository::class);

// App middleware list
$middleware = $config->getMiddleware();
//dd($middleware);

/**
 * If ACL ON add "fm-acl" middleware to array
 */
//dd($config->getAcl());
if ($config->getAcl()) {
    $middleware[] = 'fm-tenant-acl';
}

Route::group([
    'namespace' => 'FileManager',
    'prefix' => $config->getRoutePrefix()
], function () {
    Route::get('public', [FileManagerController::class, 'publicPreview'])
        ->name('fm.preview');
});

Route::group([
    'namespace' => 'FileManager',
    'middleware' => $middleware,
    'prefix' => $config->getRoutePrefix()
], function () {
    Route::get('initialize', [FileManagerController::class, 'initialize'])
        ->name('fm.initialize');

    Route::get('content', [FileManagerController::class, 'content'])
        ->name('fm.content');

    Route::get('tree', [FileManagerController::class, 'tree'])
        ->name('fm.tree');

    Route::get('select-disk', [FileManagerController::class, 'selectDisk'])
        ->name('fm.select-disk');

    Route::post('upload', [FileManagerController::class, 'upload'])
        ->name('fm.upload');

    Route::post('delete', [FileManagerController::class, 'delete'])
        ->name('fm.delete');

    Route::post('paste', [FileManagerController::class, 'paste'])
        ->name('fm.paste');

    Route::post('rename', [FileManagerController::class, 'rename'])
        ->name('fm.rename');

    Route::get('download', [FileManagerController::class, 'download'])
        ->name('fm.download');

    Route::get('thumbnails', [FileManagerController::class, 'thumbnails'])
        ->name('fm.thumbnails');

    Route::get('preview', [FileManagerController::class, 'preview'])
        ->name('fm.preview');

    Route::get('url', [FileManagerController::class, 'url'])
        ->name('fm.url');

    Route::post('create-directory', [FileManagerController::class, 'createDirectory'])
        ->name('fm.create-directory');

    Route::post('create-file', [FileManagerController::class, 'createFile'])
        ->name('fm.create-file');

    Route::post('update-file', [FileManagerController::class, 'updateFile'])
        ->name('fm.update-file');

    Route::get('stream-file', [FileManagerController::class, 'streamFile'])
        ->name('fm.stream-file');

    Route::post('zip', [FileManagerController::class, 'zip'])
        ->name('fm.zip');

    Route::post('unzip', [FileManagerController::class, 'unzip'])
        ->name('fm.unzip');

    // Route::get('properties', [FileManagerController::class, 'properties']);

    // Integration with editors
    Route::get('ckeditor', [FileManagerController::class, 'ckeditor'])
        ->name('fm.ckeditor');

    Route::get('tinymce', [FileManagerController::class, 'tinymce'])
        ->name('fm.tinymce');

    Route::get('tinymce5', [FileManagerController::class, 'tinymce5'])
        ->name('fm.tinymce5');

    Route::get('summernote', [FileManagerController::class, 'summernote'])
        ->name('fm.summernote');

    Route::get('fm-button', [FileManagerController::class, 'fmButton'])
        ->name('fm.fm-button');

    Route::post('tags', [FileManagerController::class, 'syncTag'])
        ->name('fm.fm-syncTag');

    Route::get('content/search', [FileManagerController::class, 'search'])
        ->name('fm.search');
});
