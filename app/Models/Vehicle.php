<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = ['name', 'plate_number', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function tripTickets(): HasMany
    {
        return $this->hasMany(TripTicket::class);
    }

    public function getLabelAttribute(): string
    {
        return "{$this->name} ({$this->plate_number})";
    }

    public static function getActive(): self
    {
        return static::where('is_active', true)->firstOrFail();
    }
}
