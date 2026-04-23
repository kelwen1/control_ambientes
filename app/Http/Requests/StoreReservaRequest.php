<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $id = $this->input('id_persona');
        if ($id === '' || $id === null) {
            $this->merge(['id_persona' => null]);
        }
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
            'id_persona' => ['nullable', 'regex:/^\d{7,10}$/', 'exists:persona,id_persona'],
            'dias_semana' => ['required', 'array', 'min:1'],
            'dias_semana.*' => ['required', 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo'],
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'fecha_fin' => ['required', 'date', 'after_or_equal:today', 'after_or_equal:fecha_inicio'],
            'fechas_sesion' => ['required', 'array', 'min:1'],
            'fechas_sesion.*' => ['required', 'date_format:Y-m-d'],
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
            'dias_semana.required' => 'Seleccione al menos un día de la semana para las sesiones.',
            'dias_semana.min' => 'Seleccione al menos un día de la semana para las sesiones.',
            'dias_semana.*.in' => 'Uno de los días seleccionados no es válido.',
            'fechas_sesion.required' => 'Debe generar la lista de fechas de sesión.',
            'fechas_sesion.min' => 'Debe quedar al menos una fecha en la lista.',
            'fechas_sesion.*.date_format' => 'Formato de fecha inválido en la lista de sesiones.',
            'fecha_inicio.required' => 'Debe ingresar una fecha de inicio.',
            'fecha_inicio.date' => 'El formato de la fecha de inicio no es válido.',
            'fecha_inicio.after_or_equal' => 'La fecha de inicio no puede ser anterior a hoy.',
            'fecha_fin.required' => 'Debe ingresar una fecha de fin.',
            'fecha_fin.date' => 'El formato de la fecha de fin no es válido.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio y no anterior a hoy.',
            'id_resultado.exists' => 'El resultado no pertenece a la competencia seleccionada.',
            'id_persona.regex' => 'La cédula del instructor debe tener entre 7 y 10 dígitos numéricos.',
            'id_persona.exists' => 'No existe un instructor registrado con esa cédula.',
        ];
    }
}
