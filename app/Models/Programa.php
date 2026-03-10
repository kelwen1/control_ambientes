<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'programa';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_programa';

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
        'nombre_programa',
    ];

    /**
     * Competencias del programa (SENA).
     */
    public function competencias()
    {
        return $this->hasMany(Competencia::class, 'id_programa');
    }
}

