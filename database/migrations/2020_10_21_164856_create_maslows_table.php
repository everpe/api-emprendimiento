<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaslowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maslows', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('combinations')->nullable();
            $table->json('selected')->nullable();
            $table->json('explanation')->nullable();
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
        Schema::dropIfExists('maslows');
    }
}
