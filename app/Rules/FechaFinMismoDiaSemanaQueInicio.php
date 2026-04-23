<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * La fecha de fin debe caer el mismo día de la semana que la fecha de inicio
 * (p. ej. jueves a jueves en reservas recurrentes semanales).
 */
class FechaFinMismoDiaSemanaQueInicio implements ValidationRule
{
    public function __construct(
        private readonly ?string $fechaInicio
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->fechaInicio || ! $value) {
            return;
        }
        try {
            $ini = Carbon::parse($this->fechaInicio)->startOfDay();
            $fin = Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return;
        }
        if ($ini->dayOfWeek !== $fin->dayOfWeek) {
            $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
            $nombre = $dias[$ini->dayOfWeek];
            $fail("La fecha de fin debe ser un {$nombre} (mismo día de la semana que la fecha de inicio).");
        }
    }
}
