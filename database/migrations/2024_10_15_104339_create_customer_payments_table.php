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
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount')->default(0);

            $table->bigInteger('id_payment_method')->unsigned();
            $table->foreign('id_payment_method')->references('id')->on('payment_methods')->onDelete('cascade');

            $table->unsignedBigInteger('id_customer_flight');
            $table->foreign('id_customer_flight')->references('id')->on('flight_customers')->onDelete('cascade');

            $table->string('payment_voucher')->nullable();
            $table->string('payment_ticket')->nullable();
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
        Schema::dropIfExists('customer_payments');
    }
};
