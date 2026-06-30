<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            $table->string('regional_director')->nullable()->after('approving_position');
            $table->string('regional_director_position')->nullable()->after('regional_director');
        });

        DB::table('travel_orders')->whereNull('regional_director')->update([
            'regional_director' => config('organization.regional_director'),
            'regional_director_position' => config('organization.regional_director_position'),
        ]);
    }

    public function down(): void
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            $table->dropColumn(['regional_director', 'regional_director_position']);
        });
    }
};
