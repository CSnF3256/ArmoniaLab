<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * feat(db): agregar modalidades y metadatos de ejercicios
 *
 * Añade la columna `modalidad` a la tabla `ejercicios` para permitir
 * tipificar con más granularidad el formato de presentación de cada
 * pregunta: completar_secuencia, seleccionar_intervalo, verdadero_falso,
 * ordenar_notas, etc. — se suma a las modalidades base ya existentes en
 * la columna `tipo`.
 *
 * También agrega `metadata` (JSON nullable) para almacenar parámetros
 * adicionales específicos de cada modalidad sin necesidad de nuevas
 * columnas en el futuro.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ejercicios', function (Blueprint $table) {
            // Modalidad de presentación de la pregunta
            $table->string('modalidad')->default('clasica')->after('tipo')
                ->comment('Modalidad visual del ejercicio: clasica, completar_secuencia, seleccionar_intervalo, verdadero_falso, ordenar_notas, detectar_error');

            // Metadatos adicionales libres por modalidad (hints, pistas, configuración visual, etc.)
            $table->json('metadata')->nullable()->after('datos_pregunta')
                ->comment('Metadatos opcionales propios de la modalidad (pistas, número de intentos máximos, tiempo límite, etc.)');
        });
    }

    public function down(): void
    {
        Schema::table('ejercicios', function (Blueprint $table) {
            $table->dropColumn(['modalidad', 'metadata']);
        });
    }
};

