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
        Schema::table('flight_hours_restrictions', function (Blueprint $table) {
            /* // Eliminar columna 'start_date' si existe
            if (Schema::hasColumn('flight_hours_restrictions', 'start_date')) {
                $table->dropColumn('start_date');
            }

            // Agregar nuevas columnas como claves foráneas
            $table->unsignedBigInteger('start_day');
            $table->foreign('start_day')->references('id')->on('days')->onDelete('cascade');

            $table->unsignedBigInteger('end_day')->nullable();
            $table->foreign('end_day')->references('id')->on('days');
*/
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('flight_hours_restrictions', function (Blueprint $table) {
            // Eliminar las claves foráneas antes de eliminar las columnas
            $table->dropForeign(['start_day']);
            $table->dropForeign(['end_day']);

            // Eliminar las columnas 'start_day' y 'end_day'
            $table->dropColumn(['start_day', 'end_day']);

            // Volver a agregar la columna 'start_date' si fuera necesario
            $table->date('start_date')->nullable();
        });
    }
};
