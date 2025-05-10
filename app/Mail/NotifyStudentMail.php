<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyStudentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $content;
    public $fileContent;
    public $fileName;
    public $fileMime;

    public function __construct($title, $content, $fileContent = null, $fileName = null, $fileMime = null)
    {
        $this->title = $title;
        $this->content = $content;
        $this->fileContent = $fileContent;
        $this->fileName = $fileName;
        $this->fileMime = $fileMime;
    }

    public function build()
    {
        $email = $this->subject($this->title)
                      ->view('emails.NotifyUser.send-email')
                      ->with([
                          'content' => $this->content,
                      ]);

        if ($this->fileContent && $this->fileName && $this->fileMime) {
            $email->attachData($this->fileContent, $this->fileName, [
                'mime' => $this->fileMime
            ]);
        }

        return $email;
    }
}
