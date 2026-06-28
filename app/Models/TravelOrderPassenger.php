<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelOrderPassenger extends Model
{
    protected $fillable = ['travel_order_id', 'user_id', 'name', 'designation'];

    public function travelOrder(): BelongsTo
    {
        return $this->belongsTo(TravelOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
