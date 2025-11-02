<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth.ctx:mgr', 'auth:tenant', 'fm-tenant-acl', 'auth.tenant.gate'], 'prefix' => 'cms'], function () {

    Route::resource('/folders', 'Folders\FolderController');
    Route::group(['namespace' => 'Templates'], function () {
        Route::resource('/templates', 'TemplateController');
        Route::resource('/variables', 'VariableController');
    });


    Route::resource('/snippets', 'Snippets\SnippetController');

    /**
     * trash group
     */
    Route::get('tree/trash', 'Tree\TreeTrashController@index');
    Route::delete('tree/trash/{id?}', 'Tree\TreeTrashController@destroy');

    /**
     * restore all from teh trash
     */
    Route::put('tree/trash/restore/{id?}', 'Tree\TreeRestoreController@restore');


    Route::put('tree', 'Tree\TreeController@update');
    Route::resource('/tree', 'Tree\TreeController', [
        'parameters' => [
            'tree' => 'resource'
        ]
    ])->except(
        'show', 'update'
    );

    Route::resource('resources/types', 'Resources\ResourceTypeController')
        ->except('show', 'create', 'edit');
    Route::resource('resources/groups', 'Resources\ResourceGroupController');
    Route::resource('/resources', 'Resources\ResourceController')->except(
        'index'
    );
    Route::resource('resources.media', 'Resources\Media\MediaController');


    Route::resource('/chunks', 'Chunks\ChunkController');

});
