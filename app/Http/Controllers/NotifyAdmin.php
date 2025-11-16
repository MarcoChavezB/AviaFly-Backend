<?php

namespace App\Http\Controllers;

use App\Mail\NotifySale;
use App\Models\Employee;
use Illuminate\Support\Facades\Mail;

class NotifyAdmin extends Controller
{
    public function notifySale($customerName, $employeeName, $totalAmount){

        $admins = Employee::where('notify_sale', true)->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new NotifySale($customerName, $employeeName, $totalAmount));
        }
    }
}
