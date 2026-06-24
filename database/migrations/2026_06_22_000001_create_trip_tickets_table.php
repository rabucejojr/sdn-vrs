<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 30)->unique();
            $table->date('date_filed');
            $table->text('purpose');
            $table->date('date_of_travel');
            $table->time('time_departure')->nullable();
            $table->time('time_return')->nullable();
            $table->string('destination');
            $table->enum('status', ['pending', 'approved', 'disapproved', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_tickets');
    }
};
