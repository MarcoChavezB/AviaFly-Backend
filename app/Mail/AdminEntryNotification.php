<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminEntryNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $employeeName;
    public $entryDateTime;
    public $user_type;

    /**
     * Create a new message instance.
     *
     * @param  string  $employeeName
     * @param  string  $entryDateTime
     * @param  string  $department
     * @return void
     */
    public function __construct($employeeName, $entryDateTime, $user_type)
    {
        $this->employeeName = $employeeName;
        $this->entryDateTime = $entryDateTime;
        $this->user_type = $user_type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.admin-finger-print-mail')
                    ->subject('Registro de Entrada Exitoso');
    }
}
