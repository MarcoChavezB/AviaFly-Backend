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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->date('order_date');
            $table->decimal('total', 8, 2);
            $table->decimal('sub_total', 8, 2);
            $table->integer('due_week')->nullable();
            $table->decimal('installment_value', 8, 2)->nullable();
            $table->enum('payment_status', ['pendiente', 'pagado', 'cancelado']);

            $table->unsignedBigInteger('id_client')->nullable();
            $table->foreign('id_client')->references('id')->on('students')->nullable();

            $table->unsignedBigInteger('id_discount')->nullable();
            $table->foreign('id_discount')->references('id')->on('discounts')->nullOnDelete();

            $table->unsignedBigInteger('id_employe');
            $table->foreign('id_employe')->references('id')->on('employees');

            $table->unsignedBigInteger('id_payment_method');
            $table->foreign('id_payment_method')->references('id')->on('payment_methods');

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
        Schema::dropIfExists('orders');
    }
};
