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
        Schema::create('pendings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description');
            $table->date('date_to_complete');
            $table->boolean('is_urgent');
            $table->boolean('status')->default(0);
            $table->timestamps();

            $table->unsignedBigInteger('id_created_by');
            $table->foreign('id_created_by')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('id_assigned_to')->nullable();
            $table->foreign('id_assigned_to')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pendings');
    }
};
