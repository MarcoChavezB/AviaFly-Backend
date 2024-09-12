<?php

namespace App\Mail;

use App\Models\Employee;
use App\Models\flightHistory;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use stdClass;

class FlightStatusNotify extends Mailable
{
    use Queueable, SerializesModels;
    public Student $student;
    public flightHistory $flight;
    public Employee $instructor;
    public string $status;
    public stdClass $details;

    public function __construct(Student $student, flightHistory $flight, Employee $instructor, string $status, stdClass $details){
        $this->student = $student;
        $this->flight = $flight;
        $this->instructor = $instructor;
        $this->status = $status;
        $this->details = $details;
    }


    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Notificación de cambio de estado de vuelo',
        );
    }


    public function build(){
        return $this->view('emails.NotifyUser.notifyStatusFlight')
            ->subject('Notificación de cambio de estado de vuelo')
            ->with([
                'student' => $this->student,
                'flight' => $this->flight,
                'instructor' => $this->instructor,
                'status' => $this->status,
                'details' => $this->details
            ]);
    }
}
