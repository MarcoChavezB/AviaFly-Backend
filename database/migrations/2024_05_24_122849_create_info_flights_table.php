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
        Schema::create('info_flights', function (Blueprint $table) {
            $table->id();
            $table->enum('flight_type', ['simulador', 'monomotor', 'multimotor'])->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('min_credit_hours_required', 10, 2)->nullable();
            $table->decimal('min_hours_required', 10, 2)->nullable();
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
        Schema::dropIfExists('info_flights');
    }
};
