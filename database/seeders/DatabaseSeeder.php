<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Faker\Factory as Faker;
use App\Models\Base;
use App\Models\Career;
use App\Models\Employee;
use App\Models\InfoFlight;
use App\Models\Subject;
use App\Models\TeacherSubjectTurn;
use App\Models\Turn;
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
        $bases = [
            'Torreón',
            'Querétaro',
        ];

        foreach ($bases as $base) {
            Base::create(['name' => $base, 'location' => $base]);
        }

        Career::create([
            'name' => 'Sobrecargo',
            'monthly_payments' => 5,
            'registration_fee' => 4640,
            'monthly_fee' => 5975,
        ]);

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

        Turn::create([
            'name' => 'Matutino',
        ]);

        Turn::create([
            'name' => 'Vespertino',
        ]);

                DB::table('career_subjects')->insert([
            'id_career' => 1,
            'id_subject' => 1,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 1,
            'id_subject' => 2,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 1,
            'id_subject' => 3,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 1,
            'id_subject' => 4,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 1,
            'id_subject' => 5,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 1,
            'id_subject' => 6,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 1,
            'id_subject' => 7,
        ]);

        DB::table('career_subjects')->insert([
            'id_career' => 1,
            'id_subject' => 8,
        ]);

        $faker = Faker::create();

        Employee::create([
            'name' => $faker->firstName,
            'last_names' => $faker->lastName . ' ' . $faker->lastName,
            'email' => $faker->unique()->safeEmail,
            'company_email' => $faker->unique()->companyEmail,
            'phone' => $faker->phoneNumber,
            'cellphone' => $faker->phoneNumber,
            'curp' => $faker->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]{2}'),
            'user_identification' => $faker->unique()->userName,
            'user_type' => 'instructor',
            'id_base' => 1,
        ]);

        Employee::create([
            'name' => $faker->firstName,
            'last_names' => $faker->lastName . ' ' . $faker->lastName,
            'email' => $faker->unique()->safeEmail,
            'company_email' => $faker->unique()->companyEmail,
            'phone' => $faker->phoneNumber,
            'cellphone' => $faker->phoneNumber,
            'curp' => $faker->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]{2}'),
            'user_identification' => $faker->unique()->userName,
            'user_type' => 'instructor',
            'id_base' => 1,
        ]);


        Employee::create([
            'name' => $faker->firstName,
            'last_names' => $faker->lastName . ' ' . $faker->lastName,
            'email' => $faker->unique()->safeEmail,
            'company_email' => $faker->unique()->companyEmail,
            'phone' => $faker->phoneNumber,
            'cellphone' => $faker->phoneNumber,
            'curp' => $faker->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]{2}'),
            'user_identification' => $faker->unique()->userName,
            'user_type' => 'instructor',
            'id_base' => 1,
        ]);

        Employee::create([
            'name' => $faker->firstName,
            'last_names' => $faker->lastName . ' ' . $faker->lastName,
            'email' => $faker->unique()->safeEmail,
            'company_email' => $faker->unique()->companyEmail,
            'phone' => $faker->phoneNumber,
            'cellphone' => $faker->phoneNumber,
            'curp' => $faker->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]{2}'),
            'user_identification' => $faker->unique()->userName,
            'user_type' => 'instructor',
            'id_base' => 1,
        ]);


        Employee::create([
            'name' => $faker->firstName,
            'last_names' => $faker->lastName . ' ' . $faker->lastName,
            'email' => $faker->unique()->safeEmail,
            'company_email' => $faker->unique()->companyEmail,
            'phone' => $faker->phoneNumber,
            'cellphone' => $faker->phoneNumber,
            'curp' => $faker->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]{2}'),
            'user_identification' => $faker->unique()->userName,
            'user_type' => 'instructor',
            'id_base' => 1,
        ]);


        Employee::create([
            'name' => $faker->firstName,
            'last_names' => $faker->lastName . ' ' . $faker->lastName,
            'email' => $faker->unique()->safeEmail,
            'company_email' => $faker->unique()->companyEmail,
            'phone' => $faker->phoneNumber,
            'cellphone' => $faker->phoneNumber,
            'curp' => $faker->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]{2}'),
            'user_identification' => $faker->unique()->userName,
            'user_type' => 'instructor',
            'id_base' => 1,
        ]);


        Employee::create([
            'name' => $faker->firstName,
            'last_names' => $faker->lastName . ' ' . $faker->lastName,
            'email' => $faker->unique()->safeEmail,
            'company_email' => $faker->unique()->companyEmail,
            'phone' => $faker->phoneNumber,
            'cellphone' => $faker->phoneNumber,
            'curp' => $faker->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]{2}'),
            'user_identification' => $faker->unique()->userName,
            'user_type' => 'instructor',
            'id_base' => 1,
        ]);

        Employee::create([
            'name' => $faker->firstName,
            'last_names' => $faker->lastName . ' ' . $faker->lastName,
            'email' => $faker->unique()->safeEmail,
            'company_email' => $faker->unique()->companyEmail,
            'phone' => $faker->phoneNumber,
            'cellphone' => $faker->phoneNumber,
            'curp' => $faker->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]{2}'),
            'user_identification' => $faker->unique()->userName,
            'user_type' => 'instructor',
            'id_base' => 1,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 8,
            'id_subject' => 1,
            'id_turn' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-02-29',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 7,
            'id_subject' => 2,
            'id_turn' => 1,
            'start_date' => '2024-04-01',
            'end_date' => '2024-05-31',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 6,
            'id_subject' => 3,
            'id_turn' => 1,
            'start_date' => '2024-07-01',
            'end_date' => '2024-08-31',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 5,
            'id_subject' => 4,
            'id_turn' => 1,
            'start_date' => '2024-10-01',
            'end_date' => '2024-11-30',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 4,
            'id_subject' => 5,
            'id_turn' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-02-29',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 3,
            'id_subject' => 6,
            'id_turn' => 1,
            'start_date' => '2024-04-01',
            'end_date' => '2024-05-31',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 2,
            'id_subject' => 7,
            'id_turn' => 1,
            'start_date' => '2024-07-01',
            'end_date' => '2024-08-31',
            'duration' => 4,
        ]);

        TeacherSubjectTurn::create([
            'id_teacher' => 1,
            'id_subject' => 8,
            'id_turn' => 1,
            'start_date' => '2024-10-01',
            'end_date' => '2024-11-30',
            'duration' => 4,
        ]);

        InfoFlight::create([
            "flight_type" => "simulador",
            "price" => 800,
            "min_credit_hours_required" => 12,
        ]);

        InfoFlight::create([
            "flight_type" => "monomotor",
            "price" => 3000,
            "min_credit_hours_required" => 20,
        ]);

        InfoFlight::create([
            "flight_type" => "multimotor",
            "price" => 11000,
            "min_credit_hours_required" => 40,
        ]);
    }
}
