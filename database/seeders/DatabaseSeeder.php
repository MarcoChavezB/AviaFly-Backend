<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Faker\Factory as Faker;
use App\Models\Base;
use App\Models\Career;
use App\Models\Employee;
use App\Models\InfoFlight;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherSubjectTurn;
use App\Models\Turn;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
    // BASES SEEDER
        $bases = [
            'Torreón',
            'Querétaro',
        ];

        foreach ($bases as $base) {
            Base::create(['name' => $base, 'location' => $base]);
        }
    //


    // CAREERS SEEDER
        // piloto privado id:1

        Career::create([
            'name' => 'Piloto privado',
            'monthly_payments' => 6,
            'registration_fee' => 4640,
            'monthly_fee' => 5220,
        ]);

        //sobrecargo id:2
        Career::create([
            'name' => 'Sobrecargo',
            'monthly_payments' => 5,
            'registration_fee' => 4640,
            'monthly_fee' => 5975,
        ]);

        //Oficial de Operaciones id:3
        Career::create([
            'name' => 'Oficial de Operaciones',
            'monthly_payments' => 6,
            'registration_fee' => 4640,
            'monthly_fee' => 6720,
        ]);

        $subjects = [
            'Aerodinámica', //1
            'Meteorología', //2
            'Aeronaves y Motores', //3
            'Operaciones Aeronáuticas', //4
            'Mercancias Peligrosas', //5
            'Navegación Aérea', //6
            'Reglamentación Aérea', //7
            'Telecomunicaciones', //8
            'Control de Tráfico Aéreo', //9
            'Servicio a Bordo', //10
            'Procedimientos de Emergencia', //11
            'Reglamentación Aérea', //12
            'Factores Humanos y CRM', //13
            'Geografía Turística', //14
        ];

        foreach ($subjects as $subject) {
            Subject::create([
                'name' => $subject,
            ]);
        }

        //Relaciones de las materias con las carreras: Piloto privado
        $subjectIdsForPiolotoPrivado = [1, 2, 3, 4, 6, 7, 8, 9];
        foreach ($subjectIdsForPiolotoPrivado as $subjectId) {
            DB::table('career_subjects')->insert([ //ids: 1-8
                'id_career' => 1,
                'id_subject' => $subjectId,
            ]);
        }

        //Relaciones de las materias con las carreras: Sobrecargo
        $subjectIdsForSobrecargo = [10, 2, 5, 11, 1, 7, 13, 14];
        foreach ($subjectIdsForSobrecargo as $subjectId) {
            DB::table('career_subjects')->insert([ //ids: 9-16
                'id_career' => 2,
                'id_subject' => $subjectId,
            ]);
        }

        //Relaciones de las materias con las carreras: Oficial de Operaciones
        $subjectIdsForOficialDeOperaciones = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        foreach ($subjectIdsForOficialDeOperaciones as $subjectId) {
            DB::table('career_subjects')->insert([ //ids: 17-25
                'id_career' => 3,
                'id_subject' => $subjectId,
            ]);
        }

        //---------------------//
        // Turn Seeder
        Turn::create([
            'name' => 'Matutino',
        ]);

        Turn::create([
            'name' => 'Vespertino',
        ]);

        // Employee Seeder
        $faker = Faker::create();
        Employee::create([ //id:1
            'name' => 'Dulce Maria',
            'last_names' => 'Gaytan' . ' ' . 'Rocha',
            'email' => 'dulce@maria.com',
            'company_email' => $faker->unique()->companyEmail,
            'phone' => $faker->phoneNumber,
            'cellphone' => $faker->phoneNumber,
            'curp' => $faker->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]{2}'),
            'user_identification' => $faker->unique()->userName,
            'user_type' => 'instructor',
            'id_base' => 1,
        ]);

        Employee::create([ //id:2
            'name' => 'Samuel',
            'last_names' => 'Belkotosky' . ' ' . 'Ortiz',
            'email' => 'samuel@ortiz.com',
            'company_email' => $faker->unique()->companyEmail,
            'phone' => $faker->phoneNumber,
            'cellphone' => $faker->phoneNumber,
            'curp' => $faker->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]{2}'),
            'user_identification' => $faker->unique()->userName,
            'user_type' => 'instructor',
            'id_base' => 1,
        ]);

        Employee::create([ //id:3
            'name' => 'Francisco Javier Celedon',
            'last_names' => 'Martinez' . ' ' . 'Hernandez',
            'email' => 'franjavier@mh.com',
            'company_email' => $faker->unique()->companyEmail,
            'phone' => $faker->phoneNumber,
            'cellphone' => $faker->phoneNumber,
            'curp' => $faker->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]{2}'),
            'user_identification' => $faker->unique()->userName,
            'user_type' => 'instructor',
            'id_base' => 1,
        ]);

        Employee::create([ //id:4
            'name' => 'Auner',
            'last_names' => 'Vega' . ' ' . 'Walls',
            'email' => 'auner@walls.com',
            'company_email' => $faker->unique()->companyEmail,
            'phone' => $faker->phoneNumber,
            'cellphone' => $faker->phoneNumber,
            'curp' => $faker->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]{2}'),
            'user_identification' => $faker->unique()->userName,
            'user_type' => 'instructor',
            'id_base' => 1,
        ]);


        $sobrecargoCareerSubjectsIds = [9, 10, 11, 12, 13, 14, 15, 16];
        $start_date = new \DateTime('2024-01-01');
        $end_date = new \DateTime('2024-02-29');

        foreach($sobrecargoCareerSubjectsIds as $careerSubjectId){
            TeacherSubjectTurn::create([
                'id_teacher' => 1,
                'career_subject_id' => $careerSubjectId,
                'id_turn' => 1,
                'start_date' => $start_date->format('Y-m-d'),
                'end_date' => $end_date->format('Y-m-d'),
                'duration' => 4,
            ]);

            $start_date->modify('+1 month');
            $end_date->modify('+1 month');
        }


        //Oficial de Operaciones
        TeacherSubjectTurn::create([
            'id_teacher' => 2,
            'career_subject_id' => 17,
            'id_turn' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-02-29',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 3,
            'career_subject_id' => 18,
            'id_turn' => 1,
            'start_date' => '2024-04-01',
            'end_date' => '2024-05-31',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 4,
            'career_subject_id' => 19,
            'id_turn' => 1,
            'start_date' => '2024-07-01',
            'end_date' => '2024-08-31',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 4,
            'career_subject_id' => 20,
            'id_turn' => 1,
            'start_date' => '2024-10-01',
            'end_date' => '2024-11-30',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 3,
            'career_subject_id' => 21,
            'id_turn' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-02-29',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 2,
            'career_subject_id' => 22,
            'id_turn' => 1,
            'start_date' => '2024-04-01',
            'end_date' => '2024-05-31',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 2,
            'career_subject_id' => 23,
            'id_turn' => 1,
            'start_date' => '2024-07-01',
            'end_date' => '2024-08-31',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 3,
            'career_subject_id' => 24,
            'id_turn' => 1,
            'start_date' => '2024-10-01',
            'end_date' => '2024-11-30',
            'duration' => 4,
        ]);
        
        TeacherSubjectTurn::create([
            'id_teacher' => 4,
            'career_subject_id' => 25,
            'id_turn' => 1,
            'start_date' => '2024-10-01',
            'end_date' => '2024-11-30',
            'duration' => 4,
        ]);

        //Piloto Privado
        TeacherSubjectTurn::create([
            'id_teacher' => 2,
            'career_subject_id' => 1,
            'id_turn' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-02-29',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 3,
            'career_subject_id' => 2,
            'id_turn' => 1,
            'start_date' => '2024-04-01',
            'end_date' => '2024-05-31',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 4,
            'career_subject_id' => 3,
            'id_turn' => 1,
            'start_date' => '2024-07-01',
            'end_date' => '2024-08-31',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 4,
            'career_subject_id' => 4,
            'id_turn' => 1,
            'start_date' => '2024-10-01',
            'end_date' => '2024-11-30',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 3,
            'career_subject_id' => 5,
            'id_turn' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-02-29',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 2,
            'career_subject_id' => 6,
            'id_turn' => 1,
            'start_date' => '2024-04-01',
            'end_date' => '2024-05-31',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 2,
            'career_subject_id' => 7,
            'id_turn' => 1,
            'start_date' => '2024-07-01',
            'end_date' => '2024-08-31',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 3,
            'career_subject_id' => 8,
            'id_turn' => 1,
            'start_date' => '2024-10-01',
            'end_date' => '2024-11-30',
            'duration' => 4,
        ]);



        #//////////////#

        InfoFlight::create([
            "equipo" => "simulador",
            "price" => 800,
            "min_credit_hours_required" => 2,
            "min_hours_required" => 12,
        ]);

        InfoFlight::create([
            "equipo" => "matricula",
            "price" => 0,
            "min_credit_hours_required" => 2,
            "min_hours_required" => 20,
        ]);
        InfoFlight::create([
            "equipo" => "XBPDY",
            "price" => 3100,
            "min_credit_hours_required" => 2,
            "min_hours_required" => 20,
        ]);
    }
}
