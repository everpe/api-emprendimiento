<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    /**
     * Una Actividad pertenece a una prueba(1:N)
     */
    public function test()
    {
        return $this->belongsTo('App\Test');
    }

    /**
     * Una actividad tiene muchas secciones: A,B,C,D
     */
    public function sections() {
        return $this->belongsToMany('App\Section')->withPivot('score');
    }
}
