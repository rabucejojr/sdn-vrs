<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelOrderLog extends Model
{
    protected $fillable = ['travel_order_id', 'action', 'changed_by', 'remarks'];

    public function travelOrder(): BelongsTo
    {
        return $this->belongsTo(TravelOrder::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
