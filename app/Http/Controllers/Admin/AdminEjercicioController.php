<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEjercicioRequest;
use App\Http\Requests\Admin\UpdateEjercicioRequest;
use App\Models\Course;
use App\Models\Ejercicio;
use App\Models\TipoAcorde;
use App\Services\EjercicioMetadataBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminEjercicioController extends Controller
{
    public function __construct(
        private readonly EjercicioMetadataBuilder $metadataBuilder,
    ) {}

    /**
     * Listado de ejercicios administrativos.
     */
    public function index(Request $request): View
    {
        $habilidadSeleccionada = $request->input('habilidad');
        $query = Ejercicio::with(['leccion.course', 'tipoAcorde'])->latest();

        if ($habilidadSeleccionada) {
            $query->where('habilidad', $habilidadSeleccionada);
        }

        $ejercicios = $query->paginate(9)->withQueryString();

        return view('admin.ejercicios.index', [
            'ejercicios' => $ejercicios,
            'habilidades' => Ejercicio::query()->distinct()->pluck('habilidad'),
            'habilidadSeleccionada' => $habilidadSeleccionada,
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
        $metadata = $this->metadataBuilder->build($validated, $request->boolean('audio_captura'));
        $datosPregunta = $this->metadataBuilder->buildQuestionData($validated, $metadata);

        Ejercicio::create([
            'leccion_id' => $validated['leccion_id'],
            'tipo_acorde_id' => $validated['tipo_acorde_id'] ?? null,
            'pregunta' => $validated['pregunta'],
            'tipo' => $validated['tipo'],
            'modalidad' => $validated['modalidad'],
            'habilidad' => $validated['habilidad'],
            'dificultad' => $validated['dificultad'],
            'datos_pregunta' => $datosPregunta,
            'metadata' => $metadata,
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
        $metadata = $this->metadataBuilder->build($validated, $request->boolean('audio_captura'));

        $ejercicio->update([
            'leccion_id' => $validated['leccion_id'],
            'tipo_acorde_id' => $validated['tipo_acorde_id'] ?? null,
            'pregunta' => $validated['pregunta'],
            'tipo' => $validated['tipo'],
            'modalidad' => $validated['modalidad'],
            'habilidad' => $validated['habilidad'],
            'dificultad' => $validated['dificultad'],
            'datos_pregunta' => $this->metadataBuilder->buildQuestionData($validated, $metadata),
            'metadata' => $metadata,
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

}
