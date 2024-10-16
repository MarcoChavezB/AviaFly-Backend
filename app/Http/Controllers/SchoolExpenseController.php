<?php

namespace App\Http\Controllers;

use App\Models\SchoolExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SchoolExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $expenses = SchoolExpense::with([
            'creator:id,name,last_names',
            'approver:id,name,last_names',

            'paymentMethod'
        ])->get();
        return response()->json($expenses);
    }

    /*
     * payload : {
     *  id_expense: number,
     *  new status: string
     * }
     */
    public function changeStatus(Request $request){

        $validator = Validator::make($request->all(), [
            "id_expense" => "required|numeric|exists:school_expenses,id",
            "status" => "required|string"
        ]);

        if($validator->fails()){
            return response()->json(["msg" => $validator->errors()], 422);
        }

        $expense = SchoolExpense::find($request->id_expense);

        if(!$expense){
            return response()->json(["msg" => "Expense not found"], 404);
        }

        $expense->status = $request->status;
        $expense->approved_by = Auth()->user()->id;
        $expense->save();

        return response()->json(["msg" => "expense change successfully"]);
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
        $data = $request->all();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'motive' => 'nullable|string|max:500',
            'monto' => 'required|numeric|min:1',
            'payment_method' => 'required|exists:payment_methods,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $expense = new SchoolExpense();
        $expense->name = $data['name'];
        $expense->motive = $data['motive'] ?? null;
        $expense->date = $data['date'];
        $expense->amount = $data['monto'];
        $expense->payment_method = $data['payment_method'];
        $expense->created_by = Auth()->user()->id;
        $expense->status = 'pendiente';

        $expense->save();

        return response()->json([
            'message' => 'Egreso creado correctamente',],
        201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SchoolExpense  $schoolExpense
     * @return \Illuminate\Http\Response
     */
    public function show(SchoolExpense $schoolExpense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SchoolExpense  $schoolExpense
     * @return \Illuminate\Http\Response
     */
    public function edit(SchoolExpense $schoolExpense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SchoolExpense  $schoolExpense
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SchoolExpense $schoolExpense)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SchoolExpense  $schoolExpense
     * @return \Illuminate\Http\Response
     */
    public function destroy($id_expense)
    {
        $validator = Validator::make(['id_expense' => $id_expense], [
            'id_expense' => 'required|integer|exists:school_expenses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => $validator->errors()], 422);
        }

        $expense = SchoolExpense::find($id_expense);

        if (!$expense) {
            return response()->json(['msg' => 'Expense not found'], 404);
        }

        $expense->delete();

        return response()->json(['msg' => 'Expense deleted successfully'], 200);
    }
}
