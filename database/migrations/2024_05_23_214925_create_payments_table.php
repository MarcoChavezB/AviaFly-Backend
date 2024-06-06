<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount')->default(0);
            $table->enum('payment_method', ['efectivo','abonos', 'tarnsferencia', 'credito_vuelo']);
            
            $table->unsignedBigInteger('id_flight');
            $table->foreign('id_flight')->references('id')->on('flight_payments');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
