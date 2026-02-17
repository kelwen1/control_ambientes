<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateReservaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        // Solo permitir a usuarios autenticados con roles válidos (1 = admin, 2 = usuario)
        return $user && in_array($user->id_rol, [1, 2, 3], true);
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
            'dia_semana' => 'required|in:lunes,sabado',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    if ($this->hora_inicio && $value) {
                        $horaInicio = Carbon::createFromFormat('H:i', $this->hora_inicio);
                        $horaFin = Carbon::createFromFormat('H:i', $value);
                        if ($horaFin->lte($horaInicio)) {
                            $fail('La hora de fin debe ser posterior a la hora de inicio.');
                        }
                    }
                },
            ],
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'id_estado_reserva' => 'required|integer|exists:estado_reserva,id_estado_reserva',
            'observaciones' => 'nullable|string|max:500',
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
            'dia_semana.required' => 'Debe seleccionar un día de la semana.',
            'dia_semana.in' => 'El día seleccionado no es válido.',
            'hora_inicio.required' => 'Debe ingresar una hora de inicio.',
            'hora_inicio.date_format' => 'El formato de la hora de inicio no es válido.',
            'hora_fin.required' => 'Debe ingresar una hora de fin.',
            'hora_fin.date_format' => 'El formato de la hora de fin no es válido.',
            'fecha_inicio.required' => 'Debe ingresar una fecha de inicio.',
            'fecha_inicio.date' => 'El formato de la fecha de inicio no es válido.',
            'fecha_fin.required' => 'Debe ingresar una fecha de fin.',
            'fecha_fin.date' => 'El formato de la fecha de fin no es válido.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'id_estado_reserva.required' => 'Debe seleccionar un estado de reserva.',
            'id_estado_reserva.exists' => 'El estado de reserva seleccionado no existe.',
            'observaciones.max' => 'Las observaciones no pueden exceder 500 caracteres.',
        ];
    }
}

