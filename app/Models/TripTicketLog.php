<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripTicketLog extends Model
{
    protected $fillable = [
        'trip_ticket_id',
        'from_status',
        'to_status',
        'changed_by',
        'remarks',
    ];

    public function tripTicket(): BelongsTo
    {
        return $this->belongsTo(TripTicket::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
