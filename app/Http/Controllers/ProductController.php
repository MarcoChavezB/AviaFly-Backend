<?php

namespace App\Http\Controllers;

use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                'products.is_active',
                'products.created_at',
                'products.updated_at'
            )
            ->where('products.name', 'like', '%'.$name.'%')
            ->groupBy('products.id', 'products.name', 'products.price', 'products.stock', 'products.is_active', 'products.created_at', 'products.updated_at')
            ->get();

        return response()->json($products, 200);
    }
}
