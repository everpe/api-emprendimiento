<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lienzo extends Model
{
    /**
     * Convirtiendo los campos de json -> array.
     * ya que deben ser arreglos de strings.
     */
    protected $casts = [
        'op1' => 'array',
        'op2' => 'array',
        'op3' => 'array',
        'op4' => 'array',
        'op5' => 'array',
        'op6' => 'array',
    ];

    /**
     * Una interpretación de Maslow pertenece a un test.
     */
    public function test()
    {
        return $this->belongsTo('App\Test');
    }
}
