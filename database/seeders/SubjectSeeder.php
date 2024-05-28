<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\TeacherSubjectTurn;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    public function run()
    {   
        $subjects = [
            'Servicio a Bordo' => '3',
            'Meteorología' => '4',
            'Mercancias Peligrosas' => '6',
            'Procedimientos de Emerg.' => '2',
            'Aerodinamica' => '3',
            'Reglametación Aérea' => '5',
            'Factores Humanos y CRM' => '4',
            'Geografía turística' => '3',
        ];
        
        foreach ($subjects as $subject => $weeks) {
            Subject::create([
                'name' => $subject,
                'weeks_duration' => $weeks,
            ]);
        }
        
        DB::table('career_subjects')->insert([
            'id_career' => 2,
            'id_subject' => 25,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 2,
            'id_subject' => 26,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 2,
            'id_subject' => 27,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 2,
            'id_subject' => 28,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 2,
            'id_subject' => 29,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 2,
            'id_subject' => 30,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 2,
            'id_subject' => 31,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 2,
            'id_subject' => 32,
        ]);
        
        TeacherSubjectTurn::create([
            'id_teacher' => 8,
            'id_subject' => 25,
            'id_turn' => 1,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 7,
            'id_subject' => 26,
            'id_turn' => 1,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 6,
            'id_subject' => 27,
            'id_turn' => 1,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 5,
            'id_subject' => 28,
            'id_turn' => 1,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 4,
            'id_subject' => 29,
            'id_turn' => 1,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 3,
            'id_subject' => 30,
            'id_turn' => 1,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 2,
            'id_subject' => 31,
            'id_turn' => 1,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 1,
            'id_subject' => 32,
            'id_turn' => 1,
        ]);
    }
}
