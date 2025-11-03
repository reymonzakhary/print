<?php

namespace App\Blueprint\Processors;

use App\Blueprint\Contract\BlueprintContract;
use App\Http\Resources\Items\ItemResource;
use App\Models\Domain;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use Closure;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SendByFTPProcessor implements BlueprintContract
{
    protected Item $item;
    protected Order $order;
    protected array $attach = [];

    public function handle($request, Closure $next, $args = null)
    {
        $this->item = $request['item'];
        $this->order = $request['order'];
        $args = unserialize($args);
        collect(array_keys($args['attach']))
            ->map(fn($arg) => call_user_func([$this, Str::camel($arg)], ...$args['attach'][$arg]));
        $ftpConfig = [
            "sftp" => ($args['connection_type'] === 'sftp') ? true : false,
            "host" => $args['connection_host'],
            "username" => $args['connection_username'],
            "password" => $args['connection_password'],
            "port" => $args['connection_port'] ?? 21,
            "path" => $args['connection_path'],
            'driver' => 'ftp',
            'root' => $args['connection_path'] ?? "/",
            'passive' => false,
            'ignorePassiveAddress' => true,
        ];
        config(['filesystems.disks.ftp' => $ftpConfig]);

        collect($this->attach)->map(function ($attach) {
            Storage::disk('ftp')->put($attach['name'], $attach['file']);
        });

        return $next($request);
    }

    /**
     * get file form Item Media
     * push to attach array ['file', 'name']
     */
    public function itemFiles(): void
    {
        collect($this->item->getMedia('items'))
            ->map(
                fn($i) => $this->attach[] = [
                    'file' => Storage::disk('tenant')->get("{$i['path']}{$i['name']}"),
                    'name' => "{$this->order->id}/{$this->item->id}/JobTicket-Order#{$this->order->id}-item#{$this->item->id}-{$i['name']}"
                ]
            );
    }

    /**
     * get file form Item Media
     * push to attach array ['file', 'name']
     * @param string $type    xml,html,pdf,image
     * @param string $columns accept columns name
     * @throws Throwable
     */
    public function jobTicket(string $type = 'xml', string $columns = "*")
    {
        if ($supplier = Domain::findByFqdn($this->item->supplierName)->first()) {
            $this->attach[] = [
                'file' => view('job_tickets.job_ticket_xml', [
                    'order' => $this->order,
                    'item' => ItemResource::make($this->item),
                    'iso' => 'en',
                    'supplier' => (object)['name' => $supplier->fqdn, 'supplier_id' => $this->item->supplier_id],
                ])->render(),
                'name' => "{$this->order->id}/{$this->item->id}/JobTicket-Order#{$this->order->id}-item#{$this->item->id}.xml"];
        }
    }
}
