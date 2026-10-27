<?php

namespace App\Http\Controllers;

use App\Models\AcordeGuardado;
use App\Models\TipoAcorde;
use App\Services\AcordeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcordeController extends Controller
{
    public function __construct(
        protected AcordeService $acordeService
    ) {}

    /**
     * Muestra la interfaz del Core: Constructor y Visualizador de Acordes (PDF Figura 5 y 6).
     */
    public function index(Request $request): View
    {
        $tipos = TipoAcorde::orderBy('categoria')->orderBy('id')->get();
        $notasCromaticas = AcordeService::NOTAS_LATINAS;

        $fundamental = $request->input('fundamental', 'Do');
        $tipoId = $request->input('tipo_id', $tipos->first()?->id);

        $resultado = null;
        $errorCalculo = null;

        if ($fundamental && $tipoId) {
            try {
                $tipoModelo = TipoAcorde::find($tipoId);
                if ($tipoModelo) {
                    $resultado = $this->acordeService->construir($fundamental, $tipoModelo);
                } else {
                    $errorCalculo = 'El tipo de acorde seleccionado no existe en el catálogo.';
                }
            } catch (\Exception $e) {
                $errorCalculo = $e->getMessage();
            }
        }

        return view('acordes.index', [
            'tipos' => $tipos,
            'notasCromaticas' => $notasCromaticas,
            'fundamentalSeleccionada' => $fundamental,
            'tipoSeleccionado' => $tipoId,
            'resultado' => $resultado,
            'errorCalculo' => $errorCalculo,
        ]);
    }

    /**
     * Procesa la solicitud de cálculo y visualización con validación estricta en Back-End.
     */
    public function calcular(Request $request)
    {
        $validated = $request->validate([
            'fundamental' => ['required', 'string', 'in:' . implode(',', AcordeService::NOTAS_LATINAS)],
            'tipo_id' => ['required', 'integer', 'exists:tipo_acordes,id'],
        ], [
            'fundamental.required' => 'Debe seleccionar una nota fundamental.',
            'fundamental.in' => 'La nota fundamental no corresponde a una clase cromática válida.',
            'tipo_id.required' => 'Debe seleccionar un tipo de acorde.',
            'tipo_id.exists' => 'El tipo de acorde no existe en la base de datos.',
        ]);

        $tipoModelo = TipoAcorde::findOrFail($validated['tipo_id']);
        $resultado = $this->acordeService->construir($validated['fundamental'], $tipoModelo);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'resultado' => $resultado,
            ]);
        }

        return redirect()->route('acordes.index', [
            'fundamental' => $validated['fundamental'],
            'tipo_id' => $validated['tipo_id'],
        ]);
    }

    /**
     * Guarda el acorde en la biblioteca personal del estudiante (PDF Figura 6 y 7).
     */
    public function guardar(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fundamental' => ['required', 'string', 'in:' . implode(',', AcordeService::NOTAS_LATINAS)],
            'tipo_id' => ['required', 'integer', 'exists:tipo_acordes,id'],
        ]);

        $tipoModelo = TipoAcorde::findOrFail($validated['tipo_id']);
        $calculo = $this->acordeService->construir($validated['fundamental'], $tipoModelo);

        AcordeGuardado::firstOrCreate([
            'user_id' => auth()->id(),
            'tipo_acorde_id' => $tipoModelo->id,
            'fundamental' => $calculo['fundamental'],
        ], [
            'nombre_completo' => $calculo['nombre_completo'],
            'notas' => $calculo['notas'],
        ]);

        return redirect()->route('acordes.biblioteca')
            ->with('success', "El acorde {$calculo['nombre_completo']} se guardó correctamente en tu biblioteca personal.");
    }

    /**
     * Muestra la biblioteca de acordes guardados del estudiante (PDF Figura 7).
     */
    public function biblioteca(): View
    {
        $acordes = auth()->user()->acordesGuardados()->with('tipoAcorde')->latest()->get();

        return view('acordes.biblioteca', [
            'acordes' => $acordes,
        ]);
    }

    /**
     * Elimina un acorde de la biblioteca del usuario.
     */
    public function destroy(AcordeGuardado $acorde): RedirectResponse
    {
        if ($acorde->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para eliminar este acorde.');
        }

        $nombre = $acorde->nombre_completo;
        $acorde->delete();

        return redirect()->route('acordes.biblioteca')
            ->with('success', "Acorde '{$nombre}' eliminado de tu biblioteca.");
    }
}
