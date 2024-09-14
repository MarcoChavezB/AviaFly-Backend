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
        Schema::create('air_planes', function (Blueprint $table) {
            $table->id();
            $table->string('model');
            $table->integer('limit_hours');
            $table->string('limit_weight');
            $table->string('limit_passengers');
            $table->decimal('tacometer', 8, 2)->default(0);
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
        Schema::dropIfExists('air_planes');
    }
};
