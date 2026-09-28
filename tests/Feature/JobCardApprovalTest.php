<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobCardApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function createJobCard(array $attributes = []): Vehicle
    {
        return Vehicle::create(array_merge([
            'plate' => 'MET-TEST-0001',
            'make' => 'Isuzu',
            'model' => 'FRR',
            'stage' => '1. Intake & Diagnosis',
            'intake_date' => now(),
            'prepared_by' => 'Jane Manager',
        ], $attributes));
    }

    public function test_creating_a_job_card_records_who_prepared_it(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);

        $this->actingAs($manager)->post(route('vehicles.store'), [
            'plate' => 'MET-NEW-0002',
            'make' => 'Isuzu',
            'model' => 'NQR',
            'stage' => '1. Intake & Diagnosis',
        ])->assertRedirect();

        $this->assertDatabaseHas('vehicles', [
            'plate' => 'MET-NEW-0002',
            'prepared_by' => $manager->name,
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    public function test_manager_can_approve_a_job_card(): void
    {
        $this->freezeSecond();
        $manager = User::factory()->create(['role' => 'Manager']);
        $jobCard = $this->createJobCard();

        $this->actingAs($manager)
            ->put(route('vehicles.approve', $jobCard->id))
            ->assertRedirect()
            ->assertSessionHas('flash_success');

        $jobCard->refresh();
        $this->assertSame($manager->name, $jobCard->approved_by);
        $this->assertTrue($jobCard->approved_at->equalTo(now()));
    }

    public function test_approving_twice_keeps_the_original_approver_and_date(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $approvedAt = now()->subDays(3)->startOfSecond();
        $jobCard = $this->createJobCard(['approved_by' => 'First Approver', 'approved_at' => $approvedAt]);

        $this->actingAs($admin)->put(route('vehicles.approve', $jobCard->id))->assertSessionHas('flash_danger');

        $jobCard->refresh();
        $this->assertSame('First Approver', $jobCard->approved_by);
        $this->assertTrue($jobCard->approved_at->equalTo($approvedAt));
    }

    public function test_admin_can_revoke_approval(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $jobCard = $this->createJobCard(['approved_by' => 'Jane Manager', 'approved_at' => now()]);

        $this->actingAs($admin)->delete(route('vehicles.revoke_approval', $jobCard->id))->assertRedirect();

        $jobCard->refresh();
        $this->assertNull($jobCard->approved_by);
        $this->assertNull($jobCard->approved_at);
    }

    public function test_storekeeper_and_accountant_cannot_approve_or_revoke(): void
    {
        $jobCard = $this->createJobCard();

        foreach (['Storekeeper', 'Accountant'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->put(route('vehicles.approve', $jobCard->id))->assertForbidden();
            $this->actingAs($user)->delete(route('vehicles.revoke_approval', $jobCard->id))->assertForbidden();
        }

        $this->assertNull($jobCard->fresh()->approved_at);
    }

    public function test_register_screen_and_printout_show_prepared_and_approved_details(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        $this->createJobCard(['approved_by' => 'Peter Approver', 'approved_at' => '2026-09-20 10:00:00']);
        $this->createJobCard(['plate' => 'MET-TEST-0003', 'prepared_by' => 'Sam Preparer']);

        $this->actingAs($manager)->get(route('vehicles.index'))
            ->assertOk()
            ->assertSeeInOrder(['Prepared By', 'Approved By', 'Date of Approval'])
            ->assertSee('Jane Manager')
            ->assertSee('Peter Approver')
            ->assertSee('20 Sep 2026')
            ->assertSee('Pending approval')
            ->assertSee('Approve');

        $this->actingAs($manager)->get(route('vehicles.print_register'))
            ->assertOk()
            ->assertSeeInOrder(['Prepared By', 'Approved By', 'Date of Approval'])
            ->assertSee('Sam Preparer')
            ->assertSee('Peter Approver')
            ->assertSee('20 Sep 2026');
    }
}
