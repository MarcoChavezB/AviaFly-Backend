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
        Schema::table('flight_customers', function (Blueprint $table) {
            $table->unsignedBigInteger('id_concept')->after('id_payment_method'); // Agregar el campo

            $table->foreign('id_concept')
                  ->references('id')
                  ->on('recreative_concepts')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('flight_customers', function (Blueprint $table) {
            //
        });
    }
};
