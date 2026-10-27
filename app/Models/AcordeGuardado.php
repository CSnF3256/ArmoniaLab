<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcordeGuardado extends Model
{
    use HasFactory;

    protected $table = 'acorde_guardados';

    protected $fillable = [
        'user_id',
        'tipo_acorde_id',
        'fundamental',
        'nombre_completo',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'notas' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tipoAcorde(): BelongsTo
    {
        return $this->belongsTo(TipoAcorde::class, 'tipo_acorde_id');
    }
}
