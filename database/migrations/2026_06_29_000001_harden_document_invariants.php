<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 80);
            $table->string('period', 10);
            $table->unsignedBigInteger('last_value')->default(0);
            $table->timestamps();
            $table->unique(['scope', 'period']);
        });

        $vehicleId = DB::table('vehicles')->where('is_active', true)->value('id')
            ?? DB::table('vehicles')->value('id');

        if (! $vehicleId) {
            $vehicleId = DB::table('vehicles')->insertGetId([
                'name' => 'Crosswind',
                'plate_number' => 'SJJ 504',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('vehicles')->where('id', '!=', $vehicleId)->update(['is_active' => false]);
        DB::table('vehicles')->where('id', $vehicleId)->update(['is_active' => true]);

        DB::table('trip_tickets')->whereNull('vehicle_id')->update(['vehicle_id' => $vehicleId]);
        DB::table('trip_tickets')->whereNull('date_start')->update(['date_start' => DB::raw('date_of_travel')]);
        DB::table('trip_tickets')->whereNull('date_end')->update(['date_end' => DB::raw('date_of_travel')]);

        Schema::table('trip_tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('vehicle_id')->nullable(false)->change();
            $table->date('date_start')->nullable(false)->change();
            $table->date('date_end')->nullable(false)->change();
            $table->index(['vehicle_id', 'status', 'date_start', 'date_end'], 'trip_conflict_lookup');
        });

        $duplicates = DB::table('travel_orders')
            ->select('trip_ticket_id')
            ->whereNotNull('trip_ticket_id')
            ->groupBy('trip_ticket_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('trip_ticket_id');

        foreach ($duplicates as $tripTicketId) {
            $duplicateIds = DB::table('travel_orders')
                ->where('trip_ticket_id', $tripTicketId)
                ->orderBy('id')
                ->pluck('id')
                ->slice(1);
            DB::table('travel_orders')->whereIn('id', $duplicateIds)->update(['trip_ticket_id' => null]);
        }

        Schema::table('travel_orders', function (Blueprint $table) {
            $table->unique('trip_ticket_id');
        });

        $this->seedExistingSequences();
    }

    public function down(): void
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            $table->dropUnique(['trip_ticket_id']);
        });

        Schema::table('trip_tickets', function (Blueprint $table) {
            $table->dropIndex('trip_conflict_lookup');
            $table->unsignedBigInteger('vehicle_id')->nullable()->change();
            $table->date('date_start')->nullable()->change();
            $table->date('date_end')->nullable()->change();
        });

        Schema::dropIfExists('document_sequences');
    }

    private function seedExistingSequences(): void
    {
        foreach (DB::table('trip_tickets')->select('vehicle_id', 'ticket_number', 'created_at')->orderBy('id')->get() as $ticket) {
            if (! $ticket->created_at || ! preg_match('/(\d{4})$/', $ticket->ticket_number, $match)) {
                continue;
            }

            $this->raiseSequence(
                "trip-ticket:{$ticket->vehicle_id}",
                substr((string) $ticket->created_at, 0, 7),
                (int) $match[1],
            );
        }

        foreach (DB::table('travel_orders')->select('travel_order_number', 'created_at')->orderBy('id')->get() as $order) {
            if (! $order->created_at || ! preg_match('/(\d{4})$/', $order->travel_order_number, $match)) {
                continue;
            }

            $this->raiseSequence('travel-order', substr((string) $order->created_at, 0, 4), (int) $match[1]);
        }
    }

    private function raiseSequence(string $scope, string $period, int $value): void
    {
        $existing = DB::table('document_sequences')
            ->where('scope', $scope)
            ->where('period', $period)
            ->first();

        if (! $existing) {
            DB::table('document_sequences')->insert([
                'scope' => $scope,
                'period' => $period,
                'last_value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return;
        }

        if ($value > $existing->last_value) {
            DB::table('document_sequences')->where('id', $existing->id)->update([
                'last_value' => $value,
                'updated_at' => now(),
            ]);
        }
    }
};
