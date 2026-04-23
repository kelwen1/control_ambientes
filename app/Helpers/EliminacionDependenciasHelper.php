<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Verifica dependencias antes de eliminar registros para evitar huérfanos o errores de FK.
 * Cada método devuelve null si se puede eliminar, o un texto en español explicando el bloqueo.
 */
final class EliminacionDependenciasHelper
{
    /**
     * Programa → no eliminar si hay competencias o fichas que lo referencian.
     */
    public static function motivoNoEliminarPrograma(int $idPrograma): ?string
    {
        $partes = [];
        if (Schema::hasTable('competencia') && DB::table('competencia')->where('id_programa', $idPrograma)->exists()) {
            $partes[] = 'tiene competencias asociadas';
        }
        if (Schema::hasTable('ficha') && DB::table('ficha')->where('id_programa', $idPrograma)->exists()) {
            $partes[] = 'tiene fichas de formación asociadas';
        }
        if ($partes === []) {
            return null;
        }

        return 'No se puede eliminar el programa porque '.implode(' y ', $partes).'.';
    }

    /**
     * Competencia → no eliminar si hay resultados de aprendizaje o reservas que la usen.
     */
    public static function motivoNoEliminarCompetencia(int $idCompetencia): ?string
    {
        $partes = [];
        if (Schema::hasTable('resultados') && DB::table('resultados')->where('id_competencia', $idCompetencia)->exists()) {
            $partes[] = 'tiene resultados de aprendizaje asociados';
        }
        if (Schema::hasTable('reservas') && DB::table('reservas')->where('id_competencia', $idCompetencia)->exists()) {
            $partes[] = 'tiene reservas de ambientes asociadas';
        }
        if ($partes === []) {
            return null;
        }

        return 'No se puede eliminar la competencia porque '.implode(' y ', $partes).'.';
    }

    /**
     * Resultado → no eliminar si hay reservas vinculadas a ese resultado.
     */
    public static function motivoNoEliminarResultado(int $idResultado): ?string
    {
        if (! Schema::hasTable('reservas')) {
            return null;
        }
        if (DB::table('reservas')->where('id_resultado', $idResultado)->exists()) {
            return 'No se puede eliminar el resultado porque tiene reservas de ambientes asociadas.';
        }

        return null;
    }

    /**
     * Ficha → no eliminar si hay reservas o registro de avance curricular.
     */
    public static function motivoNoEliminarFicha(int $idFicha): ?string
    {
        $partes = [];
        if (Schema::hasTable('reservas') && DB::table('reservas')->where('id_ficha', $idFicha)->exists()) {
            $partes[] = 'tiene reservas de ambientes asociadas';
        }
        if (Schema::hasTable('avance_ficha') && DB::table('avance_ficha')->where('id_ficha', $idFicha)->exists()) {
            $partes[] = 'tiene avance de formación registrado';
        }
        if ($partes === []) {
            return null;
        }

        return 'No se puede eliminar la ficha porque '.implode(' y ', $partes).'.';
    }

    /**
     * Ambiente (catálogo) → no eliminar si existe alguna reserva en ese ambiente.
     */
    public static function motivoNoEliminarAmbiente(int $idAmbiente): ?string
    {
        if (! Schema::hasTable('reservas')) {
            return null;
        }
        if (DB::table('reservas')->where('id_ambiente', $idAmbiente)->exists()) {
            return 'No se puede eliminar el ambiente porque tiene reservas asociadas.';
        }

        return null;
    }

    /**
     * Usuario (persona/instructor) → no eliminar si figura como instructor en reservas.
     * Las reglas de negocio (no auto-borrado, último admin) siguen en el controlador.
     */
    public static function motivoNoEliminarUsuarioPorReservas(string $idPersona): ?string
    {
        if (! Schema::hasTable('reservas')) {
            return null;
        }
        if (DB::table('reservas')->where('id_persona', $idPersona)->exists()) {
            return 'No se puede eliminar el usuario porque tiene reservas de ambientes asociadas (como instructor).';
        }

        return null;
    }

    /**
     * Persona → no eliminar si hay FK hacia persona.id_persona (auditoría created_by/updated_by, etc.).
     */
    public static function motivoNoEliminarPersonaPorReferencias(string $idPersona): ?string
    {
        $pairs = [
            ['competencia', 'created_by'],
            ['competencia', 'updated_by'],
            ['programa', 'created_by'],
            ['programa', 'updated_by'],
            ['resultados', 'created_by'],
            ['resultados', 'updated_by'],
            ['ambientes', 'created_by'],
            ['ambientes', 'updated_by'],
        ];

        foreach ($pairs as [$table, $column]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }
            if (DB::table($table)->where($column, $idPersona)->exists()) {
                return 'No se puede eliminar este usuario porque hay registros (por ejemplo competencias, programas o resultados) que lo tienen registrado como autor o editor. Reasigne o elimine esas dependencias antes.';
            }
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'id_persona')) {
            foreach (['created_by', 'updated_by'] as $column) {
                if (! Schema::hasColumn('users', $column)) {
                    continue;
                }
                $q = DB::table('users')->where($column, $idPersona);
                if (Schema::hasColumn('users', 'id_persona')) {
                    $q->where('id_persona', '!=', $idPersona);
                }
                if ($q->exists()) {
                    return 'No se puede eliminar este usuario porque existe otra cuenta de usuario creada o modificada bajo su registro. Reasigne la auditoría o elimine esas cuentas antes.';
                }
            }
        }

        return null;
    }
}
