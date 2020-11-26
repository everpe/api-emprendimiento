<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLienzosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lienzos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('op1')->nullable();
            $table->json('op2')->nullable();
            $table->json('op3')->nullable();
            $table->json('op4')->nullable();
            $table->json('op5')->nullable();
            $table->json('op6')->nullable();
            $table->timestamps();

            // Una interpretación de Maslow pertenece a un Test.
            $table->unsignedBigInteger('test_id');
            $table->foreign('test_id')->nullable()
                        ->references('id')->on('tests')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lienzos');
    }
}
