<?php

namespace App\Services;

use App\Contracts\TipoAcordeCatalogContract;
use App\Models\TipoAcorde;
use Illuminate\Support\Collection;

final class TipoAcordeCatalogService implements TipoAcordeCatalogContract
{
    public function all(): Collection
    {
        return TipoAcorde::query()
            ->orderBy('categoria')
            ->orderBy('id')
            ->get();
    }

    public function find(int $id): ?TipoAcorde
    {
        return $this->all()->firstWhere('id', $id);
    }

    public function forget(): void
    {
        // La invalidación se implementa al incorporar Cache-Aside.
    }
}
