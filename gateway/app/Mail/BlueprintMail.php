<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Blade;

class BlueprintMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public array $mail){}

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('test')
            ->html(Blade::render($this->mail['template'], $this->mail['data']));
    }
}
