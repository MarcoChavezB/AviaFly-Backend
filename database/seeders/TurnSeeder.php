<?php

namespace Database\Seeders;

use App\Models\Career;
use App\Models\Turn;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TurnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Career::create([
            'name' => 'Piloto',
            'monthly_payments' => '1000',
            'registration_fee' => '1000',
            'monthly_fee' => '1000',
        ]);
        Career::create([
            'name' => 'Intensivo',
            'monthly_payments' => '1000',
            'registration_fee' => '1000',
            'monthly_fee' => '1000',
        ]);
    }
}

/**
    - piloto 
    - intensivo 
    - sobrecargo 
*/
