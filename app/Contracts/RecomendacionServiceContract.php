<?php

namespace App\Contracts;

use App\Models\User;

interface RecomendacionServiceContract
{
    /** @return array<string, mixed> */
    public function obtenerDiagnostico(User $user): array;
}
