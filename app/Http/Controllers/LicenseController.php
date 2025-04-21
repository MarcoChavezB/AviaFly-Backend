<?php

namespace App\Http\Controllers;

use App\Models\License;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function index(){
        $licenses = License::orderBy('id', 'desc')->get();

        if ($licenses->isEmpty()) {
            return response()->json([
                'message' => 'No hay licencias disponibles',
                'successfully' => false
            ]);
        }

        return response()->json([
            'licenses' => $licenses,
            'message' => 'Licencias obtenidas correctamente',
            'successfully' => true,
            'total' => $licenses->count()
        ]);
    }


    public function destroy($id){
        $license = License::find($id);

        if (!$license) {
            return response()->json([
                'message' => 'Licencia no encontrada',
                'successfully' => false
            ]);
        }

        $license->delete();

        return response()->json([
            'message' => 'Licencia eliminada correctamente',
            'successfully' => true
        ]);
    }

    public function create(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        if(License::where('name', $request->name)->exists()){
            return response()->json([
                'message' => 'Ya existe una licencia con ese nombre',
                'successfully' => false
            ]);
        }

        $license = License::create($request->all());

        return response()->json([
            'license' => $license,
            'message' => 'Licencia creada correctamente',
            'successfully' => true
        ]);
    }
}
