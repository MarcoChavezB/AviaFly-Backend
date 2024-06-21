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
        Schema::create('flight_payments', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('id_student')->nullable();
                $table->foreign('id_student')->references('id')->on('students')->nullable();

                $table->unsignedBigInteger('id_flight');
                $table->foreign('id_flight')->references('id')->on('flight_history');

                $table->unsignedBigInteger('id_instructor');
                $table->foreign('id_instructor')->references('id')->on('employees');

                $table->unsignedBigInteger('id_employee');
                $table->foreign('id_employee')->references('id')->on('employees');

                $table->decimal('total', 8, 2);
                $table->enum('payment_status', ['pendiente', 'pagado', 'cancelado']);
                $table->decimal('hour_instructor_cost', 8, 2)->nullable();
                $table->integer('due_week')->nullable();
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
        Schema::dropIfExists('flight_payments');
    }
};
