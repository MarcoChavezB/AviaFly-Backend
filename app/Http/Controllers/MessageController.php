<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;

class MessageController extends Controller
{
    protected $twilio;

    public function __construct()
    {
        $this->twilio = new Client(
            env('TWILIO_SID'),
            env('TWILIO_AUTH_TOKEN')
        );
    }

    public function sendMessage($to, $message)
    {
        $this->twilio->messages->create(
            $to,
            [
                'from' => env('TWILIO_PHONE_NUMBER'),
                'body' => $message,
            ]
        );
    }
}
