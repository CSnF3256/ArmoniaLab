<?php

namespace App\Contracts;

use App\Models\TipoAcorde;
use Illuminate\Support\Collection;

interface TipoAcordeCatalogContract
{
    /** @return Collection<int, TipoAcorde> */
    public function all(): Collection;

    public function find(int $id): ?TipoAcorde;

    public function forget(): void;
}
