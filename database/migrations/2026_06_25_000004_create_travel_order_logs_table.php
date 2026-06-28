<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_order_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_order_id')->constrained('travel_orders')->cascadeOnDelete();
            $table->string('action', 50);
            $table->foreignId('changed_by')->constrained('users');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_order_logs');
    }
};
