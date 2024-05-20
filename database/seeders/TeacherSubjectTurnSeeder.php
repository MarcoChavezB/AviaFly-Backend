<?php

namespace Database\Seeders;

use App\Models\TeacherSubjectTurn;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeacherSubjectTurnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TeacherSubjectTurn::create([
            'id_teacher' => 8,
            'id_subject' => 1,
            'id_turn' => 1,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 7,
            'id_subject' => 2,
            'id_turn' => 1,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 6,
            'id_subject' => 3,
            'id_turn' => 1,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 5,
            'id_subject' => 4,
            'id_turn' => 1,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 4,
            'id_subject' => 5,
            'id_turn' => 1,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 3,
            'id_subject' => 6,
            'id_turn' => 1,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 2,
            'id_subject' => 7,
            'id_turn' => 1,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 1,
            'id_subject' => 8,
            'id_turn' => 1,
        ]);
    }
}
