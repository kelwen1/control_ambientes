<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Persona extends Model
{
    protected $table = 'persona';

    protected $primaryKey = 'id_persona';

    /**
     * En la base de datos final, el campo id_persona almacena la cédula.
     * No usamos auto-incremento porque el valor lo proporciona el usuario.
     */
    public $incrementing = false;

    protected $fillable = [
        'id_persona',
        'nombres',
        'apellidos',
        'correo',
        'telefono',
        'id_rol',
    ];

    protected function casts(): array
    {
        return [
            'id_rol' => 'integer',
        ];
    }

    /**
     * Rol de la persona.
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    /**
     * Cuenta de usuario asociada (uno a uno).
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id_persona');
    }

    /**
     * Reservas asignadas a este instructor.
     */
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'id_persona', 'id_persona');
    }
}
