<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'id_usuario';

    /**
     * Columna real en BD: 'password' (producción) o 'contraseña' (migraciones nuevas).
     * Se detecta automáticamente si la tabla tiene 'password'.
     */
    protected $fillable = [
        'id_persona',
        'user',
        'password',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'id_persona' => 'integer',
        ];
    }

    protected $username = 'user';

    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Alias para código que usa $user->contraseña (AjustesController, etc.)
     */
    public function getContraseñaAttribute()
    {
        return $this->attributes['password'] ?? $this->attributes['contraseña'] ?? null;
    }

    public function setContraseñaAttribute($value)
    {
        $this->attributes['password'] = $value;
    }

    /**
     * Persona asociada (datos personales).
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    /**
     * Persona que creó esta cuenta de usuario (users.created_by → persona.id_persona).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'created_by', 'id_persona');
    }

    /**
     * Rol vía persona (id_rol está en persona).
     */
    public function getRolAttribute(): ?Rol
    {
        return $this->persona?->rol;
    }

    /**
     * id_rol para compatibilidad (delega a persona).
     */
    public function getIdRolAttribute(): ?int
    {
        return $this->persona?->id_rol;
    }

    /**
     * Nombre completo (nombres + apellidos de persona).
     */
    public function getNameAttribute(): string
    {
        return $this->persona
            ? trim("{$this->persona->nombres} {$this->persona->apellidos}")
            : '';
    }

    public function getNombreAttribute(): ?string
    {
        return $this->persona?->nombres;
    }

    public function getApellidoAttribute(): ?string
    {
        return $this->persona?->apellidos;
    }

    public function getCorreoAttribute(): ?string
    {
        return $this->persona?->correo;
    }

    public function getTelefonoAttribute(): ?string
    {
        return $this->persona?->telefono;
    }

    /**
     * Cédula para compatibilidad con auditoría y ajustes.
     */
    public function getIdCedulaAttribute(): ?string
    {
        // En el modelo de datos, id_persona almacena la cédula
        return $this->persona?->id_persona;
    }

    /** Roles: administrador (1), coordinacion_L (2), coordinacion (3), instructor (4). */
    public function isAdmin(): bool
    {
        return (int) ($this->persona?->id_rol ?? 0) === config('roles.ids.administrador', 1);
    }

    /** Coordinación mayor (coordinacion_L) y coordinación. */
    public function isCoordinator(): bool
    {
        $id = (int) ($this->persona?->id_rol ?? 0);
        return $id === config('roles.ids.coordinacion_L', 2)
            || $id === config('roles.ids.coordinacion', 3);
    }

    /** Instructor. */
    public function isInstructor(): bool
    {
        return (int) ($this->persona?->id_rol ?? 0) === config('roles.ids.instructor', 4);
    }

    /** Alias de isInstructor() para compatibilidad. */
    public function isUser(): bool
    {
        return $this->isInstructor();
    }
}
