<?php

namespace App\Http\Requests;

use App\Helpers\DiaSemanaDesdeFechaHelper;
use App\Models\Reserva;
use App\Rules\DiaSemanaCoincideConFechaInicio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateReservaRequest extends FormRequest
{
    /**
     * En edición, ficha, instructor, fechas, jornada y estado no son editables en el formulario:
     * se toman siempre de la reserva en BD (evita manipular el POST).
     */
    protected function prepareForValidation(): void
    {
        $id = $this->route('id');
        if ($id === null) {
            return;
        }
        $reserva = Reserva::find($id);
        if (! $reserva) {
            return;
        }
        $mapaIdJornadaAClave = [1 => 'manana', 2 => 'tarde', 3 => 'noche', 4 => 'fin_semana'];
        $idJornadaFicha = DB::table('ficha')->where('id_ficha', $reserva->id_ficha)->value('id_jornada');
        $jornadaClave = $mapaIdJornadaAClave[(int) $idJornadaFicha] ?? 'manana';

        $fi = $reserva->fecha_inicio;
        $ff = $reserva->fecha_fin;
        if ($fi instanceof \DateTimeInterface) {
            $fi = $fi->format('Y-m-d');
        } elseif (is_string($fi) && $fi !== '') {
            $fi = \Carbon\Carbon::parse($fi)->format('Y-m-d');
        }
        if ($ff instanceof \DateTimeInterface) {
            $ff = $ff->format('Y-m-d');
        } elseif (is_string($ff) && $ff !== '') {
            $ff = \Carbon\Carbon::parse($ff)->format('Y-m-d');
        }

        $diaClave = ($fi !== null && $fi !== '') ? DiaSemanaDesdeFechaHelper::claveDesdeYmd((string) $fi) : null;

        $this->merge([
            'id_ficha' => $reserva->id_ficha,
            'id_persona' => $reserva->id_persona,
            'fecha_inicio' => $fi,
            'fecha_fin' => $ff,
            'jornada' => $jornadaClave,
            'id_estado_reserva' => $reserva->id_estado_reserva,
            'dia_semana' => $diaClave ?? '',
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        // Roles: administrador, coordinacion_L, coordinacion, instructor
        return $user && in_array($user->id_rol, array_values(config('roles.ids', [1, 2, 3, 4])), true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_ambiente' => 'required|integer|exists:ambientes,id_ambiente',
            'id_ficha' => 'required|integer|exists:ficha,id_ficha',
            'id_competencia' => [
                'required',
                'integer',
                Rule::exists('competencia', 'id_competencia'),
            ],
            'id_resultado' => [
                'nullable',
                'integer',
                Rule::exists('resultados', 'id_resultado')->where(function ($query) {
                    return $query->where('id_competencia', $this->input('id_competencia'));
                }),
            ],
            'id_persona' => 'nullable|exists:persona,id_persona',
            'dia_semana' => [
                'required',
                'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
                new DiaSemanaCoincideConFechaInicio($this->input('fecha_inicio')),
            ],
            'jornada' => 'required|in:manana,tarde,noche,fin_semana',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => [
                'required',
                'date',
                'after_or_equal:fecha_inicio',
            ],
            'id_estado_reserva' => 'required|integer|exists:estado_reserva,id_estado_reserva',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id_ambiente.required' => 'Debe seleccionar un ambiente.',
            'id_ambiente.exists' => 'El ambiente seleccionado no existe.',
            'id_ficha.required' => 'Debe seleccionar una ficha.',
            'id_ficha.exists' => 'La ficha seleccionada no existe.',
            'id_competencia.required' => 'Debe seleccionar una competencia.',
            'id_competencia.exists' => 'La competencia seleccionada no es válida.',
            'dia_semana.required' => 'Indique la fecha de inicio; el día se define automáticamente a partir de ella.',
            'dia_semana.in' => 'El día seleccionado no es válido.',
            'jornada.required' => 'Debe seleccionar una jornada.',
            'jornada.in' => 'La jornada seleccionada no es válida.',
            'fecha_inicio.required' => 'Debe ingresar una fecha de inicio.',
            'fecha_inicio.date' => 'El formato de la fecha de inicio no es válido.',
            'fecha_fin.required' => 'Debe ingresar una fecha de fin.',
            'fecha_fin.date' => 'El formato de la fecha de fin no es válido.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'id_estado_reserva.required' => 'Debe seleccionar un estado de reserva.',
            'id_estado_reserva.exists' => 'El estado de reserva seleccionado no existe.',
            'id_resultado.exists' => 'El resultado no pertenece a la competencia seleccionada.',
        ];
    }
}
