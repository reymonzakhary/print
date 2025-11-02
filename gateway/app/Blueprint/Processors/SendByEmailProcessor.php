<?php

namespace App\Blueprint\Processors;

use App\Blueprint\Contract\BlueprintContract;
use App\Http\Resources\Items\ItemResource;
use App\Models\Hostname;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use Closure;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SendByEmailProcessor implements BlueprintContract
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
        $data = [
            'name' => "Mamdouh Khaled",
            'body' => $args['message']
        ];
        Mail::send('emails.layout', $data, function ($message) use ($args) {

            $message->to($args['to'], 'Mamdouh Khaled')
                ->subject($args['from']);

            $message->from($args['from'], 'Mamdouh Khaled');

            collect($this->attach)->map(function ($attach) use ($message) {
                $message->attachData($attach['file'], $attach['name']);
            });
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
                    'name' => 'test.pdf'
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
    public function jobTicket(string $type = 'xml', string $columns = "*"): void
    {
        if ($supplier = Hostname::findByFqdn($this->item->supplierName)->first()) {
            $this->attach[] = [
                'file' => view('job_tickets.job_ticket_xml', [
                    'order' => $this->order,
                    'item' => ItemResource::make($this->item),
                    'iso' => 'en',
                    'supplier' => (object)['name' => $supplier->fqdn, 'supplier_id' => $this->item->supplier_id],
                ])->render(),
                'name' => 'test.xml'];
        }
    }

    public function makeLowResPdf()
    {

    }

    public function ConvertPdfToImage()
    {

    }
}
