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
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->integer('flight_hours');
            $table->date('reservation_date');
            $table->string('reservation_hour');

            $table->decimal('weight', 10, 2);
            $table->integer('number_of_passengers');

            $table->enum('payment_status', ['pendiente', 'pagado', 'cancelado'])->default('pendiente');
            $table->enum('flight_status', ['pendiente', 'realizado', 'cancelado'])->default('pendiente');
            $table->decimal('total', 10, 2);

            $table->unsignedBigInteger('id_employee');
            $table->foreign('id_employee')->references('id')->on('employees');

            $table->unsignedBigInteger('id_flight');
            $table->foreign('id_flight')->references('id')->on('info_flights');

            $table->unsignedBigInteger('id_payment_method');
            $table->foreign('id_payment_method')->references('id')->on('payment_methods');

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
