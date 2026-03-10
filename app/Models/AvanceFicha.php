<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvanceFicha extends Model
{
    protected $table = 'avance_ficha';

    protected $fillable = ['id_ficha', 'id_competencia', 'id_resultado', 'seccion'];

    public function ficha(): BelongsTo
    {
        return $this->belongsTo(Ficha::class, 'id_ficha');
    }

    public function competencia(): BelongsTo
    {
        return $this->belongsTo(Competencia::class, 'id_competencia');
    }

    public function resultado(): BelongsTo
    {
        return $this->belongsTo(Resultado::class, 'id_resultado');
    }
}
