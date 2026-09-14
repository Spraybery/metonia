<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestockFinanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_accountant_and_admin_can_edit_restock_finance(): void
    {
        $accountant = User::factory()->create(['role' => 'Accountant']);
        $admin = User::factory()->create(['role' => 'Admin']);
        $storekeeper = User::factory()->create(['role' => 'Storekeeper']);

        $this->assertTrue($accountant->canEditRestockFinance());
        $this->assertTrue($admin->canEditRestockFinance());
        $this->assertFalse($storekeeper->canEditRestockFinance());
    }

    public function test_accountant_can_update_restock_price_estimate(): void
    {
        $accountant = User::factory()->create(['role' => 'Accountant']);
        $material = Material::create([
            'item_code' => 'MAT-0001',
            'name' => 'Steel Rod 12mm',
            'category' => 'Metals',
            'unit' => 'Pieces',
            'qty' => 5,
            'low_stock' => 20,
            'unit_cost' => 150.00,
        ]);

        $response = $this->actingAs($accountant)->put(route('materials.update_restock_price', $material->id), [
            'unit_cost' => 220.50,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_success');

        $this->assertDatabaseHas('materials', [
            'id' => $material->id,
            'unit_cost' => 220.50,
        ]);
    }

    public function test_non_accountant_cannot_update_restock_price_estimate(): void
    {
        $storekeeper = User::factory()->create(['role' => 'Storekeeper']);
        $material = Material::create([
            'item_code' => 'SAF-0001',
            'name' => 'Safety Gloves',
            'category' => 'Worker Safety & PPE',
            'unit' => 'Pairs',
            'qty' => 2,
            'low_stock' => 10,
            'unit_cost' => 300.00,
        ]);

        $response = $this->actingAs($storekeeper)->put(route('materials.update_restock_price', $material->id), [
            'unit_cost' => 500.00,
        ]);

        $response->assertStatus(403);
    }

    public function test_restock_needed_page_calculates_total_estimated_budget_and_monthly_expenditure(): void
    {
        $accountant = User::factory()->create(['role' => 'Accountant']);

        $material = Material::create([
            'item_code' => 'MAT-0002',
            'name' => 'Welding Rod E6013',
            'category' => 'Consumables',
            'unit' => 'Boxes',
            'qty' => 2,
            'low_stock' => 10,
            'unit_cost' => 1200.00,
        ]);

        // Record a restock movement in current month
        MaterialMovement::create([
            'material_id' => $material->id,
            'material_name' => $material->name,
            'type' => 'in',
            'qty' => 8,
            'unit' => 'Boxes',
            'unit_cost' => 1200.00,
            'date' => now()->toDateString(),
            'person' => $accountant->name,
            'issued_by' => $accountant->name,
            'issued_to' => $accountant->name,
            'note' => 'Restock test batch',
        ]);

        $response = $this->actingAs($accountant)->get(route('materials.restock_needed'));

        $response->assertStatus(200);
        $response->assertSee('Welding Rod E6013');
        $response->assertSee('Est. Requisition Budget');
        $response->assertSee('Monthly Restock Expenditure Tracker');
    }

    public function test_storekeeper_creating_material_cannot_set_unit_cost(): void
    {
        $storekeeper = User::factory()->create(['role' => 'Storekeeper']);

        $response = $this->actingAs($storekeeper)->post(route('materials.store'), [
            'name' => 'Aluminium Sheet 2mm',
            'category' => 'Aluminium',
            'unit' => 'Pieces',
            'qty' => 10,
            'low_stock' => 5,
            'unit_cost' => 4500.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('materials', [
            'name' => 'Aluminium Sheet 2mm',
            'unit_cost' => 0.00,
        ]);
    }
}
