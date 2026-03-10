<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    protected $table = 'rol';

    protected $primaryKey = 'id_rol';

    public $timestamps = false;

    protected $fillable = ['rol'];

    /**
     * Personas con este rol.
     */
    public function personas(): HasMany
    {
        return $this->hasMany(Persona::class, 'id_rol');
    }
}
