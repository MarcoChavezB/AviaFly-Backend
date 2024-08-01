<?php

namespace App\Http\Controllers;

use App\Models\CheckInRecords;
use Illuminate\Http\Request;

class CheckInRecordsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @Return [
     *      {
     *          "id_employee": 1,
     *          "employee_name": "John Doe",
     *          "employee_email": "john doe",
     *          "employee_phone": "123456789",
     *          "arrival_date": "2021-09-01",
     *          "arrival_time": "08:00:00"
     *      },
     *
     * ]
     *
     */
    public function index()
    {
        $checkInRecords = CheckInRecords::with('employee')
            ->orderBy('created_at', 'desc') // Ordena los registros por `created_at` de forma descendente
            ->get();

        $result = $checkInRecords->map(function($item) {
            return [
                "id_employee" => $item->employee->user_identification,
                "employee_name" => $item->employee->name,
                "employee_email" => $item->employee->company_email,
                "employee_phone" => $item->employee->phone,
                "arrival_date" => $item->arrival_date,
                "arrival_time" => $item->arrival_time
            ];
        });

        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CheckInRecords  $checkInRecords
     * @return \Illuminate\Http\Response
     */
    public function show(CheckInRecords $checkInRecords)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CheckInRecords  $checkInRecords
     * @return \Illuminate\Http\Response
     */
    public function edit(CheckInRecords $checkInRecords)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CheckInRecords  $checkInRecords
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CheckInRecords $checkInRecords)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CheckInRecords  $checkInRecords
     * @return \Illuminate\Http\Response
     */
    public function destroy(CheckInRecords $checkInRecords)
    {
        //
    }
}
