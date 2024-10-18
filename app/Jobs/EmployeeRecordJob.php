<?php

namespace App\Jobs;

use App\Mail\AdminEntryNotification;
use App\Mail\EmailCheckWarn;
use App\Models\CheckInRecords;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
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

            // Obtener registros de "hora de comida" para el día actual (últimos registros por empleado)
            $records = CheckInRecords::where('check_in_records.arrival_date', $currentDate)
                ->where('check_in_records.type', 'hora de comida')
                ->whereIn('check_in_records.id_employee', function ($query) use ($currentDate) {
                    // Subconsulta para obtener el último registro de cada empleado
                    $query->select(DB::raw('MAX(id_employee)'))
                          ->from('check_in_records')
                          ->where('arrival_date', $currentDate)
                          ->groupBy('id_employee');
                })
                ->get();

            if ($records->isEmpty()) {
                echo 'No hay registros de "hora de comida" para la fecha actual.' . PHP_EOL;
                return;
            }

            foreach ($records as $lastRecord) {
                // Crear objeto Carbon con la fecha y hora
                $lastArrivalTime = Carbon::parse($lastRecord->arrival_date . ' ' . $lastRecord->arrival_time);
                echo 'Última hora de comida: ' . $lastArrivalTime . PHP_EOL;

                // Agregar 40 minutos a la última hora de comida
                $lastMealTimePlus40 = $lastArrivalTime->copy()->addMinutes(40); // Usar copy() para no modificar la original

                // Verificar si la hora actual es mayor que la hora de comida + 40 minutos
                if ($currentTime > $lastMealTimePlus40) {
                    echo 'Han pasado más de 40 minutos desde la última hora de comida.' . PHP_EOL;

                    // Obtener el empleado asociado al registro
                    $employee = $lastRecord->employee;

                    if ($employee) {
                        // Enviar advertencia al correo del empleado
                        $this->sendWarning($employee, $lastRecord, $currentTime->diffInMinutes($lastArrivalTime));
                    }
                } else {
                    echo 'Aún no han pasado 40 minutos desde la última hora de comida.' . PHP_EOL;
                }
            }
        } catch (\Exception $e) {
            echo 'Error en el Job: ' . $e->getMessage() . PHP_EOL;
        }
    }

    private function sendWarning($employee, $record, $minutesPassed)
    {
        echo 'Enviando correo a ' . $employee->email . ' - Minutos pasados: ' . $minutesPassed . PHP_EOL;
        Mail::to($employee->email)->send(new EmailCheckWarn($minutesPassed, $employee));
    }
}
