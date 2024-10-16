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
        Schema::create('school_expenses', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("motive")->nullable();
            $table->date("date");
            $table->string("invoice_path")->nullable();

            $table->enum('status', ['pendiente', 'aprobado', 'cancelado'])->default('pendiente');
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('employees')->onDelete('cascade');

            $table->unsignedBigInteger('payment_method');
            $table->foreign('payment_method')->references('id')->on('payment_methods')->onDelete('cascade');

            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('employees')->onDelete('cascade');
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
        Schema::dropIfExists('school_expenses');
    }
};
