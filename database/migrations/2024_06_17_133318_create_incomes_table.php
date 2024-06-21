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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('employee_id');
            $table->date('payment_date');
            $table->string('concept');

            $table->decimal('original_import', 8, 2);
            $table->decimal('discount', 8, 2);
            $table->decimal('iva', 8, 2);
            $table->decimal('commission', 8, 2)->default(0.00);
            $table->decimal('total', 8, 2);

            $table->string('payment_method');
            $table->string('bank_account')->nullable();
            $table->string('file_path')->nullable();
            $table->string('ticket_path')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('employee_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('incomes');
    }
};
