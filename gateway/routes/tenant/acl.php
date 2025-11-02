<?php

use App\Http\Controllers\Tenant\Mgr\Users\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'grant:acl', 'namespace' => 'Acl'], function () {
    Route::get('/acl', 'AclController')->name('acl-list');
    Route::group(['namespace' => 'Categories'], function () {
        Route::get('/acl/categories', 'AclCategoryController')->name('acl-roles-update');
    });
//    Route::post('/acl', 'AclController@store')->name('acl-create');
    // check role management
    Route::group(['middleware' => 'grant:acl,roles', 'namespace' => 'Roles'], function () {
        Route::post('/acl/roles/{role}', 'RolePermissionController')
            ->name('acl-roles-update');
        Route::resource('/acl/roles', 'RoleController', [
            'names' => [
                'index' => 'acl-roles-list',
                'show' => 'acl-roles-read',
                'store' => 'acl-roles-create',
                'update' => 'acl-roles-update',
                'destroy' => 'acl-roles-delete'
            ]
        ])->except([
            'create', 'edit'
        ]);

        // get permissions
//        Route::group(['middleware' => 'grant:acl,permissions'], function () {
//            Route::get('/acl/roles/{role}/permissions', 'PermissionController')
//                ->name('acl-permissions-list');
//        });
    });

    // add roles to user
    Route::put('acl/users/{user}/roles', [UserController::class, 'attachRole'])
        ->name('acl-permissions-list');

    Route::group(['middleware' => 'grant:acl,teams-media-sources', 'namespace' => 'Teams'], function () {
        Route::get('/acl/teams/{team}/media-sources', 'TeamAclController@index')
            ->name('acl-teams-media-sources-list');
        Route::post('/acl/teams/{team}/media-sources', 'TeamAclController@store')
            ->name('acl-teams-media-sources-create');
        Route::delete('/acl/teams/{team}/media-sources/{media_source}', 'TeamAclController@destroy')
            ->name('acl-teams-media-sources-delete');
    });
});
