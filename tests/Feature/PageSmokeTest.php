<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SampleDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DatabaseSeeder::class, SampleDataSeeder::class]);
    }

    /**
     * @return array<int, string>
     */
    private function pageUrls(): array
    {
        $vehicle = Vehicle::firstOrFail();
        $material = Material::firstOrFail();

        return [
            route('dashboard'),
            route('api.db'),
            route('vehicles.index'),
            route('vehicles.create'),
            route('vehicles.print_register'),
            route('vehicles.show', $vehicle->id),
            route('vehicles.edit', $vehicle->id),
            route('vehicles.print', $vehicle->id),
            route('materials.index'),
            route('materials.print'),
            route('materials.issuance'),
            route('materials.issuance.print'),
            route('materials.restock'),
            route('materials.restock.print'),
            route('materials.restock_needed'),
            route('materials.restock_needed.print'),
            route('materials.safety_stock'),
            route('materials.safety_stock.print'),
            route('materials.safety_issuance.print'),
            route('materials.movements', $material->id),
            route('supervisors.index'),
            route('supervisors.print'),
            route('tools.index'),
            route('tools.print'),
            route('users.index'),
        ];
    }

    public function test_no_page_throws_a_server_error_for_any_role(): void
    {
        $failures = [];

        foreach (['Admin', 'Manager', 'Storekeeper', 'Accountant'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            foreach ($this->pageUrls() as $url) {
                $status = $this->actingAs($user)->get($url)->getStatusCode();

                if ($status >= 500) {
                    $failures[] = "{$role} {$url} => {$status}";
                }
            }
        }

        $this->assertSame([], $failures);
    }
}
