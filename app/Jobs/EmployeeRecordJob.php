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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmployeeRecordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        try {
            // Obtener la fecha actual
            $currentDate = Carbon::now()->format('Y-m-d');
            $currentTime = Carbon::now();
            Log::info('Fecha actual: ' . $currentDate);
            Log::info('Hora actual: ' . $currentTime->toTimeString());

            // Obtener registros de "hora de comida" para el día actual
            $records = CheckInRecords::select('check_in_records.*')
                ->join(DB::raw('(SELECT id_employee, MAX(arrival_time) AS last_time
                                 FROM check_in_records
                                 WHERE arrival_date = "' . $currentDate . '"
                                 GROUP BY id_employee) AS last_records'), function($join) {
                    $join->on('check_in_records.id_employee', '=', 'last_records.id_employee')
                         ->on('check_in_records.arrival_time', '=', 'last_records.last_time');
                })
                ->where('check_in_records.type', 'hora de comida')
                ->where('check_in_records.arrival_date', $currentDate)
                ->get();

            if ($records->isEmpty()) {
                Log::info('No hay registros de "hora de comida" para la fecha actual.');
                return;
            }

            foreach ($records as $lastRecord) {
                // Crear objeto Carbon con la fecha y hora
                $lastArrivalTime = Carbon::createFromFormat('Y-m-d H:i:s', $lastRecord->arrival_date . ' ' . $lastRecord->arrival_time);
                Log::info('Última hora de comida: ' . $lastArrivalTime);

                // Si la hora actual es menor que la última hora de comida, no hacemos nada
                if ($currentTime < $lastArrivalTime) {
                    Log::info('La hora actual es menor que la última hora de comida, no se envía correo.');
                    continue; // Saltar al siguiente registro
                }

                $minutesPassed = $currentTime->diffInMinutes($lastArrivalTime);
                Log::info('Minutos transcurridos desde la última hora de comida: ' . $minutesPassed);

                // Verificar si han pasado más de 40 minutos
                if ($minutesPassed >= 40) {
                    // Obtener el empleado asociado al registro
                    $employee = $lastRecord->employee;

                    if ($employee) {
                        // Enviar advertencia al correo del empleado
                        $this->sendWarning($employee, $lastRecord, $minutesPassed);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en el Job: ' . $e->getMessage());
        }
    }

    private function sendWarning($employee, $record, $minutesPassed)
    {
        Log::info('Enviando correo a ' . $employee->email . ' - Minutos pasados: ' . $minutesPassed);
        Mail::to($employee->email)->send(new EmailCheckWarn($minutesPassed, $employee));
    }
}

