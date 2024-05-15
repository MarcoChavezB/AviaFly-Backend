<?php

namespace Database\Seeders;

use App\Models\User;
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
        User::create([
            'name' => 'root',
            'last_names' => 'root root',
            'phone' => random_int(100000000, 999999999),
            'cellphone' => random_int(100000000, 999999999),
            'curp' => 'rootrootrootrootroot',
            'email' => 'mzprah@gmail.com',
            'company_email' => 'root@root.com',
            'user_type' => 'root',
            'password' => bcrypt('rootalons'),
            'id_base' => 1,
            'user_identification' => 'EART1',
        ]);
    }
}
