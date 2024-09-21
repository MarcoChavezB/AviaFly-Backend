<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeSistem extends Mailable
{
    use Queueable, SerializesModels;

    public $userIdentification;

    public function __construct($userIdentification){
        $this->userIdentification = $userIdentification;
    }

    public function envelope(){
        return new Envelope(
            subject: 'Bienvenido al sistema',
        );
    }

    public function build(){
        return $this->view('emails.NotifyUser.welcomeuser')
            ->subject('Bienvenido al sistema');
    }

}
