<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessStudentCreation
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $student;
    protected $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($student, $request)
    {
        $this->student = $student;
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $career = DB::table('careers')->where('id', $this->request['career'])->first();

        $startDate = Carbon::parse($this->request['register_date']);


        DB::table('monthly_payments')->insert([
            'id_student' => $this->student->id,
            'payment_date' => $this->student->created_at,
            'amount' => $career->registration_fee,
            'concept' => 'Inscripción'.'_'.$career->name,
            'status' => 'pending',
        ]);

        for ($i = 0; $i < $career->monthly_payments; $i++) {
            $paymentDate = clone $startDate;
            $paymentDate->addMonths($i + 1);

            DB::table('monthly_payments')->insert([
                'id_student' => $this->student->id,
                'payment_date' => $paymentDate,
                'amount' => $career->monthly_fee,
                'concept' => 'Mensualidad'.' '.$career->name.' '.$paymentDate,
                'status' => 'pending',
            ]);
        }

        $careerSubjects = DB::table('career_subjects')
            ->where('id_career', $this->request['career'])
            ->get();

        if ($careerSubjects->isEmpty()) {
            return;
        }

        foreach ($careerSubjects as $careerSubject) {
            $teacher = DB::table('teacher_subject_turns')
                ->where('career_subject_id', $careerSubject->id)
                ->where('id_turn', $this->request['turn'])
                ->join('employees', 'teacher_subject_turns.id_teacher', '=', 'employees.id')
                ->where('employees.id_base', $this->student->id_base)
                ->first();

            if ($teacher) {
                DB::table('student_subjects')->insert([
                    'id_student' => $this->student->id,
                    'id_subject' => $careerSubject->id_subject,
                    'id_turn' => $this->request['turn'],
                    'id_teacher' => $teacher->id_teacher,
                ]);
            } else {
                return;
            }
        }

        DB::statement('CALL AddUserFiles(?)', [$this->student->id]);
        if($career->name == 'Piloto privado'){
            DB::statement('CALL flight_information_data(?)', [$this->student->id]);
        }

    }
}
