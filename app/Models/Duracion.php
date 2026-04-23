<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Duracion extends Model
{
    protected $table = 'duracion';

    protected $primaryKey = 'id_duracion';

    /**
     * En varias instalaciones id_duracion no es AUTO_INCREMENT; se asigna al crear (como nivel_programa).
     */
    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_duracion',
        'duracion',
    ];

    protected static function booted(): void
    {
        static::creating(function (Duracion $model): void {
            if ($model->id_duracion !== null) {
                return;
            }
            $max = (int) static::query()->max('id_duracion');
            $model->id_duracion = $max > 0 ? $max + 1 : 1;
        });
    }

    /**
     * id_duracion de una fila cuyo texto ya represente estos meses, o null si no existe.
     */
    public static function findIdForMeses(int $meses): ?int
    {
        $existing = static::query()
            ->get(['id_duracion', 'duracion'])
            ->first(function ($row) use ($meses): bool {
                if (! preg_match('/(\d+)/u', (string) $row->duracion, $m)) {
                    return false;
                }

                return (int) $m[1] === $meses;
            });

        return $existing !== null ? (int) $existing->id_duracion : null;
    }

    /**
     * Reutiliza una fila de duracion cuyo texto ya represente estos meses, o crea "N meses".
     */
    public static function idForMeses(int $meses): int
    {
        return static::findIdForMeses($meses) ?? (int) static::create(['duracion' => $meses.' meses'])->id_duracion;
    }

    public function nivelesPrograma(): HasMany
    {
        return $this->hasMany(NivelPrograma::class, 'id_duracion', 'id_duracion');
    }
}
