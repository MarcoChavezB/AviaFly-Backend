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
        Schema::create('income_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('student_id');
            $table->decimal('commission', 10, 2)->default(0.00);
            $table->string('payment_method', 50);
            $table->string('bank_account', 50)->nullable();
            $table->string('file_path', 255)->nullable();
            $table->string('ticket_path', 255)->nullable();
            $table->decimal('total', 8, 2);
            $table->date('payment_date');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('student_id')->references('id')->on('students');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('income_details');
    }
};
