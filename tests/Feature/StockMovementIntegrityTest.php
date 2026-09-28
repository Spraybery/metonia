<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialMovement;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehiclePart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private User $storekeeper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storekeeper = User::factory()->create(['role' => 'Storekeeper']);
    }

    private function createMaterial(float $qty = 0): Material
    {
        return Material::create([
            'item_code' => 'MAT-0500',
            'name' => 'Steel Sheet 2mm',
            'category' => 'Metals',
            'unit' => 'Pieces',
            'qty' => $qty,
            'low_stock' => 5,
            'unit_cost' => 1000,
            'supplier' => 'Old Supplier Ltd',
        ]);
    }

    private function receiveRestock(Material $material, float $qty): MaterialMovement
    {
        $this->actingAs($this->storekeeper)->post(route('materials.movement', $material->id), [
            'type' => 'in',
            'qty' => $qty,
            'date' => now()->toDateString(),
            'person' => 'Store Keeper',
            'supplier' => 'Old Supplier Ltd',
        ])->assertSessionHas('flash_success');

        return MaterialMovement::where('type', 'in')->latest('id')->firstOrFail();
    }

    public function test_editing_a_restock_saves_the_supplier(): void
    {
        $material = $this->createMaterial();
        $restock = $this->receiveRestock($material, 10);

        $this->actingAs($this->storekeeper)->put(route('materials.movement.update', $restock->id), [
            'supplier' => 'New Supplier Ltd',
            'qty' => 10,
            'date' => now()->toDateString(),
            'issued_by' => 'Store Keeper',
        ])->assertSessionHas('flash_success', 'Restock record updated successfully.');

        $this->assertSame('New Supplier Ltd', $material->fresh()->supplier);
    }

    public function test_reducing_a_restock_below_what_is_still_in_stock_is_blocked(): void
    {
        $material = $this->createMaterial();
        $restock = $this->receiveRestock($material, 10);
        $material->update(['qty' => 3]);

        $this->actingAs($this->storekeeper)->put(route('materials.movement.update', $restock->id), [
            'qty' => 2,
            'date' => now()->toDateString(),
        ])->assertSessionHas('flash_danger');

        $this->assertEquals(3, (float) $material->fresh()->qty);
        $this->assertEquals(10, (float) $restock->fresh()->qty);
    }

    public function test_deleting_a_restock_that_was_already_issued_is_blocked(): void
    {
        $material = $this->createMaterial();
        $restock = $this->receiveRestock($material, 10);
        $material->update(['qty' => 4]);

        $this->actingAs($this->storekeeper)
            ->delete(route('materials.movement.destroy', $restock->id))
            ->assertSessionHas('flash_danger');

        $this->assertModelExists($restock);
        $this->assertEquals(4, (float) $material->fresh()->qty);
    }

    public function test_deleting_a_restock_still_fully_in_stock_reverts_it(): void
    {
        $material = $this->createMaterial(5);
        $restock = $this->receiveRestock($material, 10);

        $this->actingAs($this->storekeeper)
            ->delete(route('materials.movement.destroy', $restock->id))
            ->assertSessionHas('flash_success', 'Restock record deleted and store stock reverted.');

        $this->assertModelMissing($restock);
        $this->assertEquals(5, (float) $material->fresh()->qty);
    }

    public function test_issuing_more_than_is_in_stock_is_rejected(): void
    {
        $material = $this->createMaterial(2);

        $this->actingAs($this->storekeeper)->post(route('materials.movement', $material->id), [
            'type' => 'out',
            'qty' => 5,
            'date' => now()->toDateString(),
            'issued_to' => 'Technician',
        ])->assertSessionHas('flash_danger');

        $this->assertEquals(2, (float) $material->fresh()->qty);
        $this->assertSame(0, MaterialMovement::where('type', 'out')->count());
    }

    public function test_material_issued_to_a_job_card_cannot_be_deleted_and_shows_a_message(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $material = $this->createMaterial(10);
        $vehicle = Vehicle::create(['plate' => 'MET-DEL-1', 'make' => 'Isuzu', 'model' => 'NQR', 'stage' => '1. Intake & Diagnosis', 'intake_date' => now()]);

        $this->actingAs($admin)->post(route('vehicles.issue_part', $vehicle->id), [
            'material_id' => $material->id,
            'qty' => 2,
            'person' => 'Tech A',
        ])->assertSessionHas('flash_success');

        $this->actingAs($admin)
            ->delete(route('materials.destroy', $material->id))
            ->assertSessionHas('flash_danger');

        $this->assertModelExists($material);
        $this->assertSame(1, VehiclePart::count());
    }

    public function test_part_issued_from_a_job_card_records_who_issued_it(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        $material = $this->createMaterial(10);
        $vehicle = Vehicle::create(['plate' => 'MET-ISS-1', 'make' => 'Isuzu', 'model' => 'NQR', 'stage' => '1. Intake & Diagnosis', 'intake_date' => now()]);

        $this->actingAs($manager)->post(route('vehicles.issue_part', $vehicle->id), [
            'material_id' => $material->id,
            'qty' => 3,
            'person' => 'Tech B',
        ]);

        $this->assertDatabaseHas('material_movements', [
            'type' => 'out',
            'vehicle_id' => $vehicle->id,
            'issued_by' => $manager->name,
            'issued_to' => 'Tech B',
            'unit_cost' => 1000,
        ]);
    }

    public function test_job_card_register_can_be_searched_by_engine_number_and_create_form_is_guarded(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        Vehicle::create(['plate' => 'MET-SRCH-1', 'make' => 'Isuzu', 'model' => 'NQR', 'stage' => '1. Intake & Diagnosis', 'intake_date' => now(), 'engine_no' => 'ENG-FIND-ME']);
        Vehicle::create(['plate' => 'MET-SRCH-2', 'make' => 'Isuzu', 'model' => 'FRR', 'stage' => '1. Intake & Diagnosis', 'intake_date' => now(), 'engine_no' => 'ENG-OTHER']);

        $this->actingAs($manager)->get(route('vehicles.index', ['search' => 'FIND-ME']))
            ->assertOk()
            ->assertSee('MET-SRCH-1')
            ->assertDontSee('MET-SRCH-2');

        $accountant = User::factory()->create(['role' => 'Accountant']);
        $this->actingAs($accountant)->get(route('vehicles.create'))->assertForbidden();
    }
}
