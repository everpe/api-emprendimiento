<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //esta será la entidad débil
        Schema::create('test_user', function (Blueprint $table) {
            $table->bigIncrements('id');

            //Llaves Foraneas de Test y el user que lo hizó
            $table->unsignedBigInteger('test_id');
            $table->unsignedBigInteger('user_id');
            //Este es el atributo pivot que se define en los models
            $table->text('interpretation');
            $table->timestamps();

            $table->foreign('test_id')
                  ->references('id')->on('tests')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            $table->foreign('user_id')
            ->references('id')->on('users')
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
        Schema::dropIfExists('test_user');
    }
}
