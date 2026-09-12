<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Documento extends Model
{
    protected $fillable = [
        'proyecto_id',
        'nombre_archivo',
        'ruta_archivo',
        'tipo_archivo',
        'tamano_archivo',
        'hash_archivo',
        'contenido_extraido',
    ];

    public function proyecto()
    {
        return $this->belongsTo(ProyectoTitulacion::class, 'proyecto_id');
    }

    public function getArchivoDisponibleAttribute(): bool
    {
        return filled($this->ruta_archivo) && Storage::disk('public')->exists($this->ruta_archivo);
    }

    public function comparacionesComoA()
    {
        return $this->hasMany(Comparacion::class, 'documento_a_id');
    }

    public function comparacionesComoB()
    {
        return $this->hasMany(Comparacion::class, 'documento_b_id');
    }
}
