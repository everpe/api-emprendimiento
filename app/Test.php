<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    /*
     * Retorna el usuario pripietario de una prueba.
     * Relación de muchos a muchos.
     */
    public function user() 
    {
        return $this->belongsTo('App\User');//->withTimestamps();
    }

    /**
     * Relación 1 a muchos, una prueba tiene muchas actividades
     */
    public function activities() 
    {
        return $this->hasMany('App\Activity');
    }

    /**
     * Un test puede tener un maslow.
     */
    public function maslow()
    {
        return $this->hasOne('App\Maslow');
    }
}
