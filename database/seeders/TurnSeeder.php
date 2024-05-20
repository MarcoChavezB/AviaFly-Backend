<?php

namespace Database\Seeders;

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
        Turn::create([
            'name' => 'Matutino',
        ]);

        Turn::create([
            'name' => 'Vespertino',
        ]);
    }
}
