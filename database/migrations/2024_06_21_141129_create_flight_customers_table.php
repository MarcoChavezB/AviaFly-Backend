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

            $table->string('first_passenger_name');
            $table->string('second_passenger_name')->nullable();
            $table->string('tird_passenger_name')->nullable();

            $table->string('first_passenger_age');
            $table->string('second_passenger_age')->nullable();
            $table->string('tird_passenger_age')->nullable();

            $table->decimal('first_passenger_weight', 10, 2);
            $table->decimal('second_passenger_weight', 10, 2)->nullable();
            $table->decimal('tird_passenger_weight', 10, 2)->nullable();
            $table->decimal('pilot_weight', 10, 2)->default(100);

            $table->integer('flight_hours');
            $table->date('reservation_date');
            $table->string('reservation_hour');
            $table->decimal('total_weight', 10, 2);
            $table->integer('number_of_passengers');

            $table->enum('payment_status', ['pendiente', 'pagado', 'cancelado'])->default('pendiente');
            $table->enum('flight_status', ['pendiente', 'realizado', 'cancelado'])->default('pendiente');
            $table->decimal('total', 10, 2);

            $table->string('flight_type')->default('recreativo');

            $table->unsignedBigInteger('id_employee');
            $table->foreign('id_employee')->references('id')->on('employees')->onDelete('cascade');

            $table->unsignedBigInteger('id_air_planes')->nullable();
            $table->foreign('id_air_planes')->references('id')->on('air_planes')->onDelete('cascade');

            $table->unsignedBigInteger('id_payment_method');
            $table->foreign('id_payment_method')->references('id')->on('payment_methods')->onDelete('cascade');

            $table->unsignedBigInteger('id_pilot');
            $table->foreign('id_pilot')->references('id')->on('employees')->onDelete('cascade');


            $table->unsignedBigInteger('id_concept');
            $table->foreign('id_concept')->references('id')->on('recreative_concepts')->onDelete('cascade');

            $table->timestamps();
            $table->boolean('has_report')->default(false);
            $table->decimal('initial_horometer', 8, 2)->default(0);
            $table->decimal('final_horometer', 8, 2)->default(0);
            $table->decimal('total_horometer', 8, 2)->default(0);
            $table->decimal('final_tacometer', 8, 2)->default(0);
            $table->string('comment')->nullable();
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
