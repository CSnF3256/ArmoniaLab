<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Ejercicio;
use App\Models\TipoAcorde;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        return view('admin.ejercicios.create', [
            'cursos' => $cursos,
            'tiposAcordes' => $tiposAcordes,
        ]);
    }

    /**
     * Guarda el ejercicio asegurando la relación en cascada Curso -> Lección.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'leccion_id' => [
                'required',
                'integer',
                Rule::exists('lecciones', 'id')->where(fn ($query) => $query->where('course_id', $request->input('course_id'))),
            ],
            'tipo_acorde_id' => ['nullable', 'integer', 'exists:tipo_acordes,id'],
            'pregunta' => ['required', 'string'],
            'tipo' => ['required', 'string', 'in:opcion_multiple,construir_acorde,identificar_notas,intervalo'],
            'habilidad' => ['required', 'string', 'max:100'],
            'dificultad' => ['required', 'string', 'in:Fácil,Medio,Avanzado'],
            'opcion_1' => ['required', 'string'],
            'opcion_2' => ['required', 'string'],
            'opcion_3' => ['required', 'string'],
            'opcion_4' => ['required', 'string'],
            'respuesta_correcta' => ['required', 'string'],
            'explicacion' => ['nullable', 'string'],
            'puntos' => ['required', 'integer', 'min:1', 'max:100'],
        ], [
            'course_id.required' => 'Debe seleccionar un curso desde el dropdown.',
            'leccion_id.required' => 'Debe seleccionar una lección dependiente del curso.',
            'leccion_id.exists' => 'La lección elegida no pertenece al curso seleccionado.',
            'respuesta_correcta.required' => 'La respuesta correcta es obligatoria.',
        ]);

        $opciones = [
            $validated['opcion_1'],
            $validated['opcion_2'],
            $validated['opcion_3'],
            $validated['opcion_4'],
        ];

        Ejercicio::create([
            'leccion_id' => $validated['leccion_id'],
            'tipo_acorde_id' => $validated['tipo_acorde_id'] ?? null,
            'pregunta' => $validated['pregunta'],
            'tipo' => $validated['tipo'],
            'habilidad' => $validated['habilidad'],
            'dificultad' => $validated['dificultad'],
            'datos_pregunta' => ['opciones' => $opciones],
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
        ]);
    }

    /**
     * Actualiza un ejercicio validando en Back-End la relación Curso -> Lección.
     */
    public function update(Request $request, Ejercicio $ejercicio): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'leccion_id' => [
                'required',
                'integer',
                Rule::exists('lecciones', 'id')->where(fn ($query) => $query->where('course_id', $request->input('course_id'))),
            ],
            'tipo_acorde_id' => ['nullable', 'integer', 'exists:tipo_acordes,id'],
            'pregunta' => ['required', 'string'],
            'tipo' => ['required', 'string', 'in:opcion_multiple,construir_acorde,identificar_notas,intervalo'],
            'habilidad' => ['required', 'string', 'max:100'],
            'dificultad' => ['required', 'string', 'in:Fácil,Medio,Avanzado'],
            'opcion_1' => ['required', 'string'],
            'opcion_2' => ['required', 'string'],
            'opcion_3' => ['required', 'string'],
            'opcion_4' => ['required', 'string'],
            'respuesta_correcta' => ['required', 'string'],
            'explicacion' => ['nullable', 'string'],
            'puntos' => ['required', 'integer', 'min:1', 'max:100'],
        ], [
            'leccion_id.exists' => 'La lección elegida no pertenece al curso seleccionado.',
        ]);

        $ejercicio->update([
            'leccion_id' => $validated['leccion_id'],
            'tipo_acorde_id' => $validated['tipo_acorde_id'] ?? null,
            'pregunta' => $validated['pregunta'],
            'tipo' => $validated['tipo'],
            'habilidad' => $validated['habilidad'],
            'dificultad' => $validated['dificultad'],
            'datos_pregunta' => [
                'opciones' => [
                    $validated['opcion_1'],
                    $validated['opcion_2'],
                    $validated['opcion_3'],
                    $validated['opcion_4'],
                ],
            ],
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
