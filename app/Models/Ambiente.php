<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ambiente extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ambientes';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_ambiente';

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
        'num_ambiente',
        'id_estado',
        'capacidad_max',
        'id_tipo_ambiente',
    ];
}
