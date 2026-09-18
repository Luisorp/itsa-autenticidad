<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'email',
        'carrera_id',
        'gestion_ingreso',
        'user_id',
        'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function cuenta()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function proyectos()
    {
        return $this->hasMany(ProyectoTitulacion::class, 'estudiante_id');
    }
}
