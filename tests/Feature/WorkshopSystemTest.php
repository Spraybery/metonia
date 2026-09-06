<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleStageHistory;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SampleDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class WorkshopSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DatabaseSeeder::class, SampleDataSeeder::class]);
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

    public function test_manager_can_transition_vehicle_stage_and_assign_lead(): void
    {
        $manager = User::where('username', 'manager')->first();
        $vehicle = Vehicle::where('plate', 'MET-2026-8849102')->first();

        $newStage = '3. Powertrain & Mechanical';
        $newLead = 'Eng. John Otieno';

        $response = $this->actingAs($manager)->put("/vehicles/{$vehicle->id}/stage", [
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

    public function test_transitioning_a_vehicle_removes_it_from_its_previous_stage_filtered_list(): void
    {
        $admin = User::where('username', 'admin')->first();
        $vehicle = Vehicle::where('plate', 'MET-2026-8849102')->first();
        $oldStage = $vehicle->stage;
        $newStage = '3. Powertrain & Mechanical';

        $this->actingAs($admin)
            ->get(route('vehicles.index', ['stage' => $oldStage]))
            ->assertSee($vehicle->plate);

        $this->actingAs($admin)->put("/vehicles/{$vehicle->id}/stage", [
            'stage' => $newStage,
        ]);

        $this->actingAs($admin)
            ->get(route('vehicles.index', ['stage' => $oldStage]))
            ->assertDontSee($vehicle->plate);

        $this->actingAs($admin)
            ->get(route('vehicles.index', ['stage' => $newStage]))
            ->assertSee($vehicle->plate);
    }

    public function test_advance_to_next_stage_action_moves_vehicle_forward_one_step(): void
    {
        $manager = User::where('username', 'manager')->first();
        $vehicle = Vehicle::where('plate', 'MET-2026-8849102')->first();
        $expectedNextStage = '3. Powertrain & Mechanical';

        $response = $this->actingAs($manager)->put("/vehicles/{$vehicle->id}/stage", [
            'stage' => $expectedNextStage,
        ]);

        $response->assertSessionHas('flash_success');

        $vehicle->refresh();
        $this->assertEquals($expectedNextStage, $vehicle->stage);
        $this->assertEquals(0, $vehicle->checklist_done);

        $this->assertDatabaseHas('vehicle_stage_histories', [
            'vehicle_id' => $vehicle->id,
            'stage' => $expectedNextStage,
        ]);
    }

    public function test_stage_history_tab_shows_computed_duration_for_each_completed_stage(): void
    {
        $admin = User::where('username', 'admin')->first();
        $vehicle = Vehicle::where('plate', 'MET-2026-8849102')->first();

        $this->travelTo(Carbon::parse('2026-01-01 08:00:00'));
        $vehicle->stageHistories()->delete();
        VehicleStageHistory::create([
            'vehicle_id' => $vehicle->id,
            'stage' => '2. Structural & Frame',
            'transitioned_at' => Carbon::now(),
        ]);
        $vehicle->update(['stage' => '2. Structural & Frame']);

        // Deliberately not a whole number of days: Carbon 3's diffInDays() returns
        // a float, so this catches a regression that would print "3.29... days".
        $this->travelTo(Carbon::parse('2026-01-04 15:00:00'));
        $this->actingAs($admin)->put("/vehicles/{$vehicle->id}/stage", [
            'stage' => '3. Powertrain & Mechanical',
        ]);

        $response = $this->actingAs($admin)->get("/vehicles/{$vehicle->id}");

        $response->assertSee('3 days');
        $response->assertDontSee('3.2');
        $response->assertSee('Still here');

        $this->travelBack();
    }

    public function test_days_in_current_stage_is_a_positive_elapsed_count_and_flags_stuck_vehicles(): void
    {
        $vehicle = Vehicle::where('plate', 'MET-2026-8849102')->first();
        $vehicle->stageHistories()->delete();

        VehicleStageHistory::create([
            'vehicle_id' => $vehicle->id,
            'stage' => $vehicle->stage,
            'transitioned_at' => Carbon::now()->subDays(12),
        ]);

        $vehicle->refresh();
        $this->assertEquals(12, $vehicle->days_in_current_stage);
        $this->assertTrue($vehicle->isStuck());
    }

    public function test_vehicles_register_print_view_renders_with_logo_and_theme(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get('/vehicles/print');

        $response->assertStatus(200);
        $response->assertSee('Vehicle Build &amp; Job Cards Register', false);
        $response->assertSee('MET-2026-8849102');
        $response->assertSee('logo_metonia');
    }

    public function test_vehicles_register_print_view_respects_stage_filter(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get('/vehicles/print?stage='.urlencode('2. Structural & Frame'));

        $response->assertStatus(200);
        $response->assertSee('MET-2026-8849102');
        $response->assertDontSee('MET-2026-7731209');
    }

    public function test_materials_register_print_view_renders_with_logo_and_theme(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get('/materials/print');

        $response->assertStatus(200);
        $response->assertSee('Store Inventory &amp; Raw Materials Register', false);
        $response->assertSee('logo_metonia');
    }

    public function test_materials_register_print_view_respects_category_filter(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get('/materials/print?category=Metals');

        $response->assertStatus(200);
        $response->assertSee('Heavy Duty Structural Steel Beam');
        $response->assertDontSee('Aluminium Tread Plate Sheet');
    }

    public function test_outward_issuance_register_print_view_renders_with_logo_and_theme(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get('/materials/issuance/print');

        $response->assertStatus(200);
        $response->assertSee('Outward Store Material Issuance Register');
        $response->assertSee('logo_metonia');
    }

    public function test_supplier_restock_register_print_view_renders_with_logo_and_theme(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get('/materials/restock/print');

        $response->assertStatus(200);
        $response->assertSee('Supplier Restock &amp; Delivery Register', false);
        $response->assertSee('logo_metonia');
    }

    public function test_safety_stock_register_print_view_renders_with_logo_and_theme(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get('/materials/safety-stock/print');

        $response->assertStatus(200);
        $response->assertSee('Worker Safety &amp; Personal Protective Equipment', false);
        $response->assertSee('logo_metonia');
    }

    public function test_tools_register_print_view_renders_with_logo_and_theme(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get('/tools/print');

        $response->assertStatus(200);
        $response->assertSee('Workshop Tools &amp; Calibration Asset Register', false);
        $response->assertSee('logo_metonia');
    }

    public function test_supervisors_roster_print_view_renders_with_logo_and_theme(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get('/supervisors/print');

        $response->assertStatus(200);
        $response->assertSee('Workshop Lead Supervisors Roster');
        $response->assertSee('logo_metonia');
    }

    public function test_login_page_shows_forgot_password_link_and_password_toggle(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee(route('password.request'), false);
        $response->assertSee('togglePasswordVisibility', false);
    }

    public function test_forgot_password_page_renders_successfully(): void
    {
        $response = $this->get('/password/forgot');

        $response->assertStatus(200);
        $response->assertSee('Reset Your Password');
    }

    public function test_valid_email_receives_generic_confirmation_and_creates_reset_token(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->post('/password/forgot', ['email' => $admin->email]);

        $response->assertSessionHas('flash_success');
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $admin->email]);
    }

    public function test_unregistered_email_shows_the_same_generic_confirmation(): void
    {
        $response = $this->post('/password/forgot', ['email' => 'nobody@metonia.co.ke']);

        $response->assertSessionHas('flash_success', 'If that email address is registered in our system, a password reset link has been sent.');
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'nobody@metonia.co.ke']);
    }

    public function test_reset_password_page_renders_with_token_and_email_prefilled(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->get('/password/reset/some-token?email='.urlencode($admin->email));

        $response->assertStatus(200);
        $response->assertSee($admin->email);
    }

    public function test_valid_reset_token_updates_password_and_redirects_to_login(): void
    {
        $admin = User::where('username', 'admin')->first();
        $token = Password::createToken($admin);

        $response = $this->post('/password/reset', [
            'token' => $token,
            'email' => $admin->email,
            'password' => 'NewSecure123',
            'password_confirmation' => 'NewSecure123',
        ]);

        $response->assertRedirect(route('login'));

        $admin->refresh();
        $this->assertTrue(Hash::check('NewSecure123', $admin->password));
    }

    public function test_invalid_reset_token_is_rejected(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->post('/password/reset', [
            'token' => 'not-a-real-token',
            'email' => $admin->email,
            'password' => 'NewSecure123',
            'password_confirmation' => 'NewSecure123',
        ]);

        $response->assertSessionHasErrors('email');

        $admin->refresh();
        $this->assertFalse(Hash::check('NewSecure123', $admin->password));
    }

    public function test_weak_new_password_is_rejected_on_reset(): void
    {
        $admin = User::where('username', 'admin')->first();
        $token = Password::createToken($admin);

        $response = $this->post('/password/reset', [
            'token' => $token,
            'email' => $admin->email,
            'password' => 'alllowercase',
            'password_confirmation' => 'alllowercase',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
