<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('board_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('board_id')->unsigned();
            $table->foreign('board_id')->references('id')->on('boards');
            $table->bigInteger('waiter_id')->unsigned(); //garçom
            $table->foreign('waiter_id')->references('id')->on('users');
            $table->bigInteger('client_id')->default(0)->index();
            $table->enum('state', ['aberto', 'fechado'])->default('aberto')->index();
            $table->decimal('total_order')->default(0)->index();
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
        Schema::dropIfExists('board_logs');
    }
};
