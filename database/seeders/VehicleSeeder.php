<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicle = Vehicle::firstOrCreate(
            ['plate_number' => 'SJJ 504'],
            [
                'name'      => 'Crosswind',
                'is_active' => true,
            ]
        );

        DB::table('trip_tickets')
            ->whereNull('vehicle_id')
            ->update(['vehicle_id' => $vehicle->id]);
    }
}
