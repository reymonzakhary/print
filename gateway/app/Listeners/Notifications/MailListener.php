<?php

namespace App\Listeners\Notifications;

use App\Events\Tenant\Order\MailQuotationEvent;
use App\Foundation\Settings\Settings;
use App\Http\Resources\Orders\OrderResource;
use App\Models\Tenants\Media\FileManager;
use App\Repositories\OrderRepository;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class MailListener implements ShouldQueue
{
    use Dispatchable;

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function sendMail($email, $name, $subject, $body, $files = null)
    {
        // Send mail
        Mail::send(
            'emails.defualt',
            [
                'body' => $body
            ], function ($message) use ($email, $name, $subject, $files) {
            $message->to($email, $name)
                ->subject($subject);
            if ($files) {
                foreach ($files as $key => $filePath) {
                    $message->attachData($filePath['data'], $filePath["name"]);
                }
            }
        });
    }

    /**
     * @param $event
     */
    public function onSendQuotationNotificationEmail(
        $event
    )
    {
        $setting = Settings::quotationLogo(0);
        $vat = Settings::vat();
        $logo = !empty($setting) ? FileManager::find($setting) : null;

        $logo_name = '';
        $logo_local_path = '';
        $file = '';

        if ($logo) {
            $logo_name = $logo->name ?? '';
            $logo_local_path = $logo->getImagePath($logo->path, $logo->name);
            $file = optional(Storage::disk('tenant'))->exists($logo_local_path) ?
                optional(Storage::disk('tenant'))->get($logo_local_path) :
                "";
        }


        // store mail queue
        $mailQueue = $event->mail;
        $order = $mailQueue->model;
        $OrderRepository = new OrderRepository($order::where('id', $mailQueue->model_id)->first());
        $order = $OrderRepository->show($mailQueue->model_id, false);


        $order = OrderResource::make($order)->additional([
            "setting" => $setting,
            "vat" => $vat,
            "token" => md5(sha1($order->expire_at . env('APP_KEY'))),
            "domain" => $event->domain,
            "logo_path" => $logo_name,
            "local_path" => $file
        ]);
        // get template data
        $mesasge = json_decode($mailQueue->message);
        $email = $order->orderedBy->email;
        $name = $order->orderedBy->profile->first_name . ' ' . $order->orderedBy->profile->last_name;
//        $subject = shortCode($mesasge->subject, $order);
//        $body = shortCode($mesasge->body, $order);
        $subject = "Quotation #" . $order->id;
        $body = View::make('pdf.order.email_qutation', ['order' => $order])->render();
        $files[] = $this->qutationPDF($order);
        foreach ($order->items as $key => $item) {
            if ($item->getMedia('items')->count()) {
                foreach ($item->getMedia('items')->toArray() as $media) {
                    $path = Storage::disk('tenant')->get("{$media['path']}{$media['name']}");
                    $fileData = [
                        'name' => ($key + 1) . "-{$media['name']}",
                        'type' => "path",
                        'data' => $path
                    ];
                    $files[] = $fileData;
                }
            }
        }
        // toDo get render mail template string

        if ($this->sendMail($email, $name, $subject, $body, $files)) {
            $mailQueue->update(['st' => 308]);
        } else {
            $mailQueue->update(['st' => 308]);
        }
        return true;
    }

    // Generate Qutation PDF file to send on the fly with mail
    public function qutationPDF($data)
    {
        $pdf = PDF::setOptions([
            'logOutputFile' => storage_path('logs/pdf.log'),
            'tempDir' => storage_path('logs/'),
            'isRemoteEnabled', true
        ])->loadView('pdf.quotation.pdf', ['order' => $data]);
        return [
            'name' => "qutation-{$data->id}.pdf",
            'type' => "data",
            'data' => $pdf->output()
        ];
    }

    /**
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            MailQuotationEvent::class,
            'App\Listeners\Notifications\MailListener@onSendQuotationNotificationEmail'
        );

    }
}
