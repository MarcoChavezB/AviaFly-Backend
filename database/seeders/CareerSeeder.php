<?php

namespace Database\Seeders;

use App\Models\Career;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CareerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $careers = [
            'Piloto Aviador Privado de Ala Fija',
            'Piloto Aviador Comercial de Ala Fija',
            'Sobrecargo de Aviacion',
            'Oficial de Operaciones Aeronáuticas',
            'Inicial de Técnico en Mantenimiento Clase I Motores y Planeadores',
            'Piloto Aviador Privado Intensivo',
            'Piloto Aviador Comercial Intensivo',
            'Oficial de Operaciones'
        ];

        foreach ($careers as $career) {
            Career::create(['name' => $career]);
        }
    }
}
