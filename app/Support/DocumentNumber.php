<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentNumber
{
    public static function tripTicket(int $vehicleId): string
    {
        $vehicle = DB::table('vehicles')->find($vehicleId);
        $prefix = Str::of($vehicle?->name ?? 'Vehicle')
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9]+/', '')
            ->limit(16, '')
            ->value();
        $prefix = $prefix !== '' ? $prefix : 'Vehicle';
        $period = now()->format('Y-m');
        $sequence = self::next("trip-ticket:{$vehicleId}", $period);

        return "{$prefix}-{$period}-".str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public static function travelOrder(): string
    {
        $period = now()->format('Y');
        $sequence = self::next('travel-order', $period);

        return "SDN-{$period}-".str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private static function next(string $scope, string $period): int
    {
        return DB::transaction(function () use ($scope, $period) {
            DB::table('document_sequences')->insertOrIgnore([
                'scope' => $scope,
                'period' => $period,
                'last_value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $row = DB::table('document_sequences')
                ->where('scope', $scope)
                ->where('period', $period)
                ->lockForUpdate()
                ->first();

            $next = $row->last_value + 1;

            DB::table('document_sequences')
                ->where('id', $row->id)
                ->update(['last_value' => $next, 'updated_at' => now()]);

            return $next;
        }, 3);
    }
}
