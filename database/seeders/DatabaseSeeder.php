<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(VehicleSeeder::class);

        User::create([
            'name'              => 'Administrator',
            'email'             => 'admin@psto-sdn.dost.gov.ph',
            'password'          => Hash::make('password'),
            'role'              => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name'              => 'Staff',
            'email'             => 'staff@psto-sdn.dost.gov.ph',
            'password'          => Hash::make('password'),
            'role'              => 'staff',
            'email_verified_at' => now(),
        ]);
    }
}
