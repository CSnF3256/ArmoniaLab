<?php

namespace App\Services;

use App\Contracts\TipoAcordeCatalogContract;
use App\Models\TipoAcorde;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;

final class TipoAcordeCatalogService implements TipoAcordeCatalogContract
{
    public const CACHE_KEY = 'catalogo.tipo_acordes';

    private const CACHE_TTL_SECONDS = 3600;

    public function __construct(
        private readonly CacheRepository $cache,
    ) {}

    public function all(): Collection
    {
        return $this->cache->remember(
            self::CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            fn (): Collection => TipoAcorde::query()
                ->orderBy('categoria')
                ->orderBy('id')
                ->get(),
        );
    }

    public function find(int $id): ?TipoAcorde
    {
        return $this->all()->firstWhere('id', $id);
    }

    public function forget(): void
    {
        $this->cache->forget(self::CACHE_KEY);
    }
}
