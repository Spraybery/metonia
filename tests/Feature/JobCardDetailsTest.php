<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobCardDetailsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function jobCardPayload(array $overrides = []): array
    {
        return array_merge([
            'plate' => 'MET-BUS-0001',
            'make' => 'Isuzu',
            'model' => 'NQR',
            'stage' => '1. Intake & Diagnosis',
            'engine_no' => '4HK1-778899',
            'bus_category' => '33-Seater Mini Bus',
            'intake_date' => '2026-09-01',
            'date_out' => '2026-09-25',
            'delivery_date' => '2026-09-30',
        ], $overrides);
    }

    public function test_registering_a_job_card_saves_engine_bus_category_and_dates(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);

        $this->actingAs($manager)->post(route('vehicles.store'), $this->jobCardPayload())->assertRedirect();

        $jobCard = Vehicle::where('plate', 'MET-BUS-0001')->firstOrFail();
        $this->assertSame('4HK1-778899', $jobCard->engine_no);
        $this->assertSame('33-Seater Mini Bus', $jobCard->bus_category);
        $this->assertSame('2026-09-01', $jobCard->intake_date->toDateString());
        $this->assertSame('2026-09-25', $jobCard->date_out->toDateString());
        $this->assertSame('2026-09-30', $jobCard->delivery_date->toDateString());
    }

    public function test_date_in_is_required_and_date_out_cannot_precede_it(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);

        $this->actingAs($manager)
            ->post(route('vehicles.store'), $this->jobCardPayload(['intake_date' => '']))
            ->assertSessionHasErrors('intake_date');

        $this->actingAs($manager)
            ->post(route('vehicles.store'), $this->jobCardPayload(['date_out' => '2026-08-01']))
            ->assertSessionHasErrors('date_out');
    }

    public function test_updating_a_job_card_changes_the_new_fields(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        $this->actingAs($manager)->post(route('vehicles.store'), $this->jobCardPayload());
        $jobCard = Vehicle::where('plate', 'MET-BUS-0001')->firstOrFail();

        $this->actingAs($manager)->put(route('vehicles.update', $jobCard->id), $this->jobCardPayload([
            'engine_no' => 'ENG-NEW-1',
            'bus_category' => '62-Seater Coach',
            'intake_date' => '2026-09-02',
            'date_out' => '',
            'delivery_date' => '2026-10-05',
        ]))->assertRedirect();

        $jobCard->refresh();
        $this->assertSame('ENG-NEW-1', $jobCard->engine_no);
        $this->assertSame('62-Seater Coach', $jobCard->bus_category);
        $this->assertSame('2026-09-02', $jobCard->intake_date->toDateString());
        $this->assertNull($jobCard->date_out);
        $this->assertSame('2026-10-05', $jobCard->delivery_date->toDateString());
    }

    public function test_register_screen_and_printouts_show_the_new_fields(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        $this->actingAs($manager)->post(route('vehicles.store'), $this->jobCardPayload());
        $jobCard = Vehicle::where('plate', 'MET-BUS-0001')->firstOrFail();

        $expected = ['4HK1-778899', '33-Seater Mini Bus', '01 Sep 2026', '25 Sep 2026', '30 Sep 2026'];

        $this->actingAs($manager)->get(route('vehicles.index'))
            ->assertOk()
            ->assertSeeInOrder(['Engine No.', 'Bus Category', 'Date In', 'Date Out', 'Delivery Date'])
            ->assertSeeInOrder($expected);

        $this->actingAs($manager)->get(route('vehicles.print_register'))
            ->assertOk()
            ->assertSeeInOrder(['Engine No.', 'Bus Category', 'Date In', 'Date Out', 'Delivery Date']);

        foreach ([route('vehicles.print_register'), route('vehicles.print', $jobCard->id), route('vehicles.show', $jobCard->id)] as $url) {
            $response = $this->actingAs($manager)->get($url)->assertOk();
            foreach ($expected as $value) {
                $response->assertSee($value);
            }
        }
    }
}
