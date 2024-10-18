<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailCheckNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $employeeName;
    public $entryDateTime;
    public $checkType;
    /**
     * Create a new message instance.
     *
     * @param  string  $employeeName
     * @param  string  $entryDateTime
     * @param  string  $department
     * @return void
     */
    public function __construct($employeeName, $entryDateTime, $checkType)
    {
        $this->employeeName = $employeeName;
        $this->entryDateTime = $entryDateTime;
        $this->checkType = $checkType;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.NotifyUser.notifyCheck')
                    ->subject('Notificacion de registro');
    }
}
