<?php

namespace Database\Seeders;

use App\Models\Base;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bases = [
            'Torreón',
            'Querétaro',
        ];

        foreach ($bases as $base) {
            Base::create(['name' => $base, 'location' => $base]);
        }
    }
}
