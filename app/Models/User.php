<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'rol', 'carrera_id', 'activo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const ROLES = [
        'administrador' => 'Administrador',
        'gestor' => 'Gestor',
        'usuario' => 'Usuario',
    ];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function estudiante()
    {
        return $this->hasOne(Estudiante::class);
    }

    public function docente()
    {
        return $this->hasOne(Docente::class);
    }

    public function reportesGenerados()
    {
        return $this->hasMany(Reporte::class, 'generado_por');
    }

    public function esAdministrador(): bool
    {
        return $this->rol === 'administrador';
    }

    public function esGestor(): bool
    {
        return $this->rol === 'gestor';
    }

    public function esUsuario(): bool
    {
        return $this->rol === 'usuario';
    }
}
