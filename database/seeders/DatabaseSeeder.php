<?php

namespace Database\Seeders;

use App\Models\Ciudad;
use App\Models\Course;
use App\Models\Ejercicio;
use App\Models\Intento;
use App\Models\Leccion;
use App\Models\Pais;
use App\Models\ProgresoLeccion;
use App\Models\Provincia;
use App\Models\TipoAcorde;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Geografía para Cascading Dropdowns
        $ecuador = Pais::firstOrCreate(['codigo' => 'EC'], ['nombre' => 'Ecuador']);
        $colombia = Pais::firstOrCreate(['codigo' => 'CO'], ['nombre' => 'Colombia']);
        $peru = Pais::firstOrCreate(['codigo' => 'PE'], ['nombre' => 'Perú']);

        // Provincias de Ecuador
        $pichincha = Provincia::firstOrCreate(['pais_id' => $ecuador->id, 'nombre' => 'Pichincha'], ['codigo' => '17']);
        $guayas = Provincia::firstOrCreate(['pais_id' => $ecuador->id, 'nombre' => 'Guayas'], ['codigo' => '09']);
        $azuay = Provincia::firstOrCreate(['pais_id' => $ecuador->id, 'nombre' => 'Azuay'], ['codigo' => '01']);
        $manabi = Provincia::firstOrCreate(['pais_id' => $ecuador->id, 'nombre' => 'Manabí'], ['codigo' => '13']);

        // Ciudades
        $quito = Ciudad::firstOrCreate(['provincia_id' => $pichincha->id, 'nombre' => 'Quito']);
        $ruminahui = Ciudad::firstOrCreate(['provincia_id' => $pichincha->id, 'nombre' => 'Rumiñahui']);
        $guayaquil = Ciudad::firstOrCreate(['provincia_id' => $guayas->id, 'nombre' => 'Guayaquil']);
        $samborondon = Ciudad::firstOrCreate(['provincia_id' => $guayas->id, 'nombre' => 'Samborondón']);
        $cuenca = Ciudad::firstOrCreate(['provincia_id' => $azuay->id, 'nombre' => 'Cuenca']);
        $manta = Ciudad::firstOrCreate(['provincia_id' => $manabi->id, 'nombre' => 'Manta']);

        // Provincias y Ciudades Colombia
        $antioquia = Provincia::firstOrCreate(['pais_id' => $colombia->id, 'nombre' => 'Antioquia'], ['codigo' => 'ANT']);
        Ciudad::firstOrCreate(['provincia_id' => $antioquia->id, 'nombre' => 'Medellín']);
        Ciudad::firstOrCreate(['provincia_id' => $antioquia->id, 'nombre' => 'Envigado']);

        // 2. Tipos de Acordes del Core Musical
        $tiposAcordes = [
            [
                'nombre' => 'Mayor',
                'simbolo' => 'M',
                'intervalos_semitonos' => '[0, 4, 7]',
                'categoria' => 'Tríadas',
                'descripcion' => 'Tríada formada por Tónica (0), Tercera mayor (4 semitonos) y Quinta justa (7 semitonos). Sonoridad brillante y estable.',
            ],
            [
                'nombre' => 'Menor',
                'simbolo' => 'm',
                'intervalos_semitonos' => '[0, 3, 7]',
                'categoria' => 'Tríadas',
                'descripcion' => 'Tríada formada por Tónica (0), Tercera menor (3 semitonos) y Quinta justa (7 semitonos). Sonoridad melancólica o introspectiva.',
            ],
            [
                'nombre' => 'Séptima dominante',
                'simbolo' => '7',
                'intervalos_semitonos' => '[0, 4, 7, 10]',
                'categoria' => 'Séptimas',
                'descripcion' => 'Acorde de 4 notas con Tónica (0), Tercera mayor (4), Quinta justa (7) y Séptima menor (10). Tensión armónica que resuelve a tónica.',
            ],
            [
                'nombre' => 'Séptima mayor',
                'simbolo' => 'maj7',
                'intervalos_semitonos' => '[0, 4, 7, 11]',
                'categoria' => 'Séptimas',
                'descripcion' => 'Acorde suave y jazzeado con Tónica (0), Tercera mayor (4), Quinta justa (7) y Séptima mayor (11 semitonos).',
            ],
            [
                'nombre' => 'Séptima menor',
                'simbolo' => 'm7',
                'intervalos_semitonos' => '[0, 3, 7, 10]',
                'categoria' => 'Séptimas',
                'descripcion' => 'Acorde menor con séptima menor añadida [0, 3, 7, 10]. Clave en progresiones ii-V-I.',
            ],
            [
                'nombre' => 'Disminuido',
                'simbolo' => 'dim',
                'intervalos_semitonos' => '[0, 3, 6]',
                'categoria' => 'Tríadas',
                'descripcion' => 'Tríada con dos terceras menores superpuestas [0, 3, 6 semitonos]. Alto grado de inestabilidad y tensión.',
            ],
            [
                'nombre' => 'Aumentado',
                'simbolo' => 'aug',
                'intervalos_semitonos' => '[0, 4, 8]',
                'categoria' => 'Tríadas',
                'descripcion' => 'Tríada formada por dos terceras mayores superpuestas [0, 4, 8 semitonos]. Sonoridad misteriosa y suspendida.',
            ],
            [
                'nombre' => 'Suspendido 4',
                'simbolo' => 'sus4',
                'intervalos_semitonos' => '[0, 5, 7]',
                'categoria' => 'Suspendidos',
                'descripcion' => 'La tercera es sustituida por una cuarta justa (5 semitonos), generando tensión que pide resolver a la tríada mayor o menor.',
            ],
            [
                'nombre' => 'Suspendido 2',
                'simbolo' => 'sus2',
                'intervalos_semitonos' => '[0, 2, 7]',
                'categoria' => 'Suspendidos',
                'descripcion' => 'La tercera es reemplazada por una segunda mayor (2 semitonos). Sonoridad abierta y etérea.',
            ],
        ];

        $modelosAcordes = [];
        foreach ($tiposAcordes as $item) {
            $modelosAcordes[$item['nombre']] = TipoAcorde::firstOrCreate(
                ['nombre' => $item['nombre']],
                $item
            );
        }

        // 3. Usuarios de Demostración
        // Administrador con Cédula válida ecuatoriana verificada por módulo 10
        $admin = User::firstOrCreate(
            ['email' => 'admin@armonialab.test'],
            [
                'name' => 'Administrador General',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'cedula' => '1710034065', // Cédula real válida de Pichincha
                'pais_id' => $ecuador->id,
                'provincia_id' => $pichincha->id,
                'ciudad_id' => $quito->id,
            ]
        );

        // Docente
        $docente = User::firstOrCreate(
            ['email' => 'demo@armonialab.test'],
            [
                'name' => 'Docente Demo',
                'password' => Hash::make('password'),
                'role' => 'docente',
                'cedula' => '0915284392', // Cédula real válida de Guayas
                'pais_id' => $ecuador->id,
                'provincia_id' => $guayas->id,
                'ciudad_id' => $guayaquil->id,
            ]
        );

        // Estudiante para probar el flujo de aprendizaje y recomendaciones
        $estudiante = User::firstOrCreate(
            ['email' => 'estudiante@armonialab.test'],
            [
                'name' => 'Camila Silva (Estudiante)',
                'password' => Hash::make('password'),
                'role' => 'estudiante',
                'cedula' => '0102030408', // Cédula de prueba válida
                'pais_id' => $ecuador->id,
                'provincia_id' => $azuay->id,
                'ciudad_id' => $cuenca->id,
            ]
        );

        // 4. Cursos y Lecciones
        $curso = Course::firstOrCreate(
            ['title' => 'Fundamentos de armonía'],
            [
                'user_id' => $docente->id,
                'description' => 'Curso integral sobre notas, intervalos, escalas y acordes con visualización en teclado.',
                'level' => 'Principiante',
                'status' => 'Publicado',
            ]
        );

        $leccion1 = Leccion::firstOrCreate(
            ['course_id' => $curso->id, 'titulo' => 'Construcción de Tríadas Mayores'],
            [
                'contenido' => 'Las tríadas mayores se componen de tres notas: la nota fundamental, la tercera mayor a 4 semitonos, y la quinta justa a 7 semitonos (fórmula [0, 4, 7]). Ejemplos: Do Mayor = Do - Mi - Sol; Sol Mayor = Sol - Si - Re.',
                'nivel' => 'Principiante',
                'orden' => 1,
                'habilidad' => 'Tríadas Mayores',
            ]
        );

        $leccion2 = Leccion::firstOrCreate(
            ['course_id' => $curso->id, 'titulo' => 'Tríadas Menores y Sonoridad Introspectiva'],
            [
                'contenido' => 'Las tríadas menores tienen la fórmula interválica [0, 3, 7]: fundamental, tercera menor a 3 semitonos y quinta justa a 7 semitonos. Ejemplos: La menor = La - Do - Mi; Re menor = Re - Fa - La.',
                'nivel' => 'Principiante',
                'orden' => 2,
                'habilidad' => 'Tríadas Menores',
            ]
        );

        $leccion3 = Leccion::firstOrCreate(
            ['course_id' => $curso->id, 'titulo' => 'Acordes de Séptima de Dominante'],
            [
                'contenido' => 'La séptima de dominante añade una 7ma menor a la tríada mayor [0, 4, 7, 10]. Es el pilar del sistema tonal mayor y menor para crear tensión y resolución al acorde de tónica.',
                'nivel' => 'Intermedio',
                'orden' => 3,
                'habilidad' => 'Acordes de Séptima',
            ]
        );

        $leccion4 = Leccion::firstOrCreate(
            ['course_id' => $curso->id, 'titulo' => 'Cálculo de Intervalos Armónicos en Semitonos'],
            [
                'contenido' => 'Un intervalo es la distancia entre dos notas. En el sistema temperado occidental de 12 semitonos, cada semitono representa una tecla contigua del teclado.',
                'nivel' => 'Principiante',
                'orden' => 4,
                'habilidad' => 'Intervalos Armónicos',
            ]
        );

        // 5. Ejercicios Interactivos
        $ejercicios = [
            [
                'leccion_id' => $leccion1->id,
                'tipo_acorde_id' => $modelosAcordes['Mayor']->id,
                'pregunta' => '¿Cuáles son las notas integrantes de la tríada de Do Mayor (C)?',
                'tipo' => 'opcion_multiple',
                'habilidad' => 'Tríadas Mayores',
                'dificultad' => 'Fácil',
                'datos_pregunta' => [
                    'opciones' => [
                        'Do - Mi - Sol',
                        'Do - Re# - Sol',
                        'Do - Fa - La',
                        'Re - Fa# - La',
                    ],
                ],
                'respuesta_correcta' => 'Do - Mi - Sol',
                'explicacion' => 'Do Mayor aplica la fórmula [0, 4, 7]: Do (0 st), Mi (4 st, 3ra Mayor) y Sol (7 st, 5ta Justa).',
                'puntos' => 10,
            ],
            [
                'leccion_id' => $leccion1->id,
                'tipo_acorde_id' => $modelosAcordes['Mayor']->id,
                'pregunta' => '¿Cuáles son las notas de la tríada de Fa Mayor (F)?',
                'tipo' => 'opcion_multiple',
                'habilidad' => 'Tríadas Mayores',
                'dificultad' => 'Fácil',
                'datos_pregunta' => [
                    'opciones' => [
                        'Fa - La - Do',
                        'Fa - Sol# - Do',
                        'Fa - La# - Re',
                        'Fa - Do - Mi',
                    ],
                ],
                'respuesta_correcta' => 'Fa - La - Do',
                'explicacion' => 'Fa (tónica) + 4 semitonos = La (tercera mayor) + 3 semitonos (7 st) = Do (quinta justa).',
                'puntos' => 10,
            ],
            [
                'leccion_id' => $leccion2->id,
                'tipo_acorde_id' => $modelosAcordes['Menor']->id,
                'pregunta' => '¿Cuáles son las notas integrantes del acorde de La menor (Am)?',
                'tipo' => 'opcion_multiple',
                'habilidad' => 'Tríadas Menores',
                'dificultad' => 'Fácil',
                'datos_pregunta' => [
                    'opciones' => [
                        'La - Do - Mi',
                        'La - Do# - Mi',
                        'La - Re - Fa',
                        'Sol - Si - Re',
                    ],
                ],
                'respuesta_correcta' => 'La - Do - Mi',
                'explicacion' => 'La menor aplica la fórmula [0, 3, 7]: La (0), Do (3ra menor, 3 semitonos) y Mi (5ta justa, 7 semitonos).',
                'puntos' => 10,
            ],
            [
                'leccion_id' => $leccion2->id,
                'tipo_acorde_id' => $modelosAcordes['Menor']->id,
                'pregunta' => '¿Cuáles notas componen la tríada de Re menor (Dm)?',
                'tipo' => 'opcion_multiple',
                'habilidad' => 'Tríadas Menores',
                'dificultad' => 'Medio',
                'datos_pregunta' => [
                    'opciones' => [
                        'Re - Fa - La',
                        'Re - Fa# - La',
                        'Re - Sol - Si',
                        'Mi - Sol - Si',
                    ],
                ],
                'respuesta_correcta' => 'Re - Fa - La',
                'explicacion' => 'Re menor tiene fundamental Re, 3ra menor Fa (a 3 semitonos) y 5ta justa La (a 7 semitonos).',
                'puntos' => 10,
            ],
            [
                'leccion_id' => $leccion3->id,
                'tipo_acorde_id' => $modelosAcordes['Séptima dominante']->id,
                'pregunta' => '¿Cuáles son las cuatro notas que conforman el acorde de Sol 7 (G7)?',
                'tipo' => 'opcion_multiple',
                'habilidad' => 'Acordes de Séptima',
                'dificultad' => 'Medio',
                'datos_pregunta' => [
                    'opciones' => [
                        'Sol - Si - Re - Fa',
                        'Sol - Si - Re - Fa#',
                        'Sol - Do - Re - Fa',
                        'La - Do# - Mi - Sol',
                    ],
                ],
                'respuesta_correcta' => 'Sol - Si - Re - Fa',
                'explicacion' => 'Sol 7 es una dominante: Sol (0), Si (3ra M / 4 st), Re (5ta J / 7 st) y Fa (7ma m / 10 st).',
                'puntos' => 15,
            ],
            [
                'leccion_id' => $leccion4->id,
                'tipo_acorde_id' => null,
                'pregunta' => '¿Cuántos semitonos de distancia separan la fundamental de una Tercera Menor?',
                'tipo' => 'opcion_multiple',
                'habilidad' => 'Intervalos Armónicos',
                'dificultad' => 'Fácil',
                'datos_pregunta' => [
                    'opciones' => [
                        '3 semitonos',
                        '4 semitonos',
                        '5 semitonos',
                        '7 semitonos',
                    ],
                ],
                'respuesta_correcta' => '3 semitonos',
                'explicacion' => 'La tercera menor equivale a 3 semitonos (tono y medio), mientras que la tercera mayor equivale a 4 semitonos.',
                'puntos' => 10,
            ],
        ];

        $ejerciciosCreados = [];
        foreach ($ejercicios as $ej) {
            $ejerciciosCreados[] = Ejercicio::firstOrCreate(
                ['pregunta' => $ej['pregunta']],
                $ej
            );
        }

        // 6. Registro de Intentos Previos para el Estudiante Demo
        // Para demostrar la recomendación personalizada:
        // Acierta en Tríadas Mayores e Intervalos, pero falla en Tríadas Menores.
        if ($estudiante->intentos()->count() === 0) {
            // Intento 1: Acierto en Do Mayor (Tríadas Mayores)
            Intento::create([
                'user_id' => $estudiante->id,
                'ejercicio_id' => $ejerciciosCreados[0]->id,
                'respuesta_usuario' => 'Do - Mi - Sol',
                'es_correcta' => true,
                'puntaje_obtenido' => 10,
                'explicacion_resultado' => '¡Correcto! Aplicaste correctamente la fórmula [0, 4, 7].',
                'tiempo_segundos' => 14,
                'habilidad' => 'Tríadas Mayores',
            ]);

            // Intento 2: Fallo en Re menor (Tríadas Menores)
            Intento::create([
                'user_id' => $estudiante->id,
                'ejercicio_id' => $ejerciciosCreados[3]->id,
                'respuesta_usuario' => 'Re - Fa# - La',
                'es_correcta' => false,
                'puntaje_obtenido' => 0,
                'explicacion_resultado' => 'Incorrecto. Re - Fa# - La es Re Mayor (tercera mayor a 4 st). Re menor lleva Fa natural a 3 st.',
                'tiempo_segundos' => 22,
                'habilidad' => 'Tríadas Menores',
            ]);

            // Intento 3: Acierto en Intervalos
            Intento::create([
                'user_id' => $estudiante->id,
                'ejercicio_id' => $ejerciciosCreados[5]->id,
                'respuesta_usuario' => '3 semitonos',
                'es_correcta' => true,
                'puntaje_obtenido' => 10,
                'explicacion_resultado' => '¡Exacto! La tercera menor tiene 3 semitonos.',
                'tiempo_segundos' => 9,
                'habilidad' => 'Intervalos Armónicos',
            ]);

            // Progreso de lección
            ProgresoLeccion::create([
                'user_id' => $estudiante->id,
                'leccion_id' => $leccion1->id,
                'estatus' => 'completada',
                'fecha_completado' => now(),
                'intentos_totales' => 1,
                'intentos_correctos' => 1,
            ]);

            ProgresoLeccion::create([
                'user_id' => $estudiante->id,
                'leccion_id' => $leccion2->id,
                'estatus' => 'en_progreso',
                'intentos_totales' => 1,
                'intentos_correctos' => 0,
            ]);
        }
    }
}
