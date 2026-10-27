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
        'habilidad',
        'dificultad',
        'datos_pregunta',
        'respuesta_correcta',
        'explicacion',
        'puntos',
    ];

    protected function casts(): array
    {
        return [
            'datos_pregunta' => 'array',
        ];
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
