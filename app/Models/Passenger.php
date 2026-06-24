<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Passenger extends Model
{
    protected $fillable = ['trip_ticket_id', 'name', 'designation'];

    public function tripTicket(): BelongsTo
    {
        return $this->belongsTo(TripTicket::class);
    }
}
