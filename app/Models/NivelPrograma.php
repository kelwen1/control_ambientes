<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NivelPrograma extends Model
{
    protected $table = 'nivel_programa';

    protected $primaryKey = 'id_nivel_programa';

    /**
     * La tabla en algunas instalaciones no usa AUTO_INCREMENT; el ID se asigna al crear.
     */
    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_nivel_programa',
        'nivel_programa',
    ];

    protected static function booted(): void
    {
        static::creating(function (NivelPrograma $model): void {
            if ($model->id_nivel_programa !== null) {
                return;
            }
            $max = (int) static::query()->max('id_nivel_programa');
            $model->id_nivel_programa = $max > 0 ? $max + 1 : 1;
        });
    }

    public function programas(): HasMany
    {
        return $this->hasMany(Programa::class, 'id_nivel_programa', 'id_nivel_programa');
    }
}
