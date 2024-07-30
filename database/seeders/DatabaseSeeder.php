<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Http\Controllers\PaymentMethodController;
use App\Models\AirPlane;
use Faker\Factory as Faker;
use App\Models\Base;
use App\Models\Career;
use App\Models\Consumable;
use App\Models\Discount;
use App\Models\Employee;
use App\Models\FlightObjetive;
use App\Models\InfoFlight;
use App\Models\Lesson;
use App\Models\LessonObjetiveSession;
use App\Models\PaymentMethod;
use App\Models\Payments;
use App\Models\Session;
use App\Models\Stage;
use App\Models\StageSession;
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

        //Oficial de Operaciones id:3
        Career::create([
            'name' => 'Piloto comercial',
            'monthly_payments' => 12,
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

        $faker = Faker::create();
        Employee::create([ //id:1
            'name' => 'Rene',
            'last_names' => 'Gaytan' . ' ' . 'Rocha',
            'email' => 'rene@maria.com',
            'company_email' => $faker->unique()->companyEmail,
            'phone' => $faker->phoneNumber,
            'cellphone' => $faker->phoneNumber,
            'curp' => $faker->regexify('[A-Z]{4}[0-9]{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]{2}'),
            'user_identification' => $faker->unique()->userName,
            'user_type' => 'flight_instructor',
            'id_base' => 1,
            'weight' => 100
        ]);



        $sobrecargoCareerSubjectsIds = [9, 10, 11, 12, 13, 14, 15, 16];
        $start_date = new \DateTime('2024-01-01');
        $end_date = new \DateTime('2024-02-29');

        foreach ($sobrecargoCareerSubjectsIds as $careerSubjectId) {
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
            "equipo" => "XBPDY",
            "price" => 3100,
            "min_credit_hours_required" => 2,
            "min_hours_required" => 20,
        ]);

        // airplane Seeders
        AirPlane::create([
            'model' => 'CESSNA 172',
            'limit_hours' => 48,
            'limit_weight' => 2300,
            'limit_passengers' => 4,
        ]);

        // lesson seerers
        $lessonsIntroduccion = [
            "Inspección antes del vuelo",
            "Ubicación del Extinguidor",
            "Puertas y Cinturones de Seguridad",
            "Encendido del Motor",
            "Rodaje",
            "Revisión antes del Despegue y Prueba de Motor",
            "Despegue Normal y Ascenso Inicial",
            "Ascensos",
            "Nivelar",
            "Utilización de Compensador",
            "Vuelo Recto y Nivelado",
            "Tendencias de la Aeronave para Volar Recto y Nivelado",
            "Virajes con Banqueo Medio",
            "Descensos",
            "Aproximación Normal y Aterrizaje",
            "Procedimientos Después del Aterrizaje",
            "Estacionamiento y Aseguramiento de la Aeronave",
            "Procedimientos Posteriores al Vuelo",
            "Coordinación en Virajes",
            "Cuanta Presión Hacia Atrás Utilizar en Virajes",
            "Tendencias de Viraje Izquierda y Derecha",
            "Ascensos y Ascensos con Virajes",
            "Planeo",
            "Descensos con y sin Flaps",
            "Comenzar una Ida Al Aire con Aletas Completamente Abajo",
            "Reconocimiento de Velocidad Baja",
            "Rodaje Con Viento Cruzado",
            "Vuelo Recto y Nivelado (RCI)*",
            "Ascenso Con Velocidad Constante y Nivelar (RCI)*",
            "Descenso Con Velocidad Constante y Nivelar (RCI)*",
            "Virajes: Entrada y Termino (RCI)*",
            "Vuelo a Velocidad de Aproximación",
            "Maniobrando en Vuelo Lento",
            "Comunicaciones en el Circuito de Transito",
            "Evasión de Colisiones",
            "Operación de Sistemas",
            "Descender y Nivelar",
            "Ascensos Con Velocidad Constante",
            "Descensos Con y Sin Potencia",
            "Desplome Sin Potencia (Fase de Aproximación al Aterrizaje)",
            "Desplome Con Potencia (Despegue y Ascenso Inicial)",
            "Patrones Rectangulares",
            "Deslizamiento hacia el Frente (Forward Slip)",
            "Virajes Sobre un Punto",
            "S’s Sobre el Camino (S‐Turns)",
            "Radiocomunicaciones",
            "Circuitos de Tránsito",
            "Despegue y Ascenso con Viento Cruzado",
            "Aproximación y Aterrizaje con Viento Cruzado",
            "Aproximación y Aterrizaje Fallidos",
            "Procedimientos de Emergencia",
            "Descensos de Emergencia",
            "Volando Sin Velocímetro",
            "Volando Sin Altímetro",
            "Aproximación de emergencia y aterrizaje",
            "Despegue y Ascenso Inicial Normal / Con Viento Cruzado",
            "Deslizamiento Hacia el Frente (Forward Slip)",
            "Aproximación y Aterrizaje Normal / Con Viento Cruzado",
            "Ida Al Aire",
            "Comunicaciones de Salida y Llegada",
            "Aterrizando en un Aeródromo No Controlado",
            "Operación de los Sistemas de la Aeronave",
            "Rodaje Normal y/o Con Viento Cruzado",
            "Revisión Antes del Despegue",
            "Despegue Normal y/o Con Viento Cruzado",
            "Procedimientos de Evasión de Tráfico",
            "Evasión de Turbulencia de Estela",
            "Maniobrando en Vuelo lento",
            "Vuelo lento con distracciones reales para reconocer y recuperar de desplomes en vuelo recto o en virajes.",
            "Desperfectos en los Sistemas y Equipo del Avión",
            "Navegación Hacia el Área de Practica",
            "Despegue de Pista Corta y Ascenso",
            "Despegue de Pista Suave y Ascenso",
            "Aproximación y Aterrizaje a Pista Corta",
            "Aproximación y Aterrizaje a Pista Suave",
            "Orientación con VOR y Seguimiento de Radiales",
            "Recuperación de Actitudes Inusuales",
            "Procedimientos en Caso de Desorientación",
            "Vuelo Solo Supervisado",
            "3 Aproximaciones con Aterrizaje Completo",
            "Navegación al Área de Practica",
            "Navegación de Regreso al Aeropuerto",
            "Descensos de Emergencia y Ascensos utilizando Radio ayudas o Vectores. (RCI)",
            "Utilizando Radio Comunicaciones, Sistemas de Navegación/Facilidades y Servicios de Radar. (RCI)",
            "Aeropuertos Controlados",
            "Utilización del ATIS",
            "Elaboración de un Plan de Vuelo de Ruta",
            "Utilización de los Servicios de Aproximación y Salidas",
            "Operación en Aeropuertos de Alta Densidad",
            "Intercepción del Curso a Volar",
            "Navegación por VOR",
            "Navegación por ADF",
            "Navegación por GPS",
            "Ajustes de Potencia y Mezcla en Crucero",
            "Desviación a un Aeropuerto Alterno",
            "Procedimiento en Caso de Perdida",
            "Estimados de Velocidad y ETA’s",
            "Ajustes y Localización de Posición Utilizando Radioayudas",
            "Vuelo en Aerovías",
            "CTAF (UNICOM) en Aeropuertos No Controlados",
            "Mínimo 1 Aterrizaje en un Aeropuerto mas de 50NM del Aeropuerto de Salida",
            "Cierre de Plan de Vuelo",
            "Cartas Seccionales",
            "Cartas WAC",
            "Publicaciones Aeronáuticas",
            "Información Meteorológica",
            "Requerimientos de Combustible",
            "Desempeño y Limitaciones",
            "Peso y Balance",
            "Log de Navegación",
            "Plan de Vuelo ICAO",
            "Factores Aeromedicos",
            "Navegación VOR y ADF",
            "Navegación GPS",
            "Navegación Visual",
            "Navegación por Estima",
            "Operación en Aeropuertos No Familiarizados",
            "Estimando Velocidad Verdadera",
            "Estimando ETA’s",
            "Estimando Velocidad Absoluta",
            "Ascenso con Velocidad Constante (IR)",
            "Descenso con Velocidad Constante (IR)",
            "Aproximación Normal y Aterrizajes Con y Sin Luz de Aterrizaje",
            "Planeación de tu Ruta",
            "Organización de tu Cabina",
            "Salida",
            "Calculo de Velocidad Absoluta",
            "Procedimientos de Evasión de Colisiones",
            "Procedimientos de Emergencias",
            "Ascensos con Velocidad Constante (RCI)",
            "Descensos con Velocidad Constante (RCI)",
            "Despegues y Aterrizajes en Pistas Cortas",
            "Despegues y Aterrizajes en Pistas Suaves",
            "Aterrizaje y Despegue con Viento Cruzado",
            "Controles Cruzados al Aterrizaje",
            "Reconocimiento y Recuperación de Desplomes",
            "Operaciones de Noche",
            "Maniobras con Referencia Terrestre",
            "Averías en Sistemas y Equipos",
            "Equipo de Supervivencia",
            "Vuelo a Velocidades Bajas con Distracciones Reales",
            "Reconocimiento y Recuperación de Desplomes",
            "Señales Visuales de Luces de la Torre de Control",
            "Evasión de Turbulencia de Estela",
            "Señalamiento y Marcas en el Aeropuerto y Pistas",
            "Iluminación de Aeropuerto y Pistas",
            "Vuelo lento con distracciones reales para reconocer y recuperar de desplomes en vuelo recto o en virajes",
            "Descensos Con y Sin Flaps",
            "Descensos Con Velocidad Constante",
            "Virajes Pronunciados (Steep Turns)",
            "Vuelo Recto y Nivelado (RCI y Hood)",
            "Ascensos y Descensos (RCI y Hood)",
            "Virajes Coordinados (RCI y Hood)",
            "Virajes a un Rumbo (RCI y Hood)",
            "Discusión Sobre Conciencia de Barrenas",
            "Despegues y Aterrizajes Normales",
            "Ascensos y Descensos con Virajes",
            "Vuelo Recto y Nivelado (RCI)",
            "Ascensos, Virajes y Descensos (RCI)",
            "Aproximación y Aterrizaje de Emergencia",
            "Circuitos de Transito",
            "Deslizamiento Hacia el Frente en el Aterrizaje",
            "Aproximación y Aterrizaje Normal y/o Con Viento Cruzado",
            "Deslizamiento Hacia el Frente en el Aterrizaje (Forward Slip)",
            "Desplome Sin Potencia (RCI)",
            "Desplome Con Potencia (RCI)",
            "Aproximación para Pista Corta y Aterrizaje",
            "Aproximación para Pista Suave y Aterrizaje",
            "Forward Slip (Controles Cruzados)",
            "Aproximación y Aterrizaje Normal",
            "Despegue y Ascenso Inicial Normal y/o Con Viento Cruzado",
            "Virajes Sobre Un Punto",
            "Orientación con VOR y Seguimiento de Radiales. (RCI)",
            "Procedimiento en Caso de Pérdida",
            "Mínimo 1 Aterrizaje en un Aeropuerto más de 50NM del Aeropuerto de Salida",
            "Procedimientos para Evadir Colisiones",
            "Factores Aeromédicos",
            "Por lo menos 1 aterrizaje en un Aeropuerto más de 50 NM",
            "Despegue y Ascenso Normal",
            "Intercepción del Curso de Vuelo",
            "Navegación VOR (Ref. Visual – RCI)",
            "Cálculos de ETA’s",
            "Procedimientos en Caso de Perderte",
            "Manejo de Emergencias",
            "Ajustes de Potencia y Mezcla",
            "Cursos Rectangulares",
            "Virajes Pronunciados",
            "Desplome con Potencia (RV‐RCI)",
            "Desplome sin Potencia (RV‐RCI)",
            "Recuperación de Actitudes Inusuales (RCI)",
            "Descensos y Ascensos de Emergencia con Radioayudas o Vectores (RCI)",
            "Utilización de Radiocomunicaciones, Sistemas de Navegación y Servicios Radar (RCI)",
            "Operaciones de Emergencias",
            "Vuelo Lento",
            "Desplome con Potencia",
            "Desplome sin Potencia",
            "Maniobras Asignadas por tu Instructor",
            "Certificados y Documentos",
            "Planeación de un Vuelo de Ruta",
            "Sistema de Espacios Aéreos",
            "Desempeño y Limitaciones de la Aeronave",
            "Lista de Equipo Mínimo",
            "Inspección en Prevuelo",
            "Organización de la Cabina",
            "Prueba de Motor",
            "Despegue en Pista Corta",
            "Despegue en Pista Suave",
            "Vuelo Recto y Nivelado (RV‐RCI)",
            "Ascensos con Velocidad Constante (RV‐RCI)",
            "Descensos con Velocidad Constante (RV‐RCI)",
            "Descenso de Emergencia y Ascensos utilizando Radio Ayudas y Vectores",
            "Uso de Radiocomunicaciones, Sistemas de Navegación y Facilidades y Servicios Radar (RCI)",
            "Desplome Sin Potencia (Aterrizaje y Aproximación)",
            "Discusión Sobre Barrenas",
            "Aproximación y Aterrizaje a Pistas Cortas",
            "Aproximación y Aterrizaje a Pistas Suaves",
            "Aproximación Normal y con Viento Cruzado",
            "Virajes a Rumbos Específicos (RV‐RCI)",
            "Nuevamente de ruta"
        ];

        $lessonsWithFiles = [
            'Despegue Normal y Ascenso Inicial' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/007/original/tbo35ue56xh8?1501274429',
            'Aproximación Normal y Aterrizaje' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/015/original/0oadorye3ui0?1501274429',
            'Rodaje Con Viento Cruzado' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/041/original/9ec5ivbsnecp?1501274430',
            'Maniobrando en Vuelo Lento' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/047/original/o38eikz2ipke?1501274430',
            'Desplome Sin Potencia (Fase de Aproximación al Aterrizaje)' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/066/original/c2wtecqpn60d?1501274431',
            'Desplome Con Potencia (Despegue y Ascenso Inicial)' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/067/original/sq5yg1my3in2?1501274431',
            'Patrones Rectangulares' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/075/original/4kdf8pa4b1pw?1501274432',
            'Deslizamiento hacia el Frente (Forward Slip)' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/077/original/hnbiq5rsc0iw?1501274433',
            'Virajes Sobre un Punto' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/077/original/hnbiq5rsc0iw?1501274433',
            'Circuitos de Tránsito' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/081/original/bfincpsg9um4?1501274434',
            'Virajes Pronunciados (Steep Turns)' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/089/original/j2z4562zivfx?1501274435',
            'Despegue y Ascenso con Viento Cruzado' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/103/original/kgtyyqbuvio2?1501274436',
            'Aproximación y Aterrizaje con Viento Cruzado' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/104/original/20xs315ak1ak?1501274436',
            'Aproximación y Aterrizaje Fallidos' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/105/original/zmvxwnt6jv6b?1501274436',
            'Circuitos de Transito' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/112/original/kb6tcyahoqak?1501274437',
            'Aproximación de emergencia y aterrizaje ' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/458/original/rnladvpvomra?1501274458',
            'Despegue y Ascenso Inicial Normal / Con Viento Cruzado' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/115/original/f7u6jhi2j3uv?1501274437',
            'Deslizamiento Hacia el Frente (Forward Slip)' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/116/original/1w5653l0ec5l?1501274437',
            'Aproximación y Aterrizaje Normal / Con Viento Cruzado' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/117/original/kj0i5bcym6t9?1501274437',
            'Ida Al Aire' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/459/original/41p7p9kh0j91?1501274459',
            'Maniobrando en Vuelo lento' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/141/original/6ketvmdgng48?1501274438',
            'Patrones Rectangulares' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/146/original/3i8bibcf3tz3?1501274438',
            'S’s Sobre el Camino (S‐Turns)' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/147/original/g3fdvn51pfrn?1501274438',
            'Aproximación y Aterrizaje de Emergencia' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/152/original/i3a16ofaqi8n?1501274439',
            'Deslizamiento Hacia el Frente en el Aterrizaje' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/154/original/8fbx8akce7av?1501274440',
            'Deslizamiento Hacia el Frente en el Aterrizaje (Forward Slip)' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/425/original/uufnu7u6jhdy?1501274456',
            'Despegue de Pista Corta y Ascenso' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/210/original/s8b43nr7eyiw?1501274442',
            'Despegue de Pista Suave y Ascenso' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/211/original/jj2s5msb7cdj?1501274442',
            'Aproximación y Aterrizaje a Pista Corta' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/212/original/4gy6w5ddg1hg?1501274442',
            'Aproximación y Aterrizaje a Pista Suave' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/213/original/7pa1wwan1y02?1501274443',
            'Desplome Sin Potencia (RCI)' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/223/original/qr1m9o252vq2?1501274444',
            'Desplome Con Potencia (RCI)' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/224/original/ym27pg4lo0yq?1501274445',
            'Forward Slip (Controles Cruzados)' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/231/original/e06e6yzv0cj8?1501274446',
            'Desplome con Potencia (RV‐RCI)' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/322/original/roofmvxsal2d?1501274448',
            'Desplome sin Potencia (RV‐RCI)' => 'https://tecblue.s3.amazonaws.com/tareas/imagenes/000/000/323/original/1u8zzpy3o0mm?1501274449',
        ];

        foreach ($lessonsIntroduccion as $lesson) {
            $data = ['name' => $lesson];

            if (isset($lessonsWithFiles[$lesson])) {
                $data['file'] = $lessonsWithFiles[$lesson];
            }

            Lesson::create($data);
        }

        $sessions = [
            [
                'name' => 'Sesión 1 - Introducción al Vuelo',
                'session_objetive' => 'En esta lección aprenderás a utilizar la lista de comprobación para realizar una inspección antes del vuelo y posteriormente realizar el proceso de arranque. Aprenderás a rodar la aeronave utilizando los pedales, acelerador y frenos para girar, controlar la velocidad y parar la aeronave. En el aire, aprenderás a utilizar los controles de vuelo para realizar un ascenso, descenso, virajes y vuelo recto y nivelado. Completado el vuelo en el aire aprenderás a cortar el motor y apagar los sistemas de la aeronave correctamente, mover el avión en tierra, asegurarlo y dejarlo listo para el siguiente vuelo.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando puedas con ayuda de tu instructor, poner en marcha la aeronave y rodarla desde la plataforma hasta la pista y en vuelo, volar el avión desde el área de práctica hacia el aeropuerto de regreso.'
            ],
            [
                'name' => 'Sesión 2 - Controlando la Aeronave',
                'session_objetive' => 'En esta lección aprenderás en que momento el avión presentara tendencias de viraje hacia la izquierda y derecha y como controlarlas. También aprenderás a controlar la velocidad durante ascensos y descensos. Aplicarás lo aprendido para realizar ascensos y descensos con virajes. Aprenderás a utilizar los flaps para ayudar a incrementar el descenso sin aumentar la velocidad. Finalmente se te transmitirán puntos clave para identificar cuando la aeronave tenga poca velocidad.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando puedas con ayuda de tu instructor, usar la lista de comprobación para realizar una inspección previa al vuelo, poner en marcha el motor, revisar los sistemas antes del despegue y apagar el motor posterior al vuelo, así como rodar la aeronave durante el movimiento terrestre. Con ayuda de tu instructor podrás controlar la aeronave desde el despegue hasta el aterrizaje, realizando virajes coordinados, ascensos, descensos y nivelar en vuelo recto. También serás capaz de volar en vuelo recto y nivelado utilizando referencias visuales exteriores.'
            ],
            [
                'name' => 'Sesión 3 - Velocidad y el Aeropuerto',
                'session_objetive' => 'En esta lección aprenderás maniobras para controlar la aeronave en referencia a la velocidad, inclusive a velocidades menores que la de crucero. Comenzarás a aprender los procedimientos en el aeropuerto y las situaciones que pudieran causar un desplome. También aprenderás a comparar los instrumentos con la vista afuera cuando se realiza una maniobra.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando puedas realizar despegues sin ayuda de tu instructor; controlar la aeronave en los aterrizajes con ayuda de tu instructor; y demostrar comunicaciones y procedimientos en el circuito de transito correctos. Tolerancias serán: Altitud de +/- 200 ft para vuelo nivelado, rumbo +/- 20° para vuelo recto y velocidad de +10/-5 kts.'
            ],
            [
                'name' => 'Sesión 4 - Desplomando la Aeronave',
                'session_objetive' => 'En esta lección aprenderás el proceso de desplome de una aeronave, como detectarlo antes de que ocurra incluyendo el sonido, elementos visuales y sensaciones para poder recuperar la aeronave de regreso a vuelo normal.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando puedas realizar una inspección antes del vuelo sin ayuda de tu instructor, utilizar la lista de comprobación para todas las operaciones terrestres y en vuelo y controlar la aeronave en todas las maniobras básicas con ayuda mínima de tu instructor. También serás capaz de reconocer cuando la aeronave se aproxima a un desplome y poder regresarla a vuelo recto y nivelado. Tendrás que mantener la altitud +/- 200 ft, rumbo +/- 15°, y en la velocidad mantener + 10 / - 5 kts mientras maniobras en vuelo lento. NOTA: A partir de esta lección todas las inspecciones antes y después del vuelo y procedimientos implicados tendrán mandatoriamente que ejecutarse por el alumno. Por ello después de esta lección no aparecerán contempladas en el contenido de la misma.'
            ],
            [
                'name' => 'Sesión 5 - Correcciones por Viento',
                'session_objetive' => 'En esta lección aprenderás como controlar la trayectoria de la aeronave con respecto a la Tierra corrigiendo por el viento para que pueda volar una trayectoria deseada. Para ello aprenderás a realizar diferentes maniobras con referencias terrestres que te ayudaran a cumplir el objetivo.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando puedas controlar la aeronave para mantener una trayectoria con referencia a la Tierra corrigiendo por la deriva generada por el viento y puedas mantener la altitud +/- 200 ft y la velocidad +/- 10 kts durante vuelo recto y nivelado y virajes.'
            ],
            [
                'name' => 'Sesión 6 - Conoce tus Instrumentos',
                'session_objetive' => 'En esta lección aprenderás a controlar la aeronave utilizando los instrumentos abordo. Aprenderás como se siente y se ve la aeronave en virajes más pronunciados (45 grados). También aprenderás a incrementar el descenso en la aproximación sin utilizar los flaps y mantenerte alineado a la pista.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando puedas controlar la aeronave con referencia a los instrumentos y mantener en estas maniobras +/- 200 ft la altitud, +/- 20° en el rumbo y la velocidad +/- 15 kts. Demostrar a tu instructor como realizar una inspección previa al vuelo de forma satisfactoria, utilizar las listas de comprobación de forma correcta para todas las fases de vuelo y controlar la aeronave en las maniobras básicas sin ayuda de tu instructor. Mantener durante un viraje pronunciado una altitud de +/- 200 ft, el ángulo de banqueo de 45°, +/- 10° y terminar la maniobra en el rumbo inicial +/- 20°. También serás capaz de aplicar los controles necesarios para entrar en un deslizamiento hacia el frente durante una aproximación para aterrizar y con ayuda de tu instructor ser capaz de mantener alineada la línea central de la pista durante despegues y aterrizajes con viento cruzado.'
            ],
            [
                'name' => 'Sesión 7 - Despegues y Aterrizajes',
                'session_objetive' => 'En esta lección aprenderás a controlar la aeronave en despegues y aterrizajes normales y con viento cruzado. También aprenderás el procedimiento para realizar una aproximación fallida.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando seas capaz de despegar, ascender inicialmente corrigiendo por la deriva del viento, incorporarte al circuito de tránsito, establecer una aproximación y aterrizar corrigiendo por la deriva del viento sin ayuda de tu instructor.'
            ],
            [
                'name' => 'Sesión 8 - Emergencias',
                'session_objetive' => 'En esta lección practicaras despegues y aterrizajes. Analizaras diferentes situaciones de emergencias simuladas y como aplicar los procedimientos aprendidos para poder resolverlas de forma segura. También aprenderás a aterrizar sin Velocímetro o Altímetro.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando puedas aplicar correctamente los procedimientos utilizados durante una aproximación y aterrizaje de emergencia, logrando planear y llegar de manera segura hacia el punto de aterrizaje escogido, manteniendo tu velocidad de planeo +/- 10 kts.'
            ],
            [
                'name' => 'Sesión 9 - Repaso General',
                'session_objetive' => 'En esta lección el instructor revisara contigo lo que has aprendido hasta este punto y te ayudará en afinar tus habilidades necesarias para que todas las maniobras se realicen bajo los estándares prácticos de evaluación. Se discutirá la operación en los diferentes espacios aéreos y los requerimientos que se necesita para cada uno.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando puedas demostrar a tu instructor controlar de manera segura la aeronave por ti mismo, en todas las fases del vuelo, utilizar procedimientos en tierra, de comunicación y de entrada y salida del circuito de transito correctos, esto para aeropuertos controlados y no controlados. Deberás de efectuar despegues, aterrizajes e idas al aire, así como todas las maniobras anteriormente aprendidas sin la asistencia de tu instructor.'
            ],
            [
                'name' => 'Sesión 10 - Evaluacion Etapa 1',
                'session_objetive' => 'En esta evaluación podrás demostrar tus habilidades en vuelo solo para que el instructor determine si se encuentra preparado para salir del circuito de tránsito en sus próximos vuelos solos.',
                'approvation_standard' => 'La evaluación será satisfactoriamente completada cuando puedas completar de forma correcta las acciones necesarias antes del vuelo y los procedimientos necesarios para tener un vuelo solo seguro en el área de práctica. Tendrás que mantener una altitud de +/‐ 100 ft, rumbos +/‐ 10° y la velocidad de +10/‐5 kts.'
            ],
            [
                'name' => 'Sesión 1 - Pistas Cortas Y Suaves',
                'session_objetive' => 'En esta lección aprenderás los procedimientos para despegar y aterrizar en pistas cortas o de superficie suave, como pasto o tierra.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando puedas reconocer los tipos de pistas en las que tiene que realizar un aterrizaje para pista corta o suave y los procedimientos que involucran cada una de las maniobras practicadas.'
            ],
            [
                'name' => 'Sesión 2 - Utilizando Las Aerovias',
                'session_objetive' => 'En esta lección practicaras la técnica de vuelo implicada en los aterrizajes para pistas cortas y de superficie suave con el propósito de que te sientas más confortable en dichas maniobras. Aprenderás a utilizar los instrumentos de Navegación para ubicarte y poder mantenerte en una aerovía. Aprenderás también a controlar el avión por medio de los instrumentos del avión durante una situación de emergencia.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando puedas realizar los diferentes tipos de despegues y aterrizajes rotando a una velocidad correcta y manteniendo velocidad de ascenso correcta sin variar +10/‐5 kts; volar y mantener una velocidad correcta durante la aproximación estabilizada sin DUAL ‐ LOCAL variar +10/‐5 kts. Volaras una trayectoria correcta y mantendrás altitud +/‐100 ft.'
            ],
            [
                'name' => 'Sesión 3 - Primer Vuelo Solo',
                'session_objetive' => 'En esta lección habrá una parte dual y otra parte solo. En la parte dual tu instructor revisará los procedimientos de despegue y aterrizaje contigo para determinar si realmente te encuentras listo para tu primer vuelo solo. En la parte solo, realizaras tu primer vuelo solo supervisado desde tierra en un circuito de tránsito local.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando realices con éxito tu primer vuelo solo supervisado desde tierra'
            ],
            [
                'name' => 'Sesión 4 - Mejorando Tus Habilidades',
                'session_objetive' => 'En esta lección practicaras solo, las maniobras con las que ya te has familiarizado anteriormente para ganar confianza en ellas y mejorar tus habilidades para realizarlas.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando completes de forma segura el vuelo solo. Durante tus prácticas, tendrás que apegarte a los lineamientos estándares prácticos manteniendo una altitud de +/‐ 100 ft, rumbo de +/‐ 10° y la velocidad de +10/‐5 kts para las maniobras asignadas.'
            ],
            [
                'name' => 'Sesión 5 - Evaluacion Etapa 2',
                'session_objetive' => 'En esta evaluación aprenderás a utilizar tus instrumentos y radios para controlar el avión y navegar en caso de que no llegaras a ver el suelo. También podrás demostrar como realizas las maniobras previamente aprendidas y completarlas dentro de los parámetros estándares. Se recomienda que esta lección sea impartida por el Jefe de Instructores.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando puedas completar despegues y aterrizajes seguros y suaves manteniendo buen control direccional. También utilices la técnica correcta para recuperar el avión de un desplome y de actitudes inusuales y que puedas iniciar ascensos y descensos DUAL ‐ LOCAL de emergencia con referencia en sus instrumentos utilizando radio comunicaciones, facilidades para navegar y servicios radar. La evaluación práctica será satisfactoria si eres capaz de recuperar el avión de un desplome y de actitudes inusuales y retornar a un vuelo recto y nivelado con referencia a los instrumentos. También seas capaz de interceptar y seguir una radial de un VOR. Las tolerancias durante las maniobras serán de altitud +/‐100 ft., rumbo +/‐10, y velocidad +/‐10 kts. Todas las aproximaciones deberán de ser estabilizadas y la velocidad no variar +10/‐5 kts durante la misma.'
            ],
            [
                'name' => 'Sesión 1 - Vamos De Ruta',
                'session_objetive' => 'En esta lección realizaras tu primer vuelo de ruta. Aprenderás los procedimientos correctos a seguir para abandonar el área local de entrenamiento y dirigirte a otro aeropuerto. Este es el inicio de preparación para que puedas realizar vuelos de ruta solo.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando puedas volar de forma segura un vuelo de ruta, obtengas información meteorológica suficiente y tomes una decisión sobre volar o no volar, basado todo esto en tu planeación de ruta previamente realizada utilizando la metodología aprendida. Es indispensable presentarte al vuelo con el Log de Navegación hecho, así como con la información meteorológica necesaria.'
            ],
            [
                'name' => 'Sesión 2 - Nuevamente de Ruta',
                'session_objetive' => 'En esta lección realizaras tu segundo vuelo de ruta. Reafirmaras los procedimientos correctos a seguir para abandonar el área local de entrenamiento y dirigirte a otro aeropuerto, además de aprender los procedimientos que implican realizar un desvío a otro aeropuerto en caso de no poder continuar con tu vuelo de la forma que se había planeado.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando puedas volar de forma segura tu vuelo de ruta, y poniendo énfasis la desviacion a otro aeropuerto. Es indispensable presentarte al vuelo con el Log de Navegación hecho, así como con la información meteorológica necesaria.'
            ],
            [
                'name' => 'Sesión 3 - Tu Primer Ruta Solo',
                'session_objetive' => 'En esta lección previo al vuelo, tu instructor realizará una discusión sobre la ruta que vayas a volar. Será tu primer vuelo de ruta solo. Esta experiencia aumentara tu nivel de habilidades, conocimiento y sobre todo confianza ante el vuelo. Esto es un paso necesario para poder convertirte en piloto privado. Este vuelo tendrá que incluir un aterrizaje en un aeropuerto que este al menos 50 NM del aeropuerto de salida.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando completes tu primer vuelo solo de ruta cumpliendo con la planeación realizada previa al mismo.'
            ],
            [
                'name' => 'Sesión 4 - Nuevos Horizontes',
                'session_objetive' => 'En este vuelo tendrás que utilizar tus habilidades adquiridas para planificar y volar una ruta solo. Esta experiencia te servirá para aumentar tu nivel de confianza en el camino para convertirte en un piloto privado. Este vuelo tendrá que ser mínimo de 150 millas con aterrizajes completos y mínimo 3 puntos. Uno de los segmentos del vuelo tendrá que ser una distancia mínima de 50 millas en línea recta.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando completes tu primer vuelo solo de ruta cumpliendo con su planeación previa al mismo. Al término del vuelo, tu instructor revisara tu log de navegación. La revisión de tiempos en cada punto, no deberá de variar en mas de +/‐5 minutos.'
            ],
            [
                'name' => 'Sesión 5 - Mejorando tus Habilidades',
                'session_objetive' => 'En este vuelo practicarás las maniobras con las que ya te has familiarizado anteriormente en tu segunda fase de instrucción, esto para ganar confianza en ellas y ajustar cualquier detalle que consideres conforme a los estándares prácticos. El propósito principal de esta lección es que puedas ajustar tus ETA’s calculados en tu planeación y realices con mayor seguridad tu desvío a otro aeropuerto.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando completes tu primer vuelo solo de ruta cumpliendo con su planeación previa al mismo. Al término del vuelo, tu instructor revisara tu log de navegación. La revisión de tiempos en cada punto, no deberá de variar en más de +/-5 minutos.'
            ],
            [
                'name' => 'Sesión 6 - Evaluacion Etapa 3',
                'session_objetive' => 'En esta evaluación podrás demostrar tu habilidad de planeación y conducción de un vuelo de ruta. Se recomienda que esta evaluación sea conducida por el instructor Jefe. Es indispensable que presentes cartas meteorológicas correspondientes y de navegación vigente, así como tu planeación de ruta previamente elaborada.',
                'approvation_standard' => 'La evaluación será satisfactoriamente aprobada cuando demuestres que puedes conducir un vuelo de ruta correctamente y cuentes con el conocimiento necesario para realizar una correcta planeación previa, realizar un análisis meteorológico para la ruta y comprendas las diferentes publicaciones de información para la navegación. Durante el vuelo tendrás que demostrar el uso de los diferentes métodos de navegación, la habilidad para determinar tu posición en cualquier momento del vuelo, obtener ETA’s en menos de 5 minutos y de utilizar la técnica correcta para establecerte en un curso hacia un aeropuerto alterno durante una desviación.'
            ],
            [
                'name' => 'Sesión 1 - Evaluado Por Tu Instructor',
                'session_objetive' => 'En este vuelo tu instructor evaluara tu nivel de precisión y desempeño y determinara en que áreas pudieras necesitar más práctica para que puedas llegar a realizarlas cumpliendo con los lineamentos estándares para piloto privado.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando puedas realizar cada una de las maniobras de forma coordinada y fluida, conforme se especifican DUAL ‐ LOCAL en los lineamientos estándares para piloto privado. Si alguna maniobra no llegara a realizarse dentro de los mismos, necesitaras de entrenamiento adicional ya sea con tu instructor o en un vuelo solo.'
            ],
            [
                'name' => 'Sesión 2 - Preparacion Para Tu Examen',
                'session_objetive' => 'En este vuelo practicaras las distintas maniobras de vuelo aprendidas haciendo especial énfasis en corregir cualquier falla en preparación para tu examen de progreso final.',
                'approvation_standard' => 'La lección será satisfactoriamente completada cuando hayas podido corregir los detalles en cada una de las maniobras que fueron detectados en la lección anterior.'
            ],
            [
                'name' => 'Sesión 3 - Evaluación Final',
                'session_objetive' => 'Esta es tu evaluación final, tu examen práctico de Piloto Privado. Estas a un paso de poder obtener tu licencia. Podrás demostrar en este vuelo tus habilidades como piloto privado y deberás de exhibir un buen juicio para tomar decisiones. El evaluador determinará al final del vuelo si tus habilidades son satisfactorias, y se apegan a estándares prácticos de evaluación. REFERENCIA: Utiliza tu manual de estándares prácticos, en donde podrás encontrar una guía de preparación para tu examen.',
                'approvation_standard' => 'La evaluación será satisfactoriamente aprobada cuando demuestres una competencia que cumpla con los lineamientos estándares de evaluación descritos para tu evaluación práctica de Piloto Privado.'
            ]
        ];


        foreach ($sessions as $session) {
            Session::create([
                'name' => $session['name'],
                'session_objetive' => $session['session_objetive'],
                'approvation_standard' => $session['approvation_standard']
            ]);
        }

        $etapas = [
            "Etapa 1",
            "Etapa 2",
            "Etapa 3",
            "Etapa 4",
        ];

        foreach ($etapas as $etapa) {
            Stage::create([
                'name' => $etapa,
            ]);
        }

        $stageSession = [
            1 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            2 => [11, 12, 13, 14, 15],
            3 => [16, 17, 18, 19, 20, 21],
            4 => [22, 23, 24],
        ];

        foreach ($stageSession as $stage => $sessions) {
            foreach ($sessions as $session) {
                StageSession::create([
                    'id_stage' => $stage,
                    'id_session' => $session,
                ]);
            }
        }
        $flight_objetives = [
            "Lección de Introducción",
            "Lección de Repaso"
        ];

        foreach ($flight_objetives as $flight_objetive) {
            FlightObjetive::create([
                'name' => $flight_objetive,
            ]);
        }


        $lessons_objetives_sessions = [
            1 => [
                1 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18],
            ],
            2 => [
                1 => [19, 20, 21, 22, 23, 24, 25, 26],
                2 => [1, 4, 5, 6, 7, 9, 10, 11, 8, 14, 13, 15, 16, 17],
            ],
            3 => [
                1 =>[27, 28, 29, 30, 31, 32, 33, 34, 35],
                2 => [1, 36, 4, 5, 6, 7, 9, 10, 37, 38, 39, 148, 22, 15, 16, 17]
            ],
            4 => [
                1 => [40, 41],
                2 => [7, 33, 11, 38, 149, 15, 35],
            ],
            5 => [
                1 => [42, 43, 44, 45],
                2 => [46, 7, 47, 35, 10, 39, 148, 40, 41, 15],
            ],
            6 => [
                1 => [150, 57, 151, 152, 153, 154],
                2 => [155, 156],
            ],
            7 => [
                1 => [48, 49, 50],
                2 => [47, 156],
            ],
            8 => [
                1 => [51, 52, 53, 54, 55],
                2 => [56, 57, 58, 59],
            ],
            9 => [
                1 => [60, 61],
                2 => [1, 62, 4, 46, 63, 64, 65, 66, 67, 157, 158, 159, 68, 40, 41, 155, 150, 42, 45, 44, 70, 51, 52, 160, 161, 162, 59, 163],
            ],
            10 => [
                1 => [1, 62, 4, 46, 63, 64, 65, 66, 67, 157, 158, 159, 33, 40, 41, 69, 155, 70, 150, 42, 45, 44, 51, 52, 160, 161, 164, 59, 163],
            ],
// etapa 2
            11 => [
                1 => [72, 73, 74, 75],
                2 => [42, 44, 45, 33, 40, 41, 69, 155]
            ],
            12 => [
                1 => [76, 165, 166, 77, 78],
                2 => [72, 73, 167, 168, 169, 45, 44]
            ],
            13 => [
                1 => [4, 46, 64, 7, 47, 59, 170],
                2 => [79, 80]
            ],
            14 => [
                1 => [171, 81, 42, 45, 172, 82, 161, 163],
            ],
            15 => [
                1 => [173, 83, 84, 165, 166, 77, 72, 73, 74, 75],
            ],
// etapa 3
// s1
            16 => [
                1 => [85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 174, 97, 98, 99, 100, 175, 176, 102],
            ],
// completado
            17 => [
                1 => [219],
            ],

            18 => [
                1 => [103, 104, 105, 106, 107, 108, 109, 110, 111, 177],
                2 => [113, 114, 115, 116, 117, 118, 119, 178]
            ],
            19 => [
                1 => [103, 104, 105, 106, 107, 108, 109, 110, 111, 177],
                2 => [91, 114, 115, 116, 117, 120, 119]
            ],
            20 => [
                1 => [179, 121, 122, 123],
            ],
            21 => [
                1 => [124, 106, 125, 126, 180, 181, 115, 116, 127, 182, 128, 95, 183, 184, 185],
            ],
// etapa 4
            22 => [
                1 => [186, 45, 44, 187, 130, 131, 188, 189, 190, 191, 192, 132, 133, 134, 135, 59, 193],
            ],
            23 => [
                1 => [186, 45, 44, 187, 194, 195, 196, 132, 133, 135, 197],
            ],
            24 => [
                1 => [198, 106, 199, 200, 201, 62, 202, 112, 181, 115, 116, 95, 183, 203, 204, 4, 5, 205, 46, 143, 128, 67, 145, 146, 134, 206, 207, 208, 209, 210, 218, 77, 211, 212, 33, 41, 213, 141, 136, 214, 187, 137, 138, 139, 51, 52, 160, 140, 161, 217, 135, 59, 215, 216, 18],
            ],
        ];
        echo "Creando lecciones de objetivos de vuelo\n";
        foreach ($lessons_objetives_sessions as $session => $objectives) {
            foreach ($objectives as $objective => $lessons) {
                foreach ($lessons as $lesson) {
                    LessonObjetiveSession::create([
                        'id_session' => $session,         // Primer nivel: id_session
                        'id_lesson' => $lesson,           // Tercer nivel: id_lesson
                        'id_flight_objetive' => $objective, // Segundo nivel: id_flight_objetive
                    ]);
                }
            }
        }


        echo "Creando consumibles...\n";
        $consumables = [
            "gasolina",
            "aceite"
        ];

        foreach ($consumables as $consumable) {
            Consumable::create([
                'name' => $consumable,
            ]);
        }

        echo "Creando descuentos...\n";

        $discounts = [];

        for ($i = 5; $i <= 50; $i += 5) {
            $discounts[] = [
                'name' => 'Descuento ' . $i . '%',
                'discount' => $i,
                'status' => 1,
            ];
        }

        $discounts[] = [
            'name' => 'Descuento 100%',
            'discount' => 100,
            'status' => 1,
        ];

        foreach ($discounts as $discount) {
            Discount::create($discount);
        }

        echo "Creando métodos de pago...\n";

        $paymentMethods = [
            [
                'type' => 'Efectivo',
                'commission' => 0,
                'status' => true,
            ],
            [
                'type' => 'Transferencia',
                'commission' => 0,
                'status' => true,
            ],
            [
                'type' => 'Credito',
                'commission' => 0,
                'status' => true,
            ],
            [
                'type' => 'Tarjeta CLIP',
                'commission' => 1.04716,
                'status' => true,
            ],
            [
                'type' => 'Inbursa CREDITO',
                'commission' => 1.01566,
                'status' => true,
            ],
            [
                'type' => 'Inbursa DEBITO',
                'commission' => 1.01218,
                'status' => true,
            ],
            [
                'type' => 'Abonos',
                'commission' => 0,
                'status' => true,
            ],
            [
                'type' => 'Credito vuelo',
                'commission' => 0,
                'status' => true,
            ],
        ];

        foreach ($paymentMethods as $paymentMethod) {
            PaymentMethod::create($paymentMethod);
        }

        Employee::create([
            'name' => 'root',
            'last_names' => 'root',
            'email' => 'marco1102004@gmail.com',
            'company_email' => 'marco1102004@gmail.com',
            'phone' => '1234567890',
            'cellphone' => '1234567890',
            'curp' => 'AAMM110200HDFLRR00',
            'user_identification' => '1234567890',
            'user_type' => 'root',
            'id_base' => 1,
        ]);

        User::create([
            'user_identification' => '1234567890',
            'user_type' => 'root',
            'password' => bcrypt('1234567890'),
            'id_base' => 1,
        ]);
    }

}
