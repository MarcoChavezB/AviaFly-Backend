<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\AirPlane;
use Faker\Factory as Faker;
use App\Models\Base;
use App\Models\Career;
use App\Models\Employee;
use App\Models\FlightObjetive;
use App\Models\InfoFlight;
use App\Models\Lesson;
use App\Models\LessonObjetiveSession;
use App\Models\Session;
use App\Models\Stage;
use App\Models\StageSession;
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

        // airplane Seeders
        AirPlane::create([
            'model' => 'CESSNA 172',
            'limit_hours' => 50,
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
        foreach ($lessonsIntroduccion as $lesson) {
            Lesson::create([
                'name' => $lesson,
            ]);
        }

        $sessions = [
            "Sesión 1 - Introducción al Vuelo",
            "Sesión 2 - Controlando la Aeronave",
            "Sesión 3 - Velocidad y el Aeropuerto",
            "Sesión 4 - Desplomando la Aeronave",
            "Sesión 5 - Correcciones por Viento",
            "Sesión 6 - Conoce tus Instrumentos",
            "Sesión 7 - Despegues y Aterrizajes",
            "Sesión 8 - Emergencias",
            "Sesión 9 - Repaso General",
            "Sesión 10 - Evaluacion Etapa 1",
            "Sesión 1 - Pistas Cortas Y Suaves",
            "Sesión 2 - Utilizando Las Aerovias",
            "Sesión 3 - Primer Vuelo Solo",
            "Sesión 4 - Mejorando Tus Habilidades",
            "Sesión 5 - Evaluacion Etapa 2",
            "Sesión 1 - Vamos De Ruta",
            "Sesión 2 - Nuevamente de Ruta",
            "Sesión 3 - Tu Primer Ruta Solo",
            "Sesión 4 - Nuevos Horizontes",
            "Sesión 5 - Mejorando tus Habilidades",
            "Sesión 6 - Evaluacion Etapa 3",
            "Sesión 1 - Evaluado Por Tu Instructor",
            "Sesión 2 - Preparacion Para Tu Examen",
            "Sesión 3 - Evaluación Final"
        ];

        foreach ($sessions as $session) {
            Session::create([
                'name' => $session,
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
        echo "Lecciones de objetivos de vuelo creadas\n";

    }


}
