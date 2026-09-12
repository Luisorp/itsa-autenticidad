<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comparacion extends Model
{
    protected $table = 'comparaciones';

    protected $fillable = [
        'documento_a_id',
        'documento_b_id',
        'porcentaje_similitud',
        'algoritmo_usado',
    ];

    protected $casts = [
        'porcentaje_similitud' => 'decimal:2',
    ];

    public function documentoA()
    {
        return $this->belongsTo(Documento::class, 'documento_a_id');
    }

    public function documentoB()
    {
        return $this->belongsTo(Documento::class, 'documento_b_id');
    }
}