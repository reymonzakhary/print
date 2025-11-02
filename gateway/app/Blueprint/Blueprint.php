<?php

namespace App\Blueprint;

use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Str;

class Blueprint
{
    public function __invoke(Order $order, Item $item)
    {
        // get Item
        // get this option from setting

        $options = [
            'SendByEmail' => [
                'from' => 'from@tdsad.com',
                'to' => 'to@tdsad.com',
                'subject' => 'subject',
                'message' => 'message',
                'attach' => [
                    'item_files' => [],
                    'job_ticket' => [
                        'columns' => 'sadasd;asdasd;asdasd;asd',
                        'type' => 'xml'
                    ]
                ]
            ],
            'SendByFTP' => [
                'connection_type' => 'ftp',
                'connection_host' => '192.168.1.26',
                'connection_username' => 'ftpuser',
                'connection_password' => '123456789',
                'connection_port' => '21',
                'connection_path' => '/files',
                'attach' => [
                    'item_files' => [],
                    'job_ticket' => [
                        'columns' => 'sadasd;asdasd;asdasd;asd',
                        'type' => 'xml'
                    ]
                ]
            ],
        ];


        app(Pipeline::class)
            ->send([
                'order' => $order,
                'item' => $item,
            ])
            ->through(collect(array_keys($options))
                ->map(
                    fn($o) => "\App\Blueprint\Processors\\" . Str::ucfirst(Str::camel($o)) . 'Processor:' . serialize($options[$o])
                )->toArray()
            )->thenReturn();

    }
}
