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
        Schema::create('employee_licenses', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_employee');
            $table->foreign('id_employee')->references('id')->on('employees');

            $table->unsignedBigInteger('id_license');
            $table->foreign('id_license')->references('id')->on('licenses');

            $table->date('expiration_date')->nullable();
            $table->string('license_date')->nullable();

            $table->integer('group')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_licenses');
    }
};
