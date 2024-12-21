<?php

namespace App\Http\Controllers;

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
                'products.type',
                DB::raw('COALESCE(SUM(order_details.quantity), 0) as quantity_sales'),
                'products.name',
                'products.price',
                'products.stock',
                'products.product_status',
                'products.created_at',
                'products.updated_at'
            )
            ->where('products.name', 'like', '%'.$name.'%')
            ->groupBy('products.id', 'products.type' ,'products.name', 'products.price', 'products.stock', 'products.product_status', 'products.created_at', 'products.updated_at')
            ->orderBy('products.created_at', 'asc');

        if($name != null){
            $products->where('products.product_status', 'activo');
        }

        $products = $products->get();

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

        if($data['product_status'] == 'activo' && ($data['stock'] == 0 || $data['price'] == 0)){
            return response()->json(["errors" => "El stock y el precio deben ser mayores a 0"], 400);
        }

        $product = Product::create($data);
        $product->save();

        return response()->json(["msg" => "Producto creado correctamente"], 201);
    }

    /*
 *      payload:{
 *      "name": "Product 1",
 *      "price": 100.00,
 *      "stock": 10,
 *      "product_status": "activo"
 *      }
     * */

    function update(Request $request,$id_product){
        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'product_status' => 'required|in:activo,inactivo'
        ], [
            'name.required' => 'Campo requerido',
            'name.string' => 'El campo debe ser de tipo texto',
            'name.max' => 'El campo debe tener un máximo de 255 caracteres',
            'price.required' => 'Campo requerido',
            'price.numeric' => 'El campo debe ser de tipo numérico',
            'stock.required' => 'Campo requerido',
            'stock.integer' => 'El campo debe ser de tipo entero',
            'product_status.required' => 'Campo requerido',
            'product_status.in' => 'El campo debe ser activo o inactivo'
        ]);


        if($data['product_status'] == 'activo' && ($data['stock'] == 0 || $data['price'] == 0)){
            return response()->json(["errors" => "El stock y el precio deben ser mayores a 0"], 400);
        }

        if($validator->fails()){
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $product = Product::find($id_product);

        $product->name = $data['name'] ?? $product->name;
        $product->price = $data['price'] ?? $product->price;
        $product->stock = $data['stock'] ?? $product->stock;
        $product->product_status = $data['product_status'] ?? $product->product_status;

        $product->save();
        return response()->json(["msg" => "Producto actualizado correctamente"], 200);
    }


    /*
 *      Return
 *      export interface Product{
        id: number
        quantity_sales: number;
        name: string;
        price: string;
        stock: number;
        product_status: string
        created_at: string;
        updated_at: string;
}
 * */
    function filters(Request $request){
     $products = DB::table('products')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.id_product')
            ->select(
                'products.id',
                DB::raw('COALESCE(SUM(order_details.quantity), 0) as quantity_sales'),
                'products.name',
                'products.price',
                'products.stock',
                'products.product_status',
                'products.created_at',
                'products.updated_at'
            );

        // Filtro por estado del producto
        if ($request->filled('status')) {
            $products->where('products.product_status', $request->status);
        }

        // Filtro por ventas
        if ($request->filled('ventas')) {
            if ($request->ventas == 'mayor') {
                $products->orderBy('quantity_sales', 'desc');
            } elseif ($request->ventas == 'menor') {
                $products->orderBy('quantity_sales', 'asc');
            }
        }

        // Filtro de nombre del producto
        if($request->filled('nombre')){
            $products->where('products.name', 'like', '%'.$request->nombre.'%');
        }

        // Agrupación y obtención de resultados
        $productsFilter = $products
            ->groupBy(
                'products.id',
                'products.name',
                'products.price',
                'products.stock',
                'products.product_status',
                'products.created_at',
                'products.updated_at'
            )
            ->get();

        return response()->json($productsFilter, 200);

    }
}
