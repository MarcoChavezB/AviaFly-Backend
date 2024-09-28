<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class RootUser extends Seeder
{

    public function run()
    {
        Employee::create([
            'name' => 'Administrador',
            'last_names' => 'root',
            'email' => 'marco1102004@gmail.com',
            'company_email' => 'marco1102004@gmail.com',
            'phone' => '6242647089',
            'cellphone' => '6242647089',
            'curp' => 'CABM040110HDGHLRA9',
            'user_identification' => 'CABM040110HDGHLRA9',
            'user_type' => 'root',
            'id_base' => 1
        ]);
    }
}
