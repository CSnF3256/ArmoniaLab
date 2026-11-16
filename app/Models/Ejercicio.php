<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ejercicio extends Model
{
    use HasFactory;

    protected $table = 'ejercicios';

    protected $fillable = [
        'leccion_id',
        'tipo_acorde_id',
        'pregunta',
        'tipo',
        'modalidad',
        'habilidad',
        'dificultad',
        'datos_pregunta',
        'metadata',
        'respuesta_correcta',
        'explicacion',
        'puntos',
    ];

    protected function casts(): array
    {
        return [
            'datos_pregunta' => 'array',
            'metadata'       => 'array',
        ];
    }

    /**
     * Lista canónica de modalidades disponibles con su etiqueta descriptiva.
     * Se usa en validaciones y en las vistas de administración.
     */
    public static function modalidades(): array
    {
        return [
            'clasica'               => 'Clásica (opción múltiple estándar)',
            'completar_secuencia'   => 'Completar secuencia de notas',
            'seleccionar_intervalo' => 'Seleccionar intervalo correcto',
            'verdadero_falso'       => 'Verdadero o Falso',
            'ordenar_notas'         => 'Ordenar notas de menor a mayor',
            'detectar_error'        => 'Detectar la nota errónea en el acorde',
            'construccion_visual'   => 'Construcción Visual (Drag & Drop)',
            'progresion_funcional'  => 'Progresiones Funcionales (tablero)',
            'sincronia_ritmica'     => 'Sincronía Rítmica (Metrónomo)',
        ];
    }

    /**
     * Evalúa un payload interactivo (drag-drop o progresión) desde el back-end.
     * Devuelve [bool $esCorrecta, string $explicacion].
     */
    public function evaluarRespuesta(string $respuestaUsuario): array
    {
        $normalizar = fn (string $s) => mb_strtolower(preg_replace('/\s+/', ' ', trim($s)));

        $esCorrecta = $normalizar($respuestaUsuario) === $normalizar($this->respuesta_correcta);

        $explicacion = $esCorrecta
            ? '¡Respuesta correcta! ' . ($this->explicacion ?? 'Has aplicado correctamente las reglas armónicas.')
            : 'Respuesta incorrecta. ' . ($this->explicacion ?? "La respuesta esperada era: {$this->respuesta_correcta}.");

        return [$esCorrecta, $explicacion];
    }

    public function leccion(): BelongsTo
    {
        return $this->belongsTo(Leccion::class);
    }

    public function tipoAcorde(): BelongsTo
    {
        return $this->belongsTo(TipoAcorde::class, 'tipo_acorde_id');
    }

    public function intentos(): HasMany
    {
        return $this->hasMany(Intento::class);
    }
}