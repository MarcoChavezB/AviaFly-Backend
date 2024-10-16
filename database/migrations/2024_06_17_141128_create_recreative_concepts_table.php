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
        Schema::create('recreative_concepts', function (Blueprint $table) {
            $table->id();
            $table->string("concept");
            $table->decimal("price", 10, 2)->default(0);
            $table->decimal("default_hours", 10, 2)->default(0);
            $table->string("description")->nullable();
            $table->decimal("max_weight", 10, 2)->nullable();
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
        Schema::dropIfExists('recreative_concepts');
    }
};
