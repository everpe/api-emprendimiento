<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Esta es la entidad débil en actividad y Sección.
 */
class CreateActivitySectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_section', function (Blueprint $table) {
            $table->bigIncrements('id');
              //Llaves Foraneas
                $table->unsignedBigInteger('activity_id');
                $table->unsignedBigInteger('section_id');
            //Campo extra de Pivot
                $table->integer('score')->unsigned();
                $table->timestamps();
  
                $table->foreign('activity_id')
                    ->references('id')->on('activities')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->foreign('section_id')
                ->references('id')->on('sections')
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
        Schema::dropIfExists('activity_section');
    }
}
