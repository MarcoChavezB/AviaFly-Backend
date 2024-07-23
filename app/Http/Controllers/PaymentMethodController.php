<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{

    public $efectivo = 1;
    public $transferencia = 2;
    public $credit = 3;
    public $tarjeta_clip = 4;
    public $inbursa_credito = 5;
    public $inbursa_debito = 6;
    public $abonos = 7;
    public $credito_vuelo = 8;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $paymentMethods = PaymentMethod::all();
        return response()->json($paymentMethods);
    }


    public function getEfectivoId(){
        return $this->efectivo;
    }

    public function getTransferenciaId(){
        return $this->transferencia;
    }

    public function getCreditId(){
        return $this->credit;
    }

    public function getTarjetaClipId(){
        return $this->tarjeta_clip;
    }

    public function getInbursaCreditoId(){
        return $this->inbursa_credito;
    }

    public function getInbursaDebitoId(){
        return $this->inbursa_debito;
    }

    public function getAbonosId(){
        return $this->abonos;
    }

    public function getCreditoVueloId(){
        return $this->credito_vuelo;
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
     * @param  \App\Models\PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentMethod $paymentMethod)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\Response
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        //
    }
}
