<?php

namespace Tests\Feature;

use App\Models\TripTicket;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReservationSecurityAndWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function vehicle(): Vehicle
    {
        return Vehicle::create([
            'name' => 'Crosswind',
            'plate_number' => 'SJJ 504',
            'is_active' => true,
        ]);
    }

    private function ticket(User $requester, Vehicle $vehicle, array $overrides = []): TripTicket
    {
        return TripTicket::create([
            'vehicle_id' => $vehicle->id,
            'purpose' => 'Official travel',
            'date_start' => now()->addWeek()->toDateString(),
            'date_end' => now()->addWeek()->toDateString(),
            'date_of_travel' => now()->addWeek()->toDateString(),
            'destination' => 'Surigao City',
            'status' => 'pending',
            'requested_by' => $requester->id,
            ...$overrides,
        ]);
    }

    public function test_staff_cannot_view_or_print_another_users_ticket(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ticket = $this->ticket($owner, $this->vehicle(), ['status' => 'approved']);

        $this->actingAs($other)->get(route('reservations.show', $ticket))->assertForbidden();
        $this->actingAs($other)->get(route('reservations.print', $ticket))->assertForbidden();
        $this->actingAs($owner)->get(route('reservations.print', $ticket))->assertOk();
    }

    public function test_unapproved_ticket_cannot_be_printed(): void
    {
        $owner = User::factory()->create();
        $ticket = $this->ticket($owner, $this->vehicle());

        $this->actingAs($owner)->get(route('reservations.pdf', $ticket))->assertForbidden();
    }

    public function test_pdf_template_includes_the_drivers_name(): void
    {
        $owner = User::factory()->create();
        $ticket = $this->ticket($owner, $this->vehicle(), [
            'status' => 'approved',
            'driver_name' => 'Juan Dela Cruz',
        ])->load(['requester', 'approver', 'passengers', 'vehicle']);

        $html = view('trip-tickets.print', [
            'ticket' => $ticket,
            'mode' => 'pdf',
            'organization' => config('organization'),
        ])->render();

        $this->assertStringContainsString('JDCruz', $html);
        $this->assertSame(1, substr_count($html, 'JUAN DELA CRUZ'));
        $this->assertStringContainsString('IMELDA S. MEZO', $html);
        $this->assertStringContainsString('<div class="sig-role">Noted by:</div>', $html);
        $this->assertStringContainsString('<div class="sig-role">Approved by:</div>', $html);
        $this->assertStringContainsString('<div class="sig-role">Driver\'s name:</div>', $html);
        $this->assertSame(2, substr_count($html, $ticket->formattedTicketNumber()));
    }

    public function test_legacy_ticket_number_is_prefixed_in_the_downloaded_pdf(): void
    {
        $owner = User::factory()->create();
        $ticket = $this->ticket($owner, $this->vehicle(), [
            'status' => 'approved',
        ]);
        $legacyNumber = str_replace('Crosswind-', '', $ticket->ticket_number);
        $ticket->forceFill(['ticket_number' => $legacyNumber])->saveQuietly();

        $this->actingAs($owner)
            ->get(route('reservations.pdf', $ticket))
            ->assertDownload("Crosswind-{$legacyNumber}.pdf");
    }

    public function test_approval_rejects_an_overlapping_approved_reservation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create();
        $vehicle = $this->vehicle();
        $date = now()->addWeek()->toDateString();
        $approved = $this->ticket($staff, $vehicle, [
            'status' => 'approved',
            'date_start' => $date,
            'date_end' => $date,
        ]);
        $pending = $this->ticket($staff, $vehicle, [
            'date_start' => $date,
            'date_end' => $date,
        ]);

        $this->assertSame('approved', $approved->status);
        $this->assertSame($date, $approved->date_start->toDateString());
        $this->assertSame(1, TripTicket::where('status', 'approved')->count());
        $this->assertSame(1, TripTicket::conflicting($date, $date, $pending->id, $vehicle->id)->count());

        $this->actingAs($admin)
            ->patch(route('admin.reservations.approve', $pending));

        $this->assertSame('pending', $pending->fresh()->status);
    }

    public function test_api_is_role_scoped_and_does_not_expose_requester_email(): void
    {
        $staff = User::factory()->create();
        $other = User::factory()->create();
        $vehicle = $this->vehicle();
        $own = $this->ticket($staff, $vehicle);
        $this->ticket($other, $vehicle);

        Sanctum::actingAs($staff, ['reservations:read']);

        $response = $this->getJson('/api/reservations')->assertOk();
        $response->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.ticket_number', $own->ticket_number)
            ->assertJsonMissingPath('data.0.requester.email');
    }

    public function test_deactivated_user_is_rejected_by_api(): void
    {
        $staff = User::factory()->create(['is_active' => false]);
        Sanctum::actingAs($staff, ['reservations:read']);

        $this->getJson('/api/reservations')->assertForbidden();
    }

    public function test_authenticated_web_user_can_create_an_expiring_read_only_token(): void
    {
        $staff = User::factory()->create();

        $response = $this->actingAs($staff)->postJson('/api/tokens/create', [
            'token_name' => 'Integration',
        ])->assertCreated();

        $response->assertJsonPath('abilities.0', 'reservations:read')
            ->assertJsonPath('abilities.1', 'vehicles:read');
        $this->assertNotNull($response->json('expires_at'));
    }

    public function test_document_numbers_are_sequential(): void
    {
        $this->travelTo(now()->setDate(2026, 6, 30));

        $staff = User::factory()->create();
        $vehicle = $this->vehicle();

        $first = $this->ticket($staff, $vehicle);
        $second = $this->ticket($staff, $vehicle, [
            'date_start' => now()->addWeeks(2)->toDateString(),
            'date_end' => now()->addWeeks(2)->toDateString(),
        ]);

        $this->assertSame('Crosswind-2026-06-086', $first->ticket_number);
        $this->assertSame('Crosswind-2026-06-087', $second->ticket_number);

        $this->travelBack();
    }

    public function test_staff_dashboard_contains_only_their_trip_details(): void
    {
        $staff = User::factory()->create();
        $other = User::factory()->create();
        $vehicle = $this->vehicle();
        $date = now()->toDateString();
        $own = $this->ticket($staff, $vehicle, [
            'status' => 'approved',
            'date_start' => $date,
            'date_end' => $date,
        ]);
        $this->ticket($other, $vehicle, [
            'status' => 'approved',
            'date_start' => now()->addDays(3)->toDateString(),
            'date_end' => now()->addDays(3)->toDateString(),
        ]);

        $this->actingAs($staff)->get('/dashboard')->assertInertia(
            fn (Assert $page) => $page
                ->where('userStats', null)
                ->has('upcoming', 1)
                ->where('upcoming.0.ticket_number', $own->ticket_number)
                ->where("calendar.bookedDates.{$date}", '')
        );
    }
}
