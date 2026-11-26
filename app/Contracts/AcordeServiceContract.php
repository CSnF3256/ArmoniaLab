<?php

namespace App\Contracts;

use App\Models\TipoAcorde;

interface AcordeServiceContract
{
    /** @return array<int, string> */
    public function availableNotes(): array;

    /** @return array<string, mixed> */
    public function construir(string $fundamental, TipoAcorde|int|array $tipoAcorde): array;
}
