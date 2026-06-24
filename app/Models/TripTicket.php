<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripTicket extends Model
{
    protected $fillable = [
        'vehicle_id',
        'ticket_number',
        'date_filed',
        'purpose',
        'date_of_travel',
        'date_start',
        'date_end',
        'time_departure',
        'time_return',
        'destination',
        'status',
        'requested_by',
        'approved_by',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date_filed'     => 'date',
            'date_of_travel' => 'date',
            'date_start'     => 'date',
            'date_end'       => 'date',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($ticket) {
            $now   = now();
            $month = $now->format('m');
            $year  = $now->format('Y');

            if (empty($ticket->vehicle_id)) {
                $ticket->vehicle_id = Vehicle::getActive()->id;
            }

            $count = TripTicket::where('vehicle_id', $ticket->vehicle_id)
                               ->whereBetween('created_at', [
                                   $now->copy()->startOfMonth()->toDateTimeString(),
                                   $now->copy()->endOfMonth()->toDateTimeString(),
                               ])
                               ->count() + 1;

            $ticket->ticket_number  = 'Crosswind-' . $year . '-' . $month . '-'
                                    . str_pad($count, 4, '0', STR_PAD_LEFT);
            $ticket->date_filed     = $now->toDateString();
            $ticket->date_of_travel = $ticket->date_start;
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ticket_number';
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TripTicketLog::class)->orderBy('created_at', 'asc');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeConflicting(Builder $query, string $dateStart, string $dateEnd, ?int $excludeId = null): Builder
    {
        return $query
            ->where('status', 'approved')
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where(fn ($q) => $q
                ->whereBetween('date_start', [$dateStart, $dateEnd])
                ->orWhereBetween('date_end', [$dateStart, $dateEnd])
                ->orWhere(fn ($q2) => $q2
                    ->where('date_start', '<=', $dateStart)
                    ->where('date_end', '>=', $dateEnd))
            );
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isMultiDay(): bool
    {
        return $this->date_end
            && $this->date_end->ne($this->date_start);
    }

    public function travelDateLabel(): string
    {
        $start = Carbon::parse($this->date_start);
        $end   = Carbon::parse($this->date_end ?? $this->date_start);

        if ($this->isMultiDay()) {
            return $start->format('M d') . ' – ' . $end->format('M d, Y');
        }

        return $start->format('M d, Y');
    }
}
