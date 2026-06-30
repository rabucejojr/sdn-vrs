<?php

namespace App\Models;

use App\Support\DocumentNumber;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

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
        'driver_name',
    ];

    protected function casts(): array
    {
        return [
            'date_filed' => 'date',
            'date_of_travel' => 'date',
            'date_start' => 'date',
            'date_end' => 'date',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($ticket) {
            $now = now();
            if (empty($ticket->vehicle_id)) {
                $ticket->vehicle_id = Vehicle::getActive()->id;
            }
            $ticket->ticket_number = DocumentNumber::tripTicket();
            $ticket->date_filed = $now->toDateString();
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

    public function travelOrder(): HasOne
    {
        return $this->hasOne(TravelOrder::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeConflicting(
        Builder $query,
        string $dateStart,
        string $dateEnd,
        ?int $excludeId = null,
        ?int $vehicleId = null,
    ): Builder {
        return $query
            ->where('status', 'approved')
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->whereDate('date_start', '<=', $dateEnd)
            ->whereDate('date_end', '>=', $dateStart);
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
        $end = Carbon::parse($this->date_end ?? $this->date_start);

        if ($this->isMultiDay()) {
            return $start->format('M d').' – '.$end->format('M d, Y');
        }

        return $start->format('M d, Y');
    }

    public function formattedDriverName(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->driver_name), -1, PREG_SPLIT_NO_EMPTY);

        if (! $parts) {
            return '';
        }

        $lastName = Str::title(Str::lower(array_pop($parts)));

        if ($parts === []) {
            return $lastName;
        }

        $firstInitial = Str::upper(Str::substr(array_shift($parts), 0, 1));
        $middleInitial = $parts === []
            ? ''
            : Str::upper(Str::substr(array_shift($parts), 0, 1));

        return "{$firstInitial}{$middleInitial}{$lastName}";
    }
}
