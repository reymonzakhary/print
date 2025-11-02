<?php


// check role management

Route::group(['middleware' => 'grant:media-sources', 'namespace' => 'MediaSources'], function () {
    Route::resource('/media-sources', 'MediaSourceController', [
        'names' => [
            'index' => 'media-sources-list',
            'show' => 'media-sources-read',
            'store' => 'media-sources-create',
            'update' => 'media-sources-update',
            'destroy' => 'media-sources-delete'
        ]
    ])->except(['create', 'edit']);


    Route::group(['middleware' => 'grant:media-sources,rules'], function () {
        Route::resource('/media-sources/{media_source}/rules', 'MediaSourceRuleController', [
            'names' => [
                'store' => 'media-sources-rules-create',
                'update' => 'media-sources-rules-update',
                'destroy' => 'media-sources-rules-delete'
            ]
        ])->except(['index', 'show', 'create', 'edit']);
    });
});



