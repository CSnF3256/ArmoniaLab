<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tipos de acordes (Core musical)
        Schema::create('tipo_acordes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: Mayor, Menor, Séptima de Dominante
            $table->string('simbolo', 20); // Ej: M, m, 7, maj7, dim
            $table->string('intervalos_semitonos'); // Ej: [0, 4, 7] o 0,4,7
            $table->string('categoria')->default('Tríadas'); // Tríadas, Séptimas, Alterados, Suspendidos
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        // 2. Acordes guardados por los usuarios (Biblioteca personal)
        Schema::create('acorde_guardados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tipo_acorde_id')->constrained('tipo_acordes')->cascadeOnDelete();
            $table->string('fundamental', 10); // Ej: Do, Re, Fa#
            $table->string('nombre_completo'); // Ej: Do Mayor, Sol 7
            $table->text('notas'); // Ej: ["Do", "Mi", "Sol"]
            $table->timestamps();
        });

        // 3. Lecciones (pertenecen a cursos)
        Schema::create('lecciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('contenido');
            $table->string('nivel')->default('Principiante');
            $table->integer('orden')->default(1);
            $table->string('habilidad')->default('Fundamentos'); // Tríadas Mayores, Tríadas Menores, Intervalos, etc.
            $table->timestamps();
        });

        // 4. Ejercicios (pertenecen a lecciones, relación en cascada para dropdowns dependientes)
        Schema::create('ejercicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leccion_id')->constrained('lecciones')->cascadeOnDelete();
            $table->foreignId('tipo_acorde_id')->nullable()->constrained('tipo_acordes')->nullOnDelete();
            $table->text('pregunta');
            $table->string('tipo')->default('opcion_multiple'); // opcion_multiple, construir_acorde, identificar_notas, intervalo
            $table->string('habilidad'); // Tríadas Mayores, Tríadas Menores, Acordes de 7ma, Intervalos, Teclado
            $table->string('dificultad')->default('Fácil'); // Fácil, Intermedio, Avanzado
            $table->json('datos_pregunta')->nullable(); // Opciones, notas dadas, fundamental, etc.
            $table->text('respuesta_correcta');
            $table->text('explicacion')->nullable();
            $table->integer('puntos')->default(10);
            $table->timestamps();
        });

        // 5. Intentos de ejercicios
        Schema::create('intentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ejercicio_id')->constrained('ejercicios')->cascadeOnDelete();
            $table->text('respuesta_usuario');
            $table->boolean('es_correcta');
            $table->integer('puntaje_obtenido')->default(0);
            $table->text('explicacion_resultado')->nullable();
            $table->integer('tiempo_segundos')->nullable();
            $table->string('habilidad')->nullable();
            $table->timestamps();
        });

        // 6. Progreso por lección
        Schema::create('progreso_lecciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leccion_id')->constrained('lecciones')->cascadeOnDelete();
            $table->string('estatus')->default('en_progreso'); // no_iniciada, en_progreso, completada
            $table->timestamp('fecha_completado')->nullable();
            $table->integer('intentos_totales')->default(0);
            $table->integer('intentos_correctos')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'leccion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progreso_lecciones');
        Schema::dropIfExists('intentos');
        Schema::dropIfExists('ejercicios');
        Schema::dropIfExists('lecciones');
        Schema::dropIfExists('acorde_guardados');
        Schema::dropIfExists('tipo_acordes');
    }
};
