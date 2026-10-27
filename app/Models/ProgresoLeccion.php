<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgresoLeccion extends Model
{
    use HasFactory;

    protected $table = 'progreso_lecciones';

    protected $fillable = [
        'user_id',
        'leccion_id',
        'estatus',
        'fecha_completado',
        'intentos_totales',
        'intentos_correctos',
    ];

    protected function casts(): array
    {
        return [
            'fecha_completado' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leccion(): BelongsTo
    {
        return $this->belongsTo(Leccion::class);
    }

    public function getPorcentajeAciertosAttribute(): float
    {
        if ($this->intentos_totales <= 0) {
            return 0.0;
        }
        return round(($this->intentos_correctos / $this->intentos_totales) * 100, 1);
    }
}
