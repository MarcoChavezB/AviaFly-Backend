<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\UserController;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductPayment;
use App\Models\Student;

class OrderController extends Controller
{

    private $userController;
    private $payment_method_controller;


    public function __construct(UserController $userController, PaymentMethodController $payment_method_controller)
    {
        $this->userController = $userController;
        $this->payment_method_controller = $payment_method_controller;
    }

    /**
     * Display a listing of the resource.
     *
     * @return
     *  [
     *      {
     *          "id_order": 1,
     *          "employee": "Juan",
     *          "client": "Juan",
     *          "order_date": "2021-09-01",
     *          "order_status": "pendiente",
     *          "payment_method": "credito",
     *          "total": 10,
     *          "installments": [
     *              {
     *                  "id_installment": 1,
     *                  "installment_date": "2021-09-01",
     *                  "installment_value": 2,
     *              },
     *              {
     *                  "id_installment": 1,
     *                  "installment_date": "2021-09-01",
     *                  "installment_value": 2,
     *              }
     *          ],
     *          "products": [
     *              {
     *                  "id_product": 1,
     *                  "product_name": "Producto 1",
     *                  "quantity": 1,
     *                  "price": 100
     *              },
     *              {
     *                  "id_product": 1,
     *                  "product_name": "Producto 1",
     *                  "quantity": 1,
     *              }
     *          ]
     *      }
     *  ]
     */
    public function index($id_student = null)
    {
        $orders = Order::with(['employee', 'client', 'products', 'productPayments.paymentMethod'])
                        ->when($id_student, function ($query, $id_student) {
                            return $query->where('id_client', $id_student);
                        })
                       ->orderBy('created_at', 'desc')
                       ->get();

        $result = $orders->map(function ($order) {
            $sortedProductPayments = $order->productPayments->sortByDesc('created_at');

            $installments = $sortedProductPayments->map(function ($payment) {
                return [
                    'id_installment' => $payment->id,
                    'payment_method' => $payment->paymentMethod->type,
                    'installment_date' => $payment->created_at->toDateString(),
                    'installment_value' => $payment->amount,
                ];
            });

            // Calcular el total de installments
            $totalInstallments = $installments->sum(function ($installment) {
                return $installment['installment_value'];
            });

            return [
                'id_order' => $order->id,
                'employee' => $order->employee->name,
                'client' => $order->client->name ?? null,
                'order_date' => $order->order_date,
                'order_status' => $order->payment_status,
                'total' => $order->total,
                'payment_method' => $order->paymentMethod->type,
                'total_installments' => $totalInstallments,
                'restant_debt' => $order->total - $totalInstallments,
                'installments' => $installments->values(),
                'products' => $order->products->map(function ($product) {
                    return [
                        'id_product' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $product->pivot->quantity,
                        'price' => $product->price,
                    ];
                }),
            ];
        });

        return response()->json($result);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @Tables products, students, discounts, payment_methods
     * @Tables orders, order_details
     * @return \Illuminate\Http\Response
     * @Payload
        {
            "id_student": 0,
            "id_discount": null,
            "id_payment_method": 7,
            "total_price": 10,
            "sub_total": 8.4,
            "amountment_week": 5,
            "amountment": 2,
            "products": [
                {
                    "product_id": 17,
                    "quantity": 1,
                    "total_price": "10.00",
                    "iva": 1.6,
                    "subtotal": 8.4
                }
            ]
        }
    */
    public function store(Request $request)
    {
        $clientData = Auth::user();
        $id_client = $this->userController->getIdEmploye($clientData->user_identification);

        $data = $request->all();
        $validator = Validator::make($data, [
            'id_discount' => 'nullable|integer|exists:discounts,id',
            'id_payment_method' => 'required|integer|exists:payment_methods,id',
            'total_price' => 'required',
            'sub_total' => 'required',
            'amountment_week' => 'integer',
            'products' => 'required|array',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.quantity' => 'required|integer',
            'products.*.total_price' => 'required|numeric',
            'products.*.iva' => 'required|numeric',
            'products.*.subtotal' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        // Creacion de order

        $order = Order::create([
            'order_date' => now(),
            'total' => $data['total_price'],
            'sub_total' => $data['sub_total'],
            'due_week' => $data['amountment_week'] ?? 0,
            'installment_value' => $data['amountment'] ?? 0,
            'id_employe' => $id_client,
            'id_client' => $data['id_student'] == 0 ? null : $data['id_student'],
            'id_discount' => $data['id_discount'] == null ? null : $data['id_discount'],
            'id_payment_method' => $data['id_payment_method'],
            'payment_status' => $data['id_payment_method'] != 1 ? 'pendiente' : 'pagado'
        ]);


       // descontar el credito del alumno si el metodo
       // de pago es credito
        if($data['id_student'] != 0 && $data['id_payment_method'] == 3){
            $student = Student::find($data['id_student']);
            if($student->credit < $data['total_price']){
                return response()->json(['message' => 'El estudiante no tiene suficiente credito'], 400);
            }
            $student->credit = $student->credit - $data['total_price'];
            $student->save();
        }

        // creacion del product_payments

        $product_payment = new ProductPayment();

        if($data['id_payment_method'] != $this->payment_method_controller->getAbonosId()){
            $product_payment->amount = $data['total_price'];
        }
        $product_payment->amount = $data['amountment'];
        $product_payment->id_order = $order->id;
        $product_payment->id_payment_method = $data['id_payment_method'];
        $product_payment->save();

        // Creacion de order_details
        $id_order = $order->id;

        $order_details = new OrderDetail();
        foreach ($data['products'] as $product){
            $order_details->create([
                'quantity' => $product['quantity'],
                'id_order' => $id_order,
                'id_product' => $product['product_id']
            ]);
        }

        // eliminacio de stock de productos comprados
        foreach ($data['products'] as $product){
            $productFind = Product::find($product['product_id']);
            if($productFind->stock < $product['quantity']){
                return response()->json(['message' => 'No hay suficiente stock'], 400);
            }
            $productFind->stock = $productFind->stock - $product['quantity'];
            $productFind->save();
        }


        return response()->json(['message' => 'Orden creada con exito'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     *
     * @Request {
     *      "id_order": 1,
     *      "payment_status": "pagado"
     * }
     */
    public function edit(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'id_order' => 'required|integer|exists:orders,id',
            'payment_status' => 'required|string|in:pagado,pendiente,cancelado',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $order = Order::find($data['id_order']);
        $order->payment_status = $data['payment_status'];
        $order->save();

        return response()->json(['message' => 'Orden actualizada con exito'], 200);
    }

    /*
    * Request:
    * {
    *   id_order: number (orders table)
    *   installment: number
    *   id_payment_method: enum('Efectivo', 'Transferencia', 'Tarjeta CLIP', 'Credito', 'Inbursa CREDITO', 'Inbursa DEBITO', 'Abonos')
    * }
    * */

    public function storeInstallment(Request $request){
        $data = $request->all();

        // Validación de los datos recibidos
        $validator = Validator::make($data, [
            'id_order' => 'required|integer|exists:orders,id',
            'installment' => 'required|numeric',
            'id_payment_method' => 'required|integer|exists:payment_methods,id',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        // Obtener la orden y los pagos relacionados
        $order = Order::find($data['id_order']);
        $orderPayments = ProductPayment::where('id_order', $data['id_order'])->get();

        // Verificar si el tipo de pago de la orden admite abonos
        if($order->id_payment_method != $this->payment_method_controller->getAbonosId()){
            return response()->json(['message' => 'El tipo de pago de la orden no admite abonos'], 400);
        }

        // Calcular el total de los pagos realizados
        $totalPayments = $orderPayments->sum('amount');

        // Verificar si la orden ya está pagada
        if($order->payment_status == 'pagado'){
            return response()->json(['message' => 'La orden ya está pagada'], 400);
        }

        // Verificar si el abono excede el total de la orden
        if($totalPayments + $data['installment'] > $order->total){
            return response()->json(['message' => 'El abono excede el total de la orden'], 400);
        }

        // Verificar el crédito del estudiante si aplica
        if($order->id_client && $data['id_payment_method'] == $this->payment_method_controller->getCreditId()){
            $student = Student::find($order->id_client);
            if($data['installment'] > $student->credit){
                return response()->json(['message' => 'El abono excede el crédito del estudiante'], 400);
            }
            // Actualizar el crédito del estudiante
            $student->credit -= $data['installment'];
            $student->save();
        }

        // Crear el nuevo pago
        $product_payment = new ProductPayment();
        $product_payment->create([
            'amount' => $data['installment'],
            'id_order' => $data['id_order'],
            'id_payment_method' => $data['id_payment_method']
        ]);

        // Actualizar el estado de la orden si está completamente pagada
        if($totalPayments + $data['installment'] == $order->total){
            $order->payment_status = 'pagado';
            $order->save();
        }

        return response()->json(['message' => 'Abono registrado con éxito'], 201);
    }
}
