<?php

namespace Database\Seeders;

use App\Models\Employee;
use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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



    }
}
