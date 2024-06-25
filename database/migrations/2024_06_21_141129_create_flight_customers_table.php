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
        Schema::create('flight_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_employee');
            $table->foreign('id_employee')->references('id')->on('employees');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->enum('flight_type', ['simulador']);
            $table->integer('flight_hours');
            $table->date('reservation_date');
            $table->string('reservation_hour');
            $table->enum('payment_status', ['pendiente', 'pagado', 'cancelado'])->default('pendiente');
            $table->enum('payment_method', ['tarjeta', 'efectivo'])->default('tarjeta');
            $table->enum('flight_status', ['pendiente', 'realizado', 'cancelado'])->default('pendiente');
            $table->decimal('total', 10, 2);
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
        Schema::dropIfExists('flight_customers');
    }
};
