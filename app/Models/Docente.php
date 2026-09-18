<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'email',
        'especialidad',
        'user_id',
        'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function cuenta()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function proyectosTutorados()
    {
        return $this->hasMany(ProyectoTitulacion::class, 'tutor_id');
    }

    public function carreras()
    {
        return $this->belongsToMany(Carrera::class, 'carrera_docente');
    }
}
