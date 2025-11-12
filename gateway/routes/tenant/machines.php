<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'grant:print-assortments,machines', 'namespace' => 'Machines'], function () {
    Route::resource('/machines', 'MachineController', [
        'names' => [
            'index' => 'print-assortments-machines-list',
            'store' => 'print-assortments-machines-create',
            'update' => 'print-assortments-machines-update',
            'destroy' => 'print-assortments-machines-delete'
        ]
    ])->except(['show','create', 'edit']);
});
