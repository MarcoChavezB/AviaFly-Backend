<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subjects = [
            'Servicio a Bordo',
            'Meteorología',
            'Mercancias Peligrosas',
            'Procedimientos de Emerg.',
            'Aerodinamica',
            'Reglametación Aérea',
            'Factores Humanos y CRM',
            'Geografía turística',
        ];

        foreach ($subjects as $subject) {
            Subject::create([
                'name' => $subject,
            ]);
        }
    }
}
