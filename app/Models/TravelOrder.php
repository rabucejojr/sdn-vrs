<?php

namespace App\Models;

use App\Support\DocumentNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TravelOrder extends Model
{
    const TRANSPORTATION_MODES = [
        'government_vehicle' => 'Government Vehicle',
        'commercial_flight' => 'Commercial Flight',
        'bus' => 'Bus',
        'ferry' => 'Ferry',
        'motorcycle' => 'Motorcycle',
        'private_vehicle' => 'Private Vehicle',
        'ride_sharing' => 'Ride-sharing',
        'others' => 'Others',
    ];

    protected $fillable = [
        'travel_order_number',
        'trip_ticket_id',
        'issued_to',
        'purpose',
        'destination',
        'destination_scope',
        'date_start',
        'date_end',
        'time_departure',
        'time_return',
        'transportation_mode',
        'vehicle_id',
        'fund_source',
        'expense_actual',
        'expense_per_diem',
        'expense_per_diem_accommodation',
        'expense_per_diem_subsistence',
        'expense_per_diem_incidental',
        'expense_transportation',
        'expense_transportation_official_vehicle',
        'expense_transportation_public_conveyance',
        'expense_transportation_others',
        'approving_officer',
        'approving_position',
        'regional_director',
        'regional_director_position',
        'status',
        'remarks',
        'issued_by',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'date_start' => 'date',
            'date_end' => 'date',
            'issued_at' => 'datetime',
            'expense_actual' => 'boolean',
            'expense_per_diem' => 'boolean',
            'expense_per_diem_accommodation' => 'boolean',
            'expense_per_diem_subsistence' => 'boolean',
            'expense_per_diem_incidental' => 'boolean',
            'expense_transportation' => 'boolean',
            'expense_transportation_official_vehicle' => 'boolean',
            'expense_transportation_public_conveyance' => 'boolean',
            'expense_transportation_others' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($to) {
            $to->travel_order_number = DocumentNumber::travelOrder();
            $to->regional_director ??= config('organization.regional_director');
            $to->regional_director_position ??= config('organization.regional_director_position');
        });
    }

    public function getRouteKeyName(): string
    {
        return 'travel_order_number';
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function tripTicket(): BelongsTo
    {
        return $this->belongsTo(TripTicket::class);
    }

    public function issuedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_to');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(TravelOrderPassenger::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TravelOrderLog::class)->latest();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isMultiDay(): bool
    {
        return $this->date_start->toDateString() !== $this->date_end->toDateString();
    }

    public function travelDateLabel(): string
    {
        if (! $this->isMultiDay()) {
            return $this->date_start->format('F j, Y');
        }

        if ($this->date_start->format('Y') !== $this->date_end->format('Y')) {
            return $this->date_start->format('F j, Y').' – '.$this->date_end->format('F j, Y');
        }

        if ($this->date_start->format('m') !== $this->date_end->format('m')) {
            return $this->date_start->format('F j').' – '.$this->date_end->format('F j, Y');
        }

        return $this->date_start->format('F j').' – '.$this->date_end->format('j, Y');
    }

    public function transportationModeLabel(): string
    {
        return self::TRANSPORTATION_MODES[$this->transportation_mode] ?? $this->transportation_mode;
    }

    public function isOutsideSdn(): bool
    {
        return $this->destination_scope === 'outside_sdn';
    }
}
