<?php

namespace App\Http\Controllers;

use App\Models\Pending;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PendingController extends Controller
{
    function index(int $id)
    {
        $userBaseId = User::find($id)->id_base;

        $personalPendings = Pending::where('id_created_by', $id)
            ->where('id_assigned_to', $id)
            ->whereHas('createdBy', function ($query) use ($userBaseId) {
                $query->where('id_base', $userBaseId);
            })
            ->where('status', FALSE)
            ->get();
        $pendingsCount = $personalPendings->count();


        $pendingsToday = Pending::where('date_to_complete', date('Y-m-d'))
            ->where('id_assigned_to', $id)
            ->where('status', FALSE)
            ->whereHas('createdBy', function ($query) use ($userBaseId) {
                $query->where('id_base', $userBaseId);
            })
            ->get();
        $pendingsTodayCount = $pendingsToday->count();

        $pendingToWeek = Pending::where('date_to_complete', '>=', date('Y-m-d', strtotime('+1 days')))
            ->where('date_to_complete', '<=', date('Y-m-d', strtotime('+7 days')))
            ->where('id_assigned_to', $id)
            ->where('status', FALSE)
            ->whereHas('createdBy', function ($query) use ($userBaseId) {
                $query->where('id_base', $userBaseId);
            })
            ->get();

        $pendingsToWeekCount = $pendingToWeek->count();

        $pendingsUrgent = Pending::where('is_urgent', 1)
            ->where('status', FALSE)
            ->whereHas('createdBy', function ($query) use ($userBaseId) {
                $query->where('id_base', $userBaseId);
            })
            ->get();
        $pendingsUrgentCount = $pendingsUrgent->count();

        return response()->json([
            'personalPendings' => [
                'pendings' => $personalPendings,
                'count' => $pendingsCount
            ],
            'pendingsToday' => [
                'pendings' => $pendingsToday,
                'count' => $pendingsTodayCount
            ],
            'pendingToWeek' => [
                'pendings' => $pendingToWeek,
                'count' => $pendingsToWeekCount
            ],
            'pendingsUrgent' => [
                'pendings' => $pendingsUrgent,
                'count' => $pendingsUrgentCount
            ],
        ]);
    }

    function create(Request $request)
    {
        $user = User::find('id', $request->user()->id);

        if ($user->id_base != $request->id_base) {
            return response()->json([
                "msg" => "No puedes crear una tarea para otra base"
            ], 401);
        }
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|boolean',
            'date_to_complete' => 'required|date_format:Y-m-d',
            'is_urgent' => 'required|boolean',
            'id_created_by' => 'required|exists:users,id',
            'id_assigned_to' => 'required|exists:users,id',
        ], [
            'title.required' => 'El título es obligatorio.',
            'title.string' => 'El título debe ser una cadena de caracteres.',
            'title.max' => 'El título no debe tener más de 255 caracteres.',
            'description.required' => 'La descripción es obligatoria.',
            'description.string' => 'La descripción debe ser una cadena de caracteres.',
            'status.required' => 'El estado es obligatorio.',
            'status.boolean' => 'El estado debe ser verdadero o falso.',
            'date_to_complete.required' => 'La fecha de completación es obligatoria.',
            'date_to_complete.date_format' => 'La fecha de completación debe tener el formato YYYY-MM-DD.',
            'is_urgent.required' => 'La urgencia es obligatoria.',
            'is_urgent.boolean' => 'La urgencia debe ser verdadera o falsa.',
            'id_created_by.required' => 'El ID del creador es obligatorio.',
            'id_created_by.exists' => 'El ID del creador no existe en la tabla de usuarios.',
            'id_assigned_to.required' => 'El ID del asignado es obligatorio.',
            'id_assigned_to.exists' => 'El ID del asignado no existe en la tabla de usuarios.',
        ]);
        
        if($validator->fails()){
            return response()->json([
                "errors"  => $validator->errors()
            ], 400);
        }
        
        $pending = new Pending();
        $pending->title = $request->title;
        $pending->description = $request->description;
        $pending->status = $request->status;
        $pending->date_to_complete = $request->date_to_complete;
        $pending->is_urgent = $request->is_urgent;
        $pending->id_created_by = $request->user()->id;
        $pending->id_assigned_to = $request->id_assigned_to;
        $pending->save();
        
        return response()->json([
            "msg" => "Se creo la tarea correctamente"
        ], 201);
    }
}
