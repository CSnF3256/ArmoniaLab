<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoAcorde extends Model
{
    use HasFactory;

    protected $table = 'tipo_acordes';

    protected $fillable = [
        'nombre',
        'simbolo',
        'intervalos_semitonos',
        'categoria',
        'descripcion',
    ];

    /**
     * Retorna los semitonos como un arreglo de enteros.
     * Ejemplo: "[0, 4, 7]" o "0,4,7" -> [0, 4, 7]
     */
    public function getIntervalosArrayAttribute(): array
    {
        $raw = $this->intervalos_semitonos;
        if (is_array($raw)) {
            return array_map('intval', $raw);
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_map('intval', $decoded);
        }

        $parts = explode(',', str_replace(['[', ']', ' '], '', $raw));
        return array_values(array_filter(array_map('trim', $parts), 'is_numeric'));
    }

    public function acordesGuardados(): HasMany
    {
        return $this->hasMany(AcordeGuardado::class);
    }

    public function ejercicios(): HasMany
    {
        return $this->hasMany(Ejercicio::class);
    }
}
