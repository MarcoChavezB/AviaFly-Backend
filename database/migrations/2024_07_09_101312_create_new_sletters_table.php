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
        Schema::create('new_sletters', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('content', 1999);
            $table->string('file')->nullable();

            $table->enum('direct_to', ['todos', 'empleados', 'instructores', 'estudiantes', 'flight_instructor']);
            $table->date('start_at');
            $table->date('expired_at');

            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('employees');

            $table->unsignedBigInteger('id_base');
            $table->foreign('id_base')->references('id')->on('bases');

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
        Schema::dropIfExists('new_sletters');
    }
};
