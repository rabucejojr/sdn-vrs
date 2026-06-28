<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_orders', function (Blueprint $table) {
            $table->id();
            $table->string('travel_order_number', 20)->unique();
            $table->foreignId('trip_ticket_id')->nullable()->constrained('trip_tickets')->nullOnDelete();
            $table->foreignId('issued_to')->constrained('users');
            $table->text('purpose');
            $table->string('destination');
            $table->enum('destination_scope', ['within_sdn', 'outside_sdn'])->default('within_sdn');
            $table->date('date_start');
            $table->date('date_end');
            $table->time('time_departure')->nullable();
            $table->time('time_return')->nullable();
            $table->string('transportation_mode', 50);
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('fund_source')->nullable();
            $table->boolean('expense_actual')->default(false);
            $table->boolean('expense_per_diem')->default(false);
            $table->boolean('expense_per_diem_accommodation')->default(false);
            $table->boolean('expense_per_diem_subsistence')->default(false);
            $table->boolean('expense_per_diem_incidental')->default(false);
            $table->boolean('expense_transportation')->default(false);
            $table->string('approving_officer');
            $table->string('approving_position');
            $table->enum('status', ['draft', 'issued', 'cancelled'])->default('draft');
            $table->text('remarks')->nullable();
            $table->foreignId('issued_by')->constrained('users');
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_orders');
    }
};
