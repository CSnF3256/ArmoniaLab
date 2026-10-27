<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Leccion extends Model
{
    use HasFactory;

    protected $table = 'lecciones';

    protected $fillable = [
        'course_id',
        'titulo',
        'contenido',
        'nivel',
        'orden',
        'habilidad',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function ejercicios(): HasMany
    {
        return $this->hasMany(Ejercicio::class);
    }

    public function progresos(): HasMany
    {
        return $this->hasMany(ProgresoLeccion::class);
    }
}
