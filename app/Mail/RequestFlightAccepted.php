<?php

namespace App\Mail;

use App\Models\flightHistory;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequestFlightAccepted extends Mailable
{
    use Queueable, SerializesModels;
    public Student $student;
    public flightHistory $flight;
    public $comment;

    public function __construct(Student $student, flightHistory $flight, $comment){
        $this->student = $student;
        $this->flight = $flight;
        $this->comment = $comment;
    }

    public function envelope(){
        return new Envelope(
            subject: 'Peticion de vuelo aceptada',
        );
    }

    public function build(){
        return $this->view('emails.request-flight-accepted')
            ->subject('Peticion de vuelo aceptada');
    }

}
