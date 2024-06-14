<?php

namespace App\Http\Controllers;

use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(string $name = null)
    {
        $products = DB::table('products')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.id_product')
            ->select(
                'products.id',
                DB::raw('COALESCE(COUNT(order_details.id_product), 0) as quantity_sales'),
                'products.name',
                'products.price',
                'products.stock',
                'products.product_status',
                'products.created_at',
                'products.updated_at'
            )
            ->where('products.name', 'like', '%'.$name.'%')
            ->groupBy('products.id', 'products.name', 'products.price', 'products.stock', 'products.product_status', 'products.created_at', 'products.updated_at')
            ->get();

        return response()->json($products, 200);
    }
    
    /**
        payload:{
            "name": "Product 1",
            "price": 100.00,
            "stock": 10,
            "product_status": "activo"
        }
    */
    function store(Request $request){
        $data = $request->all();
        
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255|unique:products',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'product_status' => 'required|in:activo,inactivo'
        ], [
            'name.required' => 'Campo requerido',
            'name.string' => 'El campo debe ser de tipo texto',
            'name.max' => 'El campo debe tener un máximo de 255 caracteres',
            'name.unique' => 'El producto ya existe',
            'price.required' => 'Campo requerido',
            'price.numeric' => 'El campo debe ser de tipo numérico',
            'stock.required' => 'Campo requerido',
            'stock.integer' => 'El campo debe ser de tipo entero',
            'product_status.required' => 'Campo requerido',
            'product_status.in' => 'El campo debe ser activo o inactivo'
        ]);
        
        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }
        
        $product = Product::create($data);
        $product->save();
        
        return response()->json(["msg" => "Producto creado correctamente"], 201);
    }    
}
