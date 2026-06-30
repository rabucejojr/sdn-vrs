<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $periods = ['2026-06' => 85];

        foreach (DB::table('trip_tickets')->pluck('ticket_number') as $ticketNumber) {
            if (preg_match('/^(\d{4}-\d{2})-(\d+)$/', $ticketNumber, $match)) {
                $periods[$match[1]] = max($periods[$match[1]] ?? 0, (int) $match[2]);
            }
        }

        foreach ($periods as $period => $minimum) {
            $sequence = DB::table('document_sequences')
                ->where('scope', 'trip-ticket')
                ->where('period', $period)
                ->first();

            if (! $sequence) {
                DB::table('document_sequences')->insert([
                    'scope' => 'trip-ticket',
                    'period' => $period,
                    'last_value' => $minimum,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                continue;
            }

            if ($sequence->last_value < $minimum) {
                DB::table('document_sequences')->where('id', $sequence->id)->update([
                    'last_value' => $minimum,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('document_sequences')
            ->where('scope', 'trip-ticket')
            ->delete();
    }
};
