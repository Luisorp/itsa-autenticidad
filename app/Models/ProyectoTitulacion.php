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

    public const PERIODOS_ACADEMICOS = [
        'anual' => 'Anual',
        'semestre_i' => 'Semestre I',
        'semestre_ii' => 'Semestre II',
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
        'periodo_academico',
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

    public function getPeriodoAcademicoNombreAttribute(): string
    {
        return self::PERIODOS_ACADEMICOS[$this->periodo_academico] ?? 'Sin especificar';
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

    public function reporte()
    {
        return $this->hasOne(Reporte::class, 'proyecto_id');
    }
}
