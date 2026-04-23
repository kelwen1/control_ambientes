<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reservas';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_reserva';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_ambiente',
        'id_ficha',
        'id_persona',
        'id_jornada',
        'id_competencia',
        'id_resultado',
        'id_dia_semana',
        'fecha_inicio',
        'fecha_fin',
        'id_estado_reserva',
    ];

    /**
     * La jornada vive en la ficha; se expone aquí para vistas y lógica que usan $reserva->id_jornada.
     */
    public function getIdJornadaAttribute(): ?int
    {
        if ($this->relationLoaded('ficha')) {
            $v = $this->ficha?->id_jornada;

            return $v !== null ? (int) $v : null;
        }

        $v = $this->ficha()->value('id_jornada');

        return $v !== null ? (int) $v : null;
    }

    /**
     * Ambiente reservado.
     */
    public function ambiente()
    {
        return $this->belongsTo(Ambiente::class, 'id_ambiente', 'id_ambiente');
    }

    /**
     * Ficha asignada.
     */
    public function ficha()
    {
        return $this->belongsTo(Ficha::class, 'id_ficha', 'id_ficha');
    }

    /**
     * Instructor asignado (persona que da la clase).
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function competencia()
    {
        return $this->belongsTo(Competencia::class, 'id_competencia', 'id_competencia');
    }

    /**
     * Días liberados: fechas específicas en las que esta reserva no ocupa el ambiente.
     * Permite que otro instructor reserve ese slot para esa fecha.
     */
    public function diasLiberados()
    {
        return $this->hasMany(ReservaDiaLiberado::class, 'id_reserva', 'id_reserva');
    }
}
