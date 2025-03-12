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
        Schema::table('flight_customers', function (Blueprint $table) {
            //
        });
    }
};
