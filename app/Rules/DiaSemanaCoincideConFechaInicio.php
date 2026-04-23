<?php

namespace App\Rules;

use App\Helpers\DiaSemanaDesdeFechaHelper;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * El día de la semana de la reserva debe ser el mismo que el de fecha_inicio (Carbon dayOfWeek, domingo = 0).
 */
class DiaSemanaCoincideConFechaInicio implements ValidationRule
{
    public function __construct(
        private readonly ?string $fechaInicio
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->fechaInicio === null || $this->fechaInicio === '' || $value === null || $value === '') {
            return;
        }

        $expected = DiaSemanaDesdeFechaHelper::claveDesdeYmd($this->fechaInicio);
        if ($expected !== null && (string) $value !== $expected) {
            $fail('El día de la semana debe coincidir con la fecha de inicio.');
        }
    }
}
