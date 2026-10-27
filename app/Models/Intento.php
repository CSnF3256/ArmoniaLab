<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Intento extends Model
{
    use HasFactory;

    protected $table = 'intentos';

    protected $fillable = [
        'user_id',
        'ejercicio_id',
        'respuesta_usuario',
        'es_correcta',
        'puntaje_obtenido',
        'explicacion_resultado',
        'tiempo_segundos',
        'habilidad',
    ];

    protected function casts(): array
    {
        return [
            'es_correcta' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ejercicio(): BelongsTo
    {
        return $this->belongsTo(Ejercicio::class);
    }
}
