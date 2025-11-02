<?php

Route::group(['middleware' => 'grant:design-providers', 'namespace' => 'DesignProviders'], function () {
    Route::resource('design/providers', 'DesignProviderController', [
        'names' => [
            'index' => 'design-providers-list',
            'show' => 'design-providers-read',
            'update' => 'design-providers-update',
        ]
    ])->except(['create', 'edit', 'store', 'destroy']);
});

Route::group(['middleware' => 'grant:design-providers,templates', 'namespace' => 'DesignProviderTemplates'], function () {
    Route::resource('design/provider/templates', 'DesignProviderTemplateController', [
        'names' => [
            'index' => 'design-providers-templates-list',
            'show' => 'design-providers-templates-read',
            'store' => 'design-providers-templates-create',
            'update' => 'design-providers-templates-update',
            'destroy' => 'design-providers-templates-delete'
        ]
    ])->except(['create', 'edit']);

});
