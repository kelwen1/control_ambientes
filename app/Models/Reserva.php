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
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'fecha_inicio',
        'fecha_fin',
        'id_estado_reserva',
        'observaciones',
    ];

    /**
     * Get the ambiente that owns the reserva.
     */
    public function ambiente()
    {
        return $this->belongsTo(Ambiente::class, 'id_ambiente', 'id_ambiente');
    }

    /**
     * Get the ficha that owns the reserva.
     */
    public function ficha()
    {
        return $this->belongsTo(Ficha::class, 'id_ficha', 'id_ficha');
    }
}

