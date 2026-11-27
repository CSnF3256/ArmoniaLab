<?php

namespace Tests\Feature;

use App\Contracts\TipoAcordeCatalogContract;
use App\Models\TipoAcorde;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TipoAcordeCacheTest extends TestCase
{
    use RefreshDatabase;

    private TipoAcordeCatalogContract $catalog;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->catalog = app(TipoAcordeCatalogContract::class);
        $this->catalog->forget();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_reutiliza_el_catalogo_hasta_invalidar_la_clave(): void
    {
        $this->createType('Mayor en caché', 'M');

        $firstRead = $this->catalog->all();
        $this->createType('Menor todavía no visible', 'm');
        $cachedRead = $this->catalog->all();

        $this->assertCount(1, $firstRead);
        $this->assertCount(1, $cachedRead);
        $this->assertSame('Mayor en caché', $cachedRead->first()->nombre);

        $this->catalog->forget();

        $this->assertCount(2, $this->catalog->all());
    }

    public function test_crear_tipo_de_acorde_invalida_el_catalogo(): void
    {
        $this->createType('Existente', 'E');
        $this->assertCount(1, $this->catalog->all());

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tipos-acordes.store'), [
                'nombre' => 'Séptima de caché',
                'simbolo' => '7c',
                'categoria' => 'Séptimas',
                'intervalos_semitonos' => '0, 4, 7, 10',
                'descripcion' => 'Creado después de llenar el caché.',
            ]);

        $response->assertRedirect(route('admin.tipos-acordes.index'));
        $this->assertTrue($this->catalog->all()->contains('nombre', 'Séptima de caché'));
    }

    public function test_actualizar_y_eliminar_invalidan_el_catalogo(): void
    {
        $type = $this->createType('Nombre anterior', 'old');
        $this->catalog->all();

        $this->actingAs($this->admin)
            ->put(route('admin.tipos-acordes.update', $type), [
                'nombre' => 'Nombre actualizado',
                'simbolo' => 'new',
                'categoria' => 'Tríadas',
                'intervalos_semitonos' => '0, 3, 7',
                'descripcion' => 'Actualizado después de llenar el caché.',
            ])
            ->assertRedirect(route('admin.tipos-acordes.index'));

        $this->assertSame('Nombre actualizado', $this->catalog->find($type->id)?->nombre);

        $this->actingAs($this->admin)
            ->delete(route('admin.tipos-acordes.destroy', $type))
            ->assertRedirect(route('admin.tipos-acordes.index'));

        $this->assertNull($this->catalog->find($type->id));
    }

    private function createType(string $name, string $symbol): TipoAcorde
    {
        return TipoAcorde::create([
            'nombre' => $name,
            'simbolo' => $symbol,
            'categoria' => 'Tríadas',
            'intervalos_semitonos' => '[0, 4, 7]',
            'descripcion' => 'Tipo utilizado por las pruebas de caché.',
        ]);
    }
}
