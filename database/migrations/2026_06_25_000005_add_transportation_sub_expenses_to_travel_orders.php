<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            $table->boolean('expense_transportation_official_vehicle')->default(false)->after('expense_transportation');
            $table->boolean('expense_transportation_public_conveyance')->default(false)->after('expense_transportation_official_vehicle');
            $table->boolean('expense_transportation_others')->default(false)->after('expense_transportation_public_conveyance');
        });
    }

    public function down(): void
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            $table->dropColumn([
                'expense_transportation_official_vehicle',
                'expense_transportation_public_conveyance',
                'expense_transportation_others',
            ]);
        });
    }
};
