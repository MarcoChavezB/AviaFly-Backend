<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Employee::create([
            'name' => 'root',
            'last_names' => 'root',
            'email' => 'marco1102004@gmail.com',
            'company_email' => 'marco1102004@gmail.com',
            'phone' => '1234567890',
            'cellphone' => '1234567890',
            'curp' => 'AAMM110200HDFLRR00',
            'user_identification' => '1234567890',
            'user_type' => 'root',
            'id_base' => 1,
        ]);

        User::create([
            'user_identification' => '1234567890',
            'user_type' => 'root',
            'password' => bcrypt('1234567890'),
            'id_base' => 1,
        ]);
    }
}
