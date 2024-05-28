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
        Schema::create('flight_history', function(Blueprint $table) {
            $table->id();
            $table->decimal('hours', 8, 2);
            $table->enum('type_flight', ['simulador', 'monomotor', 'multimotor']);
            $table->enum('status', ['process', 'canceled', 'done'])->default('process');
            $table->date('flight_date');
            $table->string('flight_hour');
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
        Schema::dropIfExists('flight_history');
    }
};
