<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ficha extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ficha';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_ficha';

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
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'num_ficha',
        'cant_aprendices',
        'id_programa',
        'id_jornada',
        'fecha_inicio',
        'fecha_fin',
        'fecha_productiva',
    ];

    protected function casts(): array
    {
        return [
            'id_jornada' => 'integer',
        ];
    }

    /**
     * Programa de formación al que pertenece la ficha.
     */
    public function programa()
    {
        return $this->belongsTo(Programa::class, 'id_programa', 'id_programa');
    }

    /**
     * Jornada de formación del grupo (tabla jornada).
     */
    public function jornada()
    {
        return $this->belongsTo(Jornada::class, 'id_jornada', 'id_jornada');
    }

    /**
     * Avance actual de la ficha (competencia, resultado, sección) si existe.
     */
    public function avanceActual()
    {
        return $this->hasOne(AvanceFicha::class, 'id_ficha', 'id_ficha')->latestOfMany();
    }
}
