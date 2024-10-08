<?php

namespace App\Http\Controllers;

use App\Models\Pending;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PendingController extends Controller
{
    function index(){
        $id = auth()->user()->id;
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
    function create(Request $request){
        $id = auth()->user()->id;
        $user = User::find($id);
        $assignetTo = User::find($request->id_assigned_to);
        
        if ($user->id_base != $assignetTo->id_base) {
            return response()->json([
                "msg" => "No puedes crear una tarea para otra base"
            ], 401);
        }
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date_to_complete' => 'required|date_format:Y-m-d',
            'is_urgent' => 'required|boolean',
            'id_assigned_to' => 'required|exists:users,id',
        ], [
            'title.required' => 'El título es obligatorio.',
            'title.string' => 'El título debe ser una cadena de caracteres.',
            'title.max' => 'El título no debe tener más de 255 caracteres.',
            'description.required' => 'La descripción es obligatoria.',
            'description.string' => 'La descripción debe ser una cadena de caracteres.',
            'date_to_complete.required' => 'La fecha de completación es obligatoria.',
            'date_to_complete.date_format' => 'La fecha de completación debe tener el formato YYYY-MM-DD.',
            'is_urgent.required' => 'La urgencia es obligatoria.',
            'is_urgent.boolean' => 'La urgencia debe ser verdadera o falsa.',
            'id_created_by.exists' => 'El ID del creador no existe en la tabla de usuarios.',
            'id_assigned_to.required' => 'El ID del asignado es obligatorio.',
            'id_assigned_to.exists' => 'El ID del asignado no existe en la tabla de usuarios.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "errors"  => $validator->errors()
            ], 400);
        }

        $pending = new Pending();
        $pending->title = $request->title;
        $pending->description = $request->description;
        $pending->status = $request->status ?? 0;
        $pending->date_to_complete = $request->date_to_complete;
        $pending->is_urgent = $request->is_urgent;
        $pending->id_created_by = $id;
        $pending->id_assigned_to = $request->id_assigned_to;
        $pending->save();

        return response()->json([
            "msg" => "Se creo la tarea correctamente"
        ], 201);
    }
    function destroy(int $id) {
        $pendingToDestroy = Pending::find($id);
        
        // validacion de base
        $assignetTo = User::find($pendingToDestroy->id_assigned_to);
        if(auth()->user()->id != $assignetTo->id_base){
            return response()->json([
                "msg" => "no puedes eliminar esta tarea"
            ]);
        }
        
        // validacion de urgencia
        if((auth()->user()->id != $pendingToDestroy->id_created_by && $pendingToDestroy->id_assigned_to != auth()->user()->id) && $pendingToDestroy->is_urgent != 1){
            return response()->json([
                "msg" => "no puedes eliminar esta tarea"
            ]);
        }
        
        
        if (!$pendingToDestroy) {
            return response()->json([
                "msg" => "no se encontro la tarea"
            ], 404);
        }
        $pendingToDestroy->delete();
        return response()->json([
            "msg" => "se elimino la tarea"
        ], 200);
    }
    function update(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status' => 'sometimes|boolean',
            'date_to_complete' => 'sometimes|date_format:Y-m-d',
            'is_urgent' => 'sometimes|boolean',
            'id_assigned_to' => 'sometimes|integer|exists:users,id',
        ], [
            'title.string' => 'El título debe ser una cadena de caracteres.',
            'title.max' => 'El título no puede tener más de 255 caracteres.',
            'description.string' => 'La descripción debe ser una cadena de caracteres.',
            'status.boolean' => 'El estado debe ser verdadero o falso.',
            'date_to_complete.date_format' => 'El formato de la fecha de completado debe ser Y-m-d.',
            'is_urgent.boolean' => 'La urgencia debe ser verdadera o falsa.',
            'id_assigned_to.integer' => 'El ID del asignado debe ser un número entero.',
            'id_assigned_to.exists' => 'El ID del asignado no existe en la tabla de usuarios.',
        ]);
        
        if($validator->fails()){
            return response()->json([
                "error" => $validator->errors()
            ], 400);
        }
        $pending = Pending::find($request->id);
        
        // editanto una tarea que no es suya
        if(auth()->user()->id != $pending->id_created_by){
            return response()->json([
                "msg" => "no puedes editar esta tarea, no es tuya"
            ]);
        }
        
        // si la tarea no es de su base
        $assignedTo = User::find($pending->id_assigned_to);
        if(auth()->user()->id != $assignedTo->id_base){
            return response()->json([
                "msg" => "no puedes editar esta tarea, no es de tu base"
            ]);
        }
        
        // no se encontro la tarea
        if(!$pending){
            return response()->json([
                "msg" => "No se encontro la tarea",
                "data" => $request->all()
            ], 404);
        }
        
        $pending->title = $request->title ?? $pending->title;
        $pending->description = $request->description ?? $pending->description;
        $pending->status = $request->status ?? $pending->status;
        $pending->date_to_complete = $request->date_to_complete ?? $pending->date_to_complete;
        $pending->is_urgent = $request->is_urgent ?? $pending->is_urgent;
        $pending->id_assigned_to = $request->id_assigned_to ?? $pending->id_assigned_to;
        $pending->save();
        
        if(!$pending->wasChanged()){
            return response()->json([
                "msg" => "No se realizaron cambios"
            ], 304);
        }
        
        return response()->json([
            "msg" => "se actualizo la tarea"
        ], 200);
    }
}


