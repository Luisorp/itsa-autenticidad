<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Reporte extends Model
{
    protected $fillable = [
        'proyecto_id',
        'generado_por',
        'ruta_pdf',
    ];

    public function proyecto()
    {
        return $this->belongsTo(ProyectoTitulacion::class, 'proyecto_id');
    }

    public function getArchivoDisponibleAttribute(): bool
    {
        return filled($this->ruta_pdf) && Storage::disk('public')->exists($this->ruta_pdf);
    }

    public function generadoPor()
    {
        return $this->belongsTo(User::class, 'generado_por');
    }
}
