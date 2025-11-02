<?php
Route::group(['middleware' => 'grant:print-assortments,delivery', 'namespace' => 'DeliveryDays'], function () {
    Route::resource('/delivery-day', 'DeliveryDayController', [
        'names' => [
            'index' => 'print-assortments-machines-list',
            'show' => 'print-assortments-machines-read',
            'store' => 'print-assortments-machines-create',
            'update' => 'print-assortments-machines-update',
            'destroy' => 'print-assortments-machines-delete'
        ]
    ]);
});
