<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('type', ['uniforme', 'mtto', 'libro', 'otro'])->default('otro')->after('product_status');

            // Agregar soft delete
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            // Eliminar columna 'type'
            $table->dropColumn('type');

            // Eliminar soft delete
            $table->dropSoftDeletes();
        });
    }
};
