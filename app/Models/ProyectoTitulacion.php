<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProyectoTitulacion extends Model
{
    public const MODALIDADES = [
        'proyecto_grado' => 'Proyecto de grado',
        'sociocomunitario_productivo' => 'Proyecto sociocomunitario productivo',
        'emprendimiento_productivo' => 'Proyecto de emprendimiento productivo',
        'trabajo_dirigido_externo' => 'Trabajo dirigido externo',
    ];

    protected $table = 'proyectos_titulacion';

    protected $fillable = [
        'titulo',
        'resumen',
        'modalidad',
        'carrera_id',
        'estudiante_id',
        'tutor_id',
        'anio',
        'estado',
        'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function getModalidadNombreAttribute(): string
    {
        return self::MODALIDADES[$this->modalidad] ?? 'Modalidad no definida';
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function tutor()
    {
        return $this->belongsTo(Docente::class, 'tutor_id');
    }

    public function documento()
    {
        return $this->hasOne(Documento::class, 'proyecto_id');
    }

    public function reportes()
    {
        return $this->hasMany(Reporte::class, 'proyecto_id');
    }
}
