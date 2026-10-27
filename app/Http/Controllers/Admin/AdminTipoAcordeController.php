<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoAcorde;
use App\Rules\ValidIntervalStructure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTipoAcordeController extends Controller
{
    /**
     * Catálogo de tipos de acordes para administración.
     */
    public function index(): View
    {
        $tipos = TipoAcorde::orderBy('categoria')->orderBy('nombre')->get();

        return view('admin.tipos_acordes.index', [
            'tipos' => $tipos,
        ]);
    }

    /**
     * Formulario de creación de tipo de acorde.
     */
    public function create(): View
    {
        return view('admin.tipos_acordes.create');
    }

    /**
     * Guarda el tipo de acorde tras realizar la VALIDACIÓN EN BACK-END DEL DATO SENSIBLE DEL CORE
     * (Estructura de intervalos en semitonos). No se puede validar solo en JS.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:tipo_acordes,nombre'],
            'simbolo' => ['required', 'string', 'max:20'],
            'categoria' => ['required', 'string', 'in:Tríadas,Séptimas,Suspendidos,Alterados,Extendidos'],
            'descripcion' => ['nullable', 'string'],

            // VALIDACIÓN EN BACK-END DEL DATO SENSIBLE DEL CORE
            'intervalos_semitonos' => [
                'required',
                'string',
                new ValidIntervalStructure(),
            ],
        ], [
            'intervalos_semitonos.required' => 'La fórmula de intervalos en semitonos es obligatoria para el cálculo armónico.',
            'nombre.unique' => 'Este tipo de acorde ya existe en el catálogo.',
        ]);

        // Formatear a formato estándar [0, 4, 7]
        $limpio = str_replace(['[', ']', ' '], '', $validated['intervalos_semitonos']);
        $array = array_map('intval', explode(',', $limpio));
        $formateado = '[' . implode(', ', $array) . ']';

        TipoAcorde::create([
            'nombre' => $validated['nombre'],
            'simbolo' => $validated['simbolo'],
            'categoria' => $validated['categoria'],
            'intervalos_semitonos' => $formateado,
            'descripcion' => $validated['descripcion'] ?? null,
        ]);

        return redirect()->route('admin.tipos-acordes.index')
            ->with('success', "Tipo de acorde '{$validated['nombre']}' registrado y validado en Back-End exitosamente.");
    }

    /**
     * Elimina un tipo de acorde si no tiene ejercicios asociados.
     */
    public function destroy(TipoAcorde $tipoAcorde): RedirectResponse
    {
        $nombre = $tipoAcorde->nombre;
        $tipoAcorde->delete();

        return redirect()->route('admin.tipos-acordes.index')
            ->with('success', "Tipo de acorde '{$nombre}' eliminado.");
    }
}
