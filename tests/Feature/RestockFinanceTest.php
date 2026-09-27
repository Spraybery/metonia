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

    private function createRestock(string $date, float $qty = 10): MaterialMovement
    {
        $material = Material::firstOrCreate(['item_code' => 'MAT-0100'], [
            'name' => 'Steel Rod 12mm',
            'category' => 'Metals',
            'unit' => 'Pieces',
            'qty' => 0,
            'low_stock' => 5,
            'unit_cost' => 100.00,
        ]);

        return MaterialMovement::create([
            'material_id' => $material->id,
            'material_name' => $material->name,
            'type' => 'in',
            'qty' => $qty,
            'unit' => 'Pieces',
            'unit_cost' => 100.00,
            'date' => $date,
            'person' => 'Store Keeper',
        ]);
    }

    public function test_accountant_can_record_amount_spent_on_restock(): void
    {
        $accountant = User::factory()->create(['role' => 'Accountant']);
        $restock = $this->createRestock(now()->toDateString());

        $response = $this->actingAs($accountant)->put(route('materials.movement.amount_spent', $restock->id), [
            'amount_spent' => 1250.75,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_success');
        $this->assertDatabaseHas('material_movements', [
            'id' => $restock->id,
            'amount_spent' => 1250.75,
            'amount_spent_recorded_by' => $accountant->name,
        ]);
    }

    public function test_storekeeper_cannot_record_amount_spent_on_restock(): void
    {
        $storekeeper = User::factory()->create(['role' => 'Storekeeper']);
        $restock = $this->createRestock(now()->toDateString());

        $this->actingAs($storekeeper)
            ->put(route('materials.movement.amount_spent', $restock->id), ['amount_spent' => 500])
            ->assertForbidden();

        $this->assertNull($restock->fresh()->amount_spent);
    }

    public function test_amount_spent_cannot_be_recorded_on_an_issuance(): void
    {
        $accountant = User::factory()->create(['role' => 'Accountant']);
        $issuance = $this->createRestock(now()->toDateString());
        $issuance->update(['type' => 'out']);

        $this->actingAs($accountant)
            ->put(route('materials.movement.amount_spent', $issuance->id), ['amount_spent' => 500])
            ->assertNotFound();
    }

    public function test_amount_spent_must_be_a_non_negative_number(): void
    {
        $accountant = User::factory()->create(['role' => 'Accountant']);
        $restock = $this->createRestock(now()->toDateString());

        $this->actingAs($accountant)
            ->put(route('materials.movement.amount_spent', $restock->id), ['amount_spent' => -10])
            ->assertSessionHasErrors('amount_spent');
    }

    public function test_restock_page_totals_recorded_spend_per_month(): void
    {
        $accountant = User::factory()->create(['role' => 'Accountant']);
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->startOfMonth()->subMonth();

        $this->createRestock($thisMonth->toDateString())->update(['amount_spent' => 1000]);
        $this->createRestock($thisMonth->copy()->addDay()->toDateString())->update(['amount_spent' => 2500.50]);
        $this->createRestock($thisMonth->copy()->addDays(2)->toDateString());
        $this->createRestock($lastMonth->toDateString())->update(['amount_spent' => 400]);

        $response = $this->actingAs($accountant)->get(route('materials.restock'));

        $response->assertOk();
        $response->assertSee('Monthly Money Spent on Restocks');
        $response->assertViewHas('currentMonthSpend', fn (array $month) => $month['amount_spent'] === 3500.5
            && $month['restock_count'] === 3
            && $month['awaiting_cost_count'] === 1);
        $response->assertViewHas('monthlyRestockSpend', fn ($months) => $months->pluck('amount_spent')->all() === [3500.5, 400.0]
            && $months->pluck('year_month')->all() === [$thisMonth->format('Y-m'), $lastMonth->format('Y-m')]);
        $response->assertSee('Enter Amount');
    }

    public function test_storekeeper_does_not_see_enter_amount_control(): void
    {
        $storekeeper = User::factory()->create(['role' => 'Storekeeper']);
        $this->createRestock(now()->toDateString());

        $this->actingAs($storekeeper)
            ->get(route('materials.restock'))
            ->assertOk()
            ->assertDontSee('Enter Amount');
    }

    public function test_recorded_amount_spent_overrides_estimated_restock_cost(): void
    {
        $restock = $this->createRestock(now()->toDateString(), qty: 10);

        $this->assertSame(1000.0, $restock->total_cost);

        $restock->update(['amount_spent' => 850]);

        $this->assertSame(850.0, $restock->fresh()->total_cost);
    }

    public function test_dashboard_tracks_restock_spend_but_not_issued_stock_value(): void
    {
        $accountant = User::factory()->create(['role' => 'Accountant']);
        $this->createRestock(now()->toDateString())->update(['amount_spent' => 1800]);
        $this->createRestock(now()->toDateString());
        $this->createRestock(now()->toDateString())->update(['type' => 'out']);

        $response = $this->actingAs($accountant)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('monthlyRestockSpend', 1800.0);
        $response->assertViewHas('monthlyRestocksAwaitingAmount', 1);
        $response->assertSee('Money Spent on Restocks (MTD)');
        $response->assertDontSee('Stock Issued (MTD)');
        $response->assertDontSee('Net Valuation Change');
    }
}
