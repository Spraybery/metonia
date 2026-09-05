<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Authorized Staff Sign In');
    }

    public function test_authentication_works_with_username_and_password(): void
    {
        $response = $this->post('/login', [
            'identifier' => 'admin',
            'password' => 'password',
            'remember' => 'on',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_dashboard_returns_analytics_and_stuck_vehicles(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Workshop Operations Dashboard');
        $response->assertSee('Pipeline Bottlenecks: Stuck Vehicles');
    }

    public function test_api_snapshot_endpoint_returns_json(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->getJson('/api/db');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'totalActiveVehicles',
            'stuckVehicles',
            'lowStockMaterials',
            'revenueMtd',
            'marginMtd',
            'pipelineCounts',
        ]);
    }

    public function test_atomic_parts_issuance_decrements_inventory(): void
    {
        $admin = User::where('username', 'admin')->first();
        $vehicle = Vehicle::where('plate', 'MET-2026-8849102')->first();
        $material = Material::where('qty', '>', 5)->first();

        $initialQty = (float) $material->qty;
        $issueQty = 2.0;

        $response = $this->actingAs($admin)->post("/vehicles/{$vehicle->id}/parts", [
            'material_id' => $material->id,
            'qty' => $issueQty,
        ]);

        $response->assertSessionHas('flash_success');

        $material->refresh();
        $this->assertEquals($initialQty - $issueQty, (float) $material->qty);

        $this->assertDatabaseHas('vehicle_parts', [
            'vehicle_id' => $vehicle->id,
            'material_id' => $material->id,
            'qty' => $issueQty,
        ]);
    }

    public function test_shopkeeper_cannot_access_user_management(): void
    {
        $shopkeeper = User::where('username', 'shopkeeper')->first();

        $response = $this->actingAs($shopkeeper)->get('/users');
        $response->assertStatus(403);
    }

    public function test_manager_can_record_supplier_delivery_and_update_inventory(): void
    {
        $manager = User::where('username', 'manager')->first();
        $material = Material::first();
        $initialQty = (float) $material->qty;
        $deliveryQty = 25.0;

        $response = $this->actingAs($manager)->post("/materials/{$material->id}/movement", [
            'type' => 'in',
            'qty' => $deliveryQty,
            'date' => now()->toDateString(),
            'person' => 'Grace Nduta',
            'supplier' => 'Nairobi Heavy Parts Suppliers',
            'note' => 'Quarterly consignment delivery note #8821',
        ]);

        $response->assertSessionHas('flash_success');

        $material->refresh();
        $this->assertEquals($initialQty + $deliveryQty, (float) $material->qty);
        $this->assertEquals('Nairobi Heavy Parts Suppliers', $material->supplier);

        $this->assertDatabaseHas('material_movements', [
            'material_id' => $material->id,
            'type' => 'in',
            'qty' => $deliveryQty,
            'person' => 'Grace Nduta',
        ]);
    }

    public function test_shopkeeper_can_issue_material_to_vehicle_with_technician_and_syncs_job_card(): void
    {
        $shopkeeper = User::where('username', 'shopkeeper')->first();
        $vehicle = Vehicle::where('plate', 'MET-2026-8849102')->first();
        $material = Material::where('qty', '>', 5)->first();

        $initialQty = (float) $material->qty;
        $issueQty = 3.0;

        $response = $this->actingAs($shopkeeper)->post("/materials/{$material->id}/movement", [
            'type' => 'out',
            'qty' => $issueQty,
            'date' => now()->toDateString(),
            'person' => 'Eng. Peter Kimani',
            'vehicle_id' => $vehicle->id,
            'note' => 'Chassis reinforcement crossbars',
        ]);

        $response->assertSessionHas('flash_success');

        $material->refresh();
        $this->assertEquals($initialQty - $issueQty, (float) $material->qty);

        // Verify Job Card parts schedule has synced
        $this->assertDatabaseHas('vehicle_parts', [
            'vehicle_id' => $vehicle->id,
            'material_id' => $material->id,
            'qty' => $issueQty,
        ]);

        $this->assertDatabaseHas('material_movements', [
            'material_id' => $material->id,
            'type' => 'out',
            'person' => 'Eng. Peter Kimani',
            'vehicle_id' => $vehicle->id,
        ]);
    }

    public function test_general_supervisor_can_transition_vehicle_stage_and_assign_lead(): void
    {
        $supervisor = User::where('username', 'supervisor')->first();
        $vehicle = Vehicle::where('plate', 'MET-2026-8849102')->first();

        $newStage = '3. Powertrain & Mechanical';
        $newLead = 'Eng. John Otieno';

        $response = $this->actingAs($supervisor)->put("/vehicles/{$vehicle->id}/stage", [
            'stage' => $newStage,
            'assigned_to' => $newLead,
        ]);

        $response->assertSessionHas('flash_success');

        $vehicle->refresh();
        $this->assertEquals($newStage, $vehicle->stage);
        $this->assertEquals($newLead, $vehicle->assigned_to);

        $this->assertDatabaseHas('vehicle_stage_histories', [
            'vehicle_id' => $vehicle->id,
            'stage' => $newStage,
        ]);
    }

    public function test_accountant_has_view_only_access_and_cannot_modify_inventory(): void
    {
        $accountant = User::where('username', 'accountant')->first();
        $material = Material::first();

        // Accountant can view dashboard and vehicles
        $this->actingAs($accountant)->get('/dashboard')->assertStatus(200);
        $this->actingAs($accountant)->get('/materials')->assertStatus(200);
        $this->actingAs($accountant)->get('/vehicles')->assertStatus(200);

        // Accountant cannot post movements or edits
        $response = $this->actingAs($accountant)->post("/materials/{$material->id}/movement", [
            'type' => 'in',
            'qty' => 10,
            'date' => now()->toDateString(),
            'person' => 'Alice Wambui',
        ]);
        $response->assertStatus(403);
    }

    public function test_admin_cannot_delete_self_via_self_deletion_guard(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->delete("/users/{$admin->id}");
        $response->assertSessionHas('flash_danger');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_printable_job_card_renders_official_print_engine(): void
    {
        $admin = User::where('username', 'admin')->first();
        $vehicle = Vehicle::where('plate', 'MET-2026-8849102')->first();

        $response = $this->actingAs($admin)->get("/vehicles/{$vehicle->id}/print");
        $response->assertStatus(200);
        $response->assertSee('Official Vehicle Job Card &amp; Build Dossier', false);
        $response->assertSee('MET-2026-8849102');
    }
}
