<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competencia extends Model
{
    protected $table = 'competencia';

    protected $primaryKey = 'id_competencia';

    protected $fillable = [
        'nombre_competencia',
        'id_programa',
        'nombre_norma',
        'codigo',
        'hora_totales',
        'porcentaje_horas',
        'duracion',
        'cantidad_resultados',
    ];

    /**
     * Legado: antes cada competencia podía asociarse a un programa. Ahora el catálogo es común
     * (id_programa null); la vinculación a un programa concreto es por la ficha.
     */
    public function programa(): BelongsTo
    {
        return $this->belongsTo(Programa::class, 'id_programa');
    }

    public function resultados(): HasMany
    {
        return $this->hasMany(Resultado::class, 'id_competencia');
    }

    /**
     * Horas en el complejo (cupo para repartir entre resultados).
     * Prioriza `duracion` guardada; si es 0 (legado o incompleto), recalcula con hora_totales y porcentaje_horas.
     */
    public function horasDuracionEnComplejo(): int
    {
        $guardado = (int) ($this->duracion ?? 0);
        if ($guardado > 0) {
            return $guardado;
        }

        $horaTotales = (int) ($this->hora_totales ?? 0);
        if ($horaTotales < 1) {
            $raw = $this->getAttributes();
            if (isset($raw['hora totales'])) {
                $horaTotales = (int) $raw['hora totales'];
            }
        }

        $porcentaje = (int) ($this->porcentaje_horas ?? 0);

        if ($horaTotales < 1) {
            return 0;
        }

        // Si no hay porcentaje guardado, usar el mismo mínimo que al crear competencia (60 %).
        $p = $porcentaje >= 1 ? $porcentaje : 60;
        if ($p > 85) {
            $p = 85;
        }
        if ($p < 60) {
            $p = 60;
        }

        return (int) round($horaTotales * ($p / 100));
    }

    /**
     * Horas totales de la competencia para repartir entre resultados (antes solo existía duracion como total).
     */
    public function horasTotalesParaDistribucion(): int
    {
        $ht = (int) ($this->hora_totales ?? 0);
        if ($ht > 0) {
            return $ht;
        }

        return (int) ($this->duracion ?? 0);
    }
}
