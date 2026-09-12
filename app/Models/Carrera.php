<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    protected $fillable = ['nombre', 'codigo', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function proyectos()
    {
        return $this->hasMany(ProyectoTitulacion::class);
    }
}
