<?php

// date_of_travel retained for backward compatibility.
// Use date_start and date_end going forward. date_of_travel = date_start for all new records.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_tickets', function (Blueprint $table) {
            $table->foreignId('vehicle_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('vehicles')
                  ->restrictOnDelete();

            $table->date('date_start')->nullable()->after('date_of_travel');
            $table->date('date_end')->nullable()->after('date_start');
        });
    }

    public function down(): void
    {
        Schema::table('trip_tickets', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn(['vehicle_id', 'date_start', 'date_end']);
        });
    }
};
