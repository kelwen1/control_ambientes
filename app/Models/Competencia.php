<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competencia extends Model
{
    protected $table = 'competencia';

    protected $primaryKey = 'id_competencia';

    protected $fillable = ['nombre_competencia', 'id_programa'];

    public function programa(): BelongsTo
    {
        return $this->belongsTo(Programa::class, 'id_programa');
    }

    public function resultados(): HasMany
    {
        return $this->hasMany(Resultado::class, 'id_competencia');
    }
}
