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
        Schema::table('info_flights', function (Blueprint $table) {
            $table->string('type')->nullable(); // Agrega el campo 'type'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('info_flights', function (Blueprint $table) {
            $table->dropColumn('type'); // Elimina el campo 'type' en caso de revertir la migración
        });
    }
};
