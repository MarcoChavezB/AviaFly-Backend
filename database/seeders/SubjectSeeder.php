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
                DB::table('career_subjects')->insert([
            'id_career' => 2,
            'id_subject' => 1,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 2,
            'id_subject' => 2,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 2,
            'id_subject' => 3,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 2,
            'id_subject' => 4,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 2,
            'id_subject' => 5,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 2,
            'id_subject' => 6,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' =>2,
            'id_subject' => 7,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 2,
            'id_subject' => 8,
        ]);
    }
}
