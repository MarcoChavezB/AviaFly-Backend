<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifySale extends Mailable
{
    use Queueable, SerializesModels;

    public $customerName;
    public $employeeName;
    public $totalAmount;

    /**
     * Create a new message instance.
     *
     * @param  string  $customerName
     * @param  string  $employeeName
     * @param  float   $totalAmount
     * @param  int     $saleId
     * @return void
     */
    public function __construct($customerName, $employeeName, $totalAmount)
    {
        $this->customerName = $customerName;
        $this->employeeName = $employeeName;
        $this->totalAmount = $totalAmount;
    }

    public function build()
    {
        return $this->view('emails.admin-notify-sale')
                    ->subject('Registro de nueva venta en el sistema');
    }
}
