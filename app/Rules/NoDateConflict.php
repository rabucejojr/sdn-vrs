<?php

namespace App\Rules;

use App\Models\TripTicket;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoDateConflict implements ValidationRule
{
    public function __construct(
        private readonly string $dateEnd,
        private readonly ?int $excludeId = null,
        private readonly ?int $vehicleId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $end = $this->dateEnd ?: $value;

        $conflict = TripTicket::conflicting($value, $end, $this->excludeId, $this->vehicleId)
            ->first(['ticket_number']);

        if ($conflict) {
            $fail("These dates overlap an existing approved reservation ({$conflict->ticket_number}). Please choose different dates.");
        }
    }
}
