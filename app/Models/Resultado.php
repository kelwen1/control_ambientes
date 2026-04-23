<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resultado extends Model
{
    protected $table = 'resultados';

    protected $primaryKey = 'id_resultado';

    protected $fillable = [
        'denominacion',
        'id_competencia',
        'horas',
        'sesiones',
    ];

    public function competencia(): BelongsTo
    {
        return $this->belongsTo(Competencia::class, 'id_competencia');
    }
}
