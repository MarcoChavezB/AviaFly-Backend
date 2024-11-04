<?php

namespace App\Mail;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportIssue extends Mailable
{
    use Queueable, SerializesModels;
    public Student $student; // Declaración del tipo
    public $urlError;
    public $description;

    /**
     * Create a new message instance.
     *
     * @param Student $student
     * @param string $urlError
     * @param string $description
     */
    public function __construct(Student $student, $urlError, $description)
    {
        $this->student = $student; // Asignación correcta
        $this->urlError = $urlError;
        $this->description = $description;
    }

    public function envelope() {
        return new Envelope(
            subject: 'Nuevo reporte en sistema'
        );
    }

    public function build() {
        return $this->view('issues.report_issue')
            ->subject('Nuevo reporte en sistema');
    }
}
