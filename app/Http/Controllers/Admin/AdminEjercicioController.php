<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEjercicioRequest;
use App\Http\Requests\Admin\UpdateEjercicioRequest;
use App\Models\Course;
use App\Models\Ejercicio;
use App\Models\TipoAcorde;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminEjercicioController extends Controller
{
    /**
     * Listado de ejercicios administrativos.
     */
    public function index(): View
    {
        $ejercicios = Ejercicio::with(['leccion.course', 'tipoAcorde'])->latest()->paginate(15);

        return view('admin.ejercicios.index', [
            'ejercicios' => $ejercicios,
        ]);
    }

    /**
     * Formulario de creación de ejercicios con DROPDOWNS DEPENDIENTES (Curso -> Lección).
     */
    public function create(): View
    {
        $cursos = Course::orderBy('title')->get();
        $tiposAcordes = TipoAcorde::orderBy('nombre')->get();
        $modalidades = Ejercicio::modalidades();

        return view('admin.ejercicios.create', [
            'cursos' => $cursos,
            'tiposAcordes' => $tiposAcordes,
            'modalidades' => $modalidades,
        ]);
    }

    /**
     * Guarda el ejercicio asegurando la relación en cascada Curso -> Lección.
     */
    public function store(StoreEjercicioRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Opciones: construccion_visual y progresion_funcional pueden tener más de 4 notas/acordes
        $opciones = array_values(array_filter([
            $validated['opcion_1'] ?? null,
            $validated['opcion_2'] ?? null,
            $validated['opcion_3'] ?? null,
            $validated['opcion_4'] ?? null,
        ], fn ($v) => ! is_null($v) && $v !== ''));

        // Metadatos opcionales de modalidad (pista, tiempo, reglas armónicas, contexto)
        $metadata = array_filter([
            'pista' => $validated['metadata_pista'] ?? null,
            'tiempo_limite' => isset($validated['metadata_tiempo_limite']) ? (int) $validated['metadata_tiempo_limite'] : null,
            'reglas' => $validated['metadata_reglas'] ?? null,
            'contexto' => $validated['metadata_contexto'] ?? null,
            'audio_captura' => $request->boolean('audio_captura') ? true : null,
            'audio_notas_objetivo' => $request->boolean('audio_captura')
                ? $this->normalizarNotasDeAudio($validated['audio_notas_objetivo'])
                : null,
        ], fn ($v) => ! is_null($v) && $v !== '');

        // Para progresion_funcional, almacenar también las reglas en datos_pregunta para acceso desde la vista
        $datosPregunta = ['opciones' => $opciones];
        if (! empty($metadata['reglas'])) {
            $datosPregunta['reglas'] = $metadata['reglas'];
        }
        if (! empty($metadata['contexto'])) {
            $datosPregunta['contexto'] = $metadata['contexto'];
        }

        Ejercicio::create([
            'leccion_id' => $validated['leccion_id'],
            'tipo_acorde_id' => $validated['tipo_acorde_id'] ?? null,
            'pregunta' => $validated['pregunta'],
            'tipo' => $validated['tipo'],
            'modalidad' => $validated['modalidad'],
            'habilidad' => $validated['habilidad'],
            'dificultad' => $validated['dificultad'],
            'datos_pregunta' => $datosPregunta,
            'metadata' => empty($metadata) ? null : $metadata,
            'respuesta_correcta' => $validated['respuesta_correcta'],
            'explicacion' => $validated['explicacion'] ?? null,
            'puntos' => $validated['puntos'],
        ]);

        return redirect()->route('admin.ejercicios.index')
            ->with('success', 'Ejercicio interactivo creado exitosamente con relación Curso-Lección vinculada.');
    }

    /**
     * Muestra el formulario de edición manteniendo la cascada Curso -> Lección.
     */
    public function edit(Ejercicio $ejercicio): View
    {
        $ejercicio->load('leccion.course');

        return view('admin.ejercicios.edit', [
            'ejercicio' => $ejercicio,
            'cursos' => Course::orderBy('title')->get(),
            'tiposAcordes' => TipoAcorde::orderBy('nombre')->get(),
            'modalidades' => Ejercicio::modalidades(),
        ]);
    }

    /**
     * Actualiza un ejercicio validando en Back-End la relación Curso -> Lección.
     */
    public function update(UpdateEjercicioRequest $request, Ejercicio $ejercicio): RedirectResponse
    {
        $validated = $request->validated();

        $ejercicio->update([
            'leccion_id' => $validated['leccion_id'],
            'tipo_acorde_id' => $validated['tipo_acorde_id'] ?? null,
            'pregunta' => $validated['pregunta'],
            'tipo' => $validated['tipo'],
            'modalidad' => $validated['modalidad'],
            'habilidad' => $validated['habilidad'],
            'dificultad' => $validated['dificultad'],
            'datos_pregunta' => [
                'opciones' => array_values(array_filter([
                    $validated['opcion_1'],
                    $validated['opcion_2'],
                    $validated['opcion_3'] ?? null,
                    $validated['opcion_4'] ?? null,
                ], fn ($opcion) => ! is_null($opcion) && $opcion !== '')),
            ],
            'metadata' => array_filter([
                'pista' => $validated['metadata_pista'] ?? null,
                'tiempo_limite' => isset($validated['metadata_tiempo_limite']) ? (int) $validated['metadata_tiempo_limite'] : null,
                'reglas' => $validated['metadata_reglas'] ?? null,
                'contexto' => $validated['metadata_contexto'] ?? null,
                'audio_captura' => $request->boolean('audio_captura') ? true : null,
                'audio_notas_objetivo' => $request->boolean('audio_captura')
                    ? $this->normalizarNotasDeAudio($validated['audio_notas_objetivo'])
                    : null,
            ], fn ($v) => ! is_null($v)) ?: null,
            'respuesta_correcta' => $validated['respuesta_correcta'],
            'explicacion' => $validated['explicacion'] ?? null,
            'puntos' => $validated['puntos'],
        ]);

        return redirect()->route('admin.ejercicios.index')
            ->with('success', 'Ejercicio actualizado correctamente y relaciones verificadas en Back-End.');
    }

    /**
     * Elimina un ejercicio.
     */
    public function destroy(Ejercicio $ejercicio): RedirectResponse
    {
        $ejercicio->delete();

        return redirect()->route('admin.ejercicios.index')
            ->with('success', 'Ejercicio eliminado exitosamente.');
    }

    /** @return array<int, string> */
    private function normalizarNotasDeAudio(string $value): array
    {
        $nombres = [
            'do' => 'Do', 'do#' => 'Do#', 'reb' => 'Reb', 're' => 'Re', 're#' => 'Re#', 'mib' => 'Mib',
            'mi' => 'Mi', 'fa' => 'Fa', 'fa#' => 'Fa#', 'solb' => 'Solb', 'sol' => 'Sol',
            'sol#' => 'Sol#', 'lab' => 'Lab', 'la' => 'La', 'la#' => 'La#', 'sib' => 'Sib', 'si' => 'Si',
        ];

        return array_values(array_filter(array_map(function (string $nota) use ($nombres): string {
            $nota = strtolower(trim($nota));

            if (preg_match('/^([a-z]+#?)([2-6])$/u', $nota, $coincidencia) && isset($nombres[$coincidencia[1]])) {
                return $nombres[$coincidencia[1]].$coincidencia[2];
            }

            return ucfirst($nota);
        }, explode(',', $value))));
    }
}
