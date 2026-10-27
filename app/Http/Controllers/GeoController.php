<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Pais;
use App\Models\Provincia;
use Illuminate\Http\JsonResponse;

class GeoController extends Controller
{
    /**
     * Retorna las provincias asociadas a un país para alimentar el dropdown dependiente.
     */
    public function provincias(Pais $pais): JsonResponse
    {
        $provincias = $pais->provincias()->select('id', 'nombre')->orderBy('nombre')->get();
        return response()->json($provincias);
    }

    /**
     * Retorna las ciudades asociadas a una provincia para alimentar el dropdown dependiente.
     */
    public function ciudades(Provincia $provincia): JsonResponse
    {
        $ciudades = $provincia->ciudades()->select('id', 'nombre')->orderBy('nombre')->get();
        return response()->json($ciudades);
    }

    /**
     * Retorna las lecciones asociadas a un curso para alimentar el dropdown dependiente
     * en el formulario de creación de ejercicios del Admin.
     */
    public function leccionesDeCurso(Course $course): JsonResponse
    {
        $lecciones = $course->lecciones()->select('id', 'titulo', 'habilidad')->orderBy('orden')->get();
        return response()->json($lecciones);
    }
}
