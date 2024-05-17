<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->get('search', '');

        $contacts = Contact::where('name', 'like', '%' . $search . '%')
            ->orWhere('last_names', 'like', '%' . $search . '%')
            ->orWhere('company', 'like', '%' . $search . '%')
            ->paginate(15);

        if($contacts->isEmpty()){
            return response()->json(['message' => 'No se encontraron contactos'], 404);
        }

        return response()->json([
            'page' => $contacts->currentPage(),
            'hasMorePages' => $contacts->hasMorePages(),
            'contacts' => $contacts->items()
        ], 200);
    }


    public function index2(Request $request)
    {
        $contacts = Contact::all();

        if($contacts->isEmpty()){
            return response()->json(['message' => 'No se encontraron contactos'], 404);
        }

        return response()->json(['contacts' => $contacts], 200);
    }


    public function create(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string',
            'last_names' => 'required|string',
            'email' => 'required|string|email',
            'secondary_email' => 'sometimes|string|email',
            'phone' => 'sometimes|string',
            'cellphone' => 'required|string',
            'company' => 'sometimes|string',
            'giro' => 'sometimes|string',
            'curp' => 'sometimes|string',
            'street' => 'sometimes|string',
            'outside_number' => 'sometimes|string',
            'inside_number' => 'sometimes|string',
            'neighborhood' => 'sometimes|string',
            'municipality' => 'sometimes|string',
            'zip_code' => 'sometimes|string',
            'state' => 'sometimes|string',
            'country' => 'sometimes|string',
            'feedback' => 'sometimes|string',
        ],
        [
            'name.required' => 'El campo nombre es requerido',
            'last_names.required' => 'El campo apellidos es requerido',
            'email.required' => 'El campo email es requerido',
            'email.email' => 'El campo email debe ser un email',
            'cellphone.required' => 'El campo celular es requerido',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $contact = Contact::create($request->all());

        return response()->json($contact, 201);
    }

    public function show(Int $id)
    {
        $contact = Contact::find($id);

        if(!$contact){
            return response()->json(['message' => 'Contacto no encontrado'], 404);
        }

        return response()->json($contact, 200);
    }


    public function update(Request $request, Int $id){

        $validator = Validator::make($request->all(),[
            'name' => 'required|string',
            'last_names' => 'required|string',
            'email' => 'required|string|email',
            'secondary_email' => 'sometimes|string|email',
            'phone' => 'sometimes|string',
            'cellphone' => 'required|string',
            'company' => 'sometimes|string',
            'giro' => 'sometimes|string',
            'curp' => 'sometimes|string',
            'street' => 'sometimes|string',
            'outside_number' => 'sometimes|string',
            'inside_number' => 'sometimes|string',
            'neighborhood' => 'sometimes|string',
            'municipality' => 'sometimes|string',
            'zip_code' => 'sometimes|string',
            'state' => 'sometimes|string',
            'country' => 'sometimes|string',
            'feedback' => 'sometimes|string',
        ],
            [
                'name.required' => 'El campo nombre es requerido',
                'last_names.required' => 'El campo apellidos es requerido',
                'email.required' => 'El campo email es requerido',
                'email.email' => 'El campo email debe ser un email',
                'cellphone.required' => 'El campo celular es requerido',
            ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $contact = Contact::find($id);

        if(!$contact){
            return response()->json(['message' => 'Contacto no encontrado'], 404);
        }

        $contact->update($request->all());

        return response()->json($contact, 200);
    }


    public function destroy(Int $id)
    {
        $contact = Contact::find($id);

        if(!$contact){
            return response()->json(['message' => 'Contacto no encontrado'], 404);
        }

        $contact->delete();

        return response()->json(['message' => 'Contacto eliminado'], 200);
    }
}
