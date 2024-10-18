<?php

namespace App\Jobs;

use App\Mail\EmailCheckWarn;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmployeeRecordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        try {
            // Obtener la fecha y hora actuales
            $currentDate = Carbon::now()->format('Y-m-d');
            $currentTime = Carbon::now();
            echo 'Fecha actual: ' . $currentDate . PHP_EOL;
            echo 'Hora actual: ' . $currentTime->toTimeString() . PHP_EOL;

            // Obtener registros de empleados que no han marcado "fin de hora de comida"
            $records = DB::table('employees as e')
                ->join(DB::raw('(SELECT id_employee, MAX(CONCAT(arrival_date, " ", arrival_time)) AS last_record_time
                                 FROM check_in_records
                                 WHERE arrival_date = "' . $currentDate . '"
                                 GROUP BY id_employee) as last_records'), 'e.id', '=', 'last_records.id_employee')
                ->join('check_in_records as cr', function($join) {
                    $join->on('cr.id_employee', '=', 'last_records.id_employee')
                         ->on(DB::raw('CONCAT(cr.arrival_date, " ", cr.arrival_time)'), '=', 'last_records.last_record_time');
                })
                ->where('cr.type', 'hora de comida')  // Filtrar solo por 'hora de comida'
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('check_in_records as cr2')
                          ->whereRaw('cr2.id_employee = cr.id_employee')
                          ->where('cr2.arrival_date', DB::raw('cr.arrival_date'))
                          ->where('cr2.type', 'fin de comida');  // Asegurarse de que no haya "fin de comida"
                })
                ->select('e.id as employee_id', 'e.name as employee_name', 'e.email as employee_email', 'cr.arrival_date', 'cr.arrival_time as meal_time')
                ->get();

            if ($records->isEmpty()) {
                echo 'No hay empleados que no hayan marcado "fin de hora de comida" para la fecha actual.' . PHP_EOL;
                return;
            }

            foreach ($records as $lastRecord) {
                // Crear objeto Carbon con la fecha y hora
                $lastArrivalTime = Carbon::parse($lastRecord->arrival_date . ' ' . $lastRecord->meal_time);
                echo 'Última hora de comida: ' . $lastArrivalTime . PHP_EOL;

                $timeToAdd = 40; // Por defecto 40 minutos

                // Verificar si es el empleado con ID 3
                if($lastRecord->employee_id == 3){
                    $timeToAdd = 120; // Para el empleado 3, 120 minutos
                }

                $lastMealTimePlus = $lastArrivalTime->copy()->addMinutes($timeToAdd);
                // Verificar si la hora actual es mayor que la hora de comida + 40 minutos
                if ($currentTime > $lastMealTimePlus) {
                    echo 'Han pasado más de 120 minutos desde la última hora de comida.' . PHP_EOL;

                    // Crear una instancia de Employee con datos de stdClass
                    $employee = new Employee([
                        'id' => $lastRecord->employee_id,
                        'name' => $lastRecord->employee_name,
                        'email' => $lastRecord->employee_email,
                    ]);

                    // Enviar advertencia al correo del empleado
                    $this->sendWarning($employee, $currentTime->diffInMinutes($lastArrivalTime));
                } else {
                    echo 'Aún no han pasado 120 minutos desde la última hora de comida.' . PHP_EOL;
                }
            }
        } catch (\Exception $e) {
            echo 'Error en el Job: ' . $e->getMessage() . PHP_EOL;
        }
    }

    private function sendWarning($employee, $minutesPassed)
    {
        Log::info('Enviando correo a ' . $employee->email . ' - Minutos pasados: ' . $minutesPassed);
        Mail::to($employee->email)->send(new EmailCheckWarn($minutesPassed, $employee));
    }
}
