<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    /**
     * Una Sección puede estar en muchas actividadess
     */
    public function activities() {
        return $this->belongsToMany('App\Activity')->withPivot('score');;
    }
}
