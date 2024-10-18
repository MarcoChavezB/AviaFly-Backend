<?php

namespace App\Mail;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailCheckWarn extends Mailable
{
    use Queueable, SerializesModels;

    public $timeDifference;
    public Employee $employee;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($timeDifference, Employee $employee)
    {
        $this->timeDifference = $timeDifference;
        $this->employee = $employee;
    }

    public function build(){
        return $this->view('emails.NotifyUser.check_warning')
            ->subject('Alerta de chequeo')->with([
            'timeDifference' => $this->timeDifference,
            'employee' => $this->employee,
        ]);
    }

}
