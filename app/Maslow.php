<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Maslow extends Model
{

    /**
     * Convirtiendo los campos de json -> array.
     * ya que deben ser arreglos de strings.
     */
    protected $casts = [
        'combinations' => 'array',
        'selected' => 'array',
        'explanation' => 'array'
    ];

    /**
     * Una interpretación de Maslow pertenece a un test.
     */
    public function test()
    {
        return $this->belongsTo('App\Test');
    }
}
