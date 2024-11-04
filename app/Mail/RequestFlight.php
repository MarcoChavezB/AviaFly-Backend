<?php

namespace App\Mail;

use App\Models\flightHistory;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequestFlight extends Mailable
{
    use Queueable, SerializesModels;
    public Student $student;
    public flightHistory $flight;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Student $student, flightHistory $flight)
    {
        $this->student = $student;
        $this->flight = $flight;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Peticion de vuelo',
        );
    }

    public function build(){
        return $this->view('emails.request-flight')
            ->subject('Request Flight');
    }

}
