<?php

namespace Tests\Feature;

use App\Models\TravelOrder;
use App\Models\TripTicket;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TravelOrderSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function order(string $status = 'issued'): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $recipient = User::factory()->create();
        $vehicle = Vehicle::create(['name' => 'Crosswind', 'plate_number' => 'SJJ 504', 'is_active' => true]);
        $ticket = TripTicket::create([
            'vehicle_id' => $vehicle->id,
            'purpose' => 'Official travel',
            'date_start' => now()->addWeek()->toDateString(),
            'date_end' => now()->addWeek()->toDateString(),
            'destination' => 'Surigao City',
            'status' => 'approved',
            'requested_by' => $recipient->id,
        ]);
        $order = TravelOrder::create([
            'trip_ticket_id' => $ticket->id,
            'issued_to' => $recipient->id,
            'purpose' => 'Official travel',
            'destination' => 'Surigao City',
            'destination_scope' => 'within_sdn',
            'date_start' => now()->addWeek()->toDateString(),
            'date_end' => now()->addWeek()->toDateString(),
            'transportation_mode' => 'government_vehicle',
            'vehicle_id' => $vehicle->id,
            'approving_officer' => 'Officer',
            'approving_position' => 'PSTD',
            'status' => $status,
            'issued_by' => $admin->id,
            'issued_at' => $status === 'issued' ? now() : null,
        ]);
        $order->passengers()->create([
            'user_id' => $recipient->id,
            'name' => $recipient->name,
        ]);

        return compact('admin', 'recipient', 'ticket', 'order');
    }

    public function test_only_authorized_people_can_print_an_issued_order(): void
    {
        ['recipient' => $recipient, 'order' => $order] = $this->order();
        $other = User::factory()->create();

        $this->actingAs($other)->get(route('travel-orders.print', $order))->assertForbidden();
        $this->actingAs($recipient)->get(route('travel-orders.print', $order))->assertOk();
    }

    public function test_cancelled_order_cannot_be_printed(): void
    {
        ['admin' => $admin, 'order' => $order] = $this->order('cancelled');

        $this->actingAs($admin)->get(route('travel-orders.print', $order))->assertForbidden();
    }

    public function test_database_allows_only_one_order_per_trip_ticket(): void
    {
        ['admin' => $admin, 'recipient' => $recipient, 'ticket' => $ticket, 'order' => $order] = $this->order();

        $this->expectException(QueryException::class);
        DB::table('travel_orders')->insert([
            'travel_order_number' => 'SDN-2099-9999',
            'trip_ticket_id' => $ticket->id,
            'issued_to' => $recipient->id,
            'purpose' => 'Duplicate',
            'destination' => 'Duplicate',
            'destination_scope' => 'within_sdn',
            'date_start' => now()->toDateString(),
            'date_end' => now()->toDateString(),
            'transportation_mode' => 'bus',
            'approving_officer' => 'Officer',
            'approving_position' => 'PSTD',
            'status' => 'draft',
            'issued_by' => $admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
