<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    /*
     * Retorna todos los usuarios que realizaron cierta prueba.
     * Relación de muchos a muchos.
     */
    public function users() {
        return $this->belongsToMany('App\User')    
        ->withPivot('interpretation')
        ->withTimestamps();
    }

    /**
     * Relación 1 a muchos, una prueba tiene muchas actividades
     */
    public function activities() {
        return $this->hasMany('App\Activity');
    }
}
