<?php

namespace App\Http\Requests;

use App\Models\TripTicket;
use App\Rules\NoDateConflict;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class SaveTripTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var TripTicket|null $ticket */
        $ticket = $this->route('ticket');
        $dateRules = ['required', 'date'];

        if (! $ticket) {
            $dateRules[] = 'after_or_equal:today';
        } elseif ($ticket->status === 'approved') {
            $dateRules[] = new NoDateConflict(
                $this->input('date_end') ?: $this->input('date_start'),
                $ticket->id,
                $ticket->vehicle_id,
            );
        }

        return [
            'purpose' => ['required', 'string', 'max:1000'],
            'date_start' => $dateRules,
            'date_end' => ['required', 'date', 'after_or_equal:date_start'],
            'time_departure' => ['nullable', 'date_format:H:i'],
            'time_return' => ['nullable', 'date_format:H:i'],
            'destination' => ['required', 'string', 'max:255'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'passengers' => ['required', 'array', 'min:1', 'max:30'],
            'passengers.*.name' => ['required', 'string', 'max:255'],
            'passengers.*.designation' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                if (! $validator->errors()->hasAny(['date_start', 'date_end'])) {
                    $start = Carbon::parse($this->input('date_start'));
                    $end = Carbon::parse($this->input('date_end'));
                    if ($start->diffInDays($end) > 365) {
                        $validator->errors()->add('date_end', 'A reservation may not span more than 365 days.');
                    }
                }

                if ($this->input('date_start') === $this->input('date_end')
                    && $this->filled('time_departure') && $this->filled('time_return')
                    && $this->input('time_return') <= $this->input('time_departure')) {
                    $validator->errors()->add('time_return', 'Return time must be after departure time for same-day travel.');
                }

                $names = collect($this->input('passengers', []))
                    ->pluck('name')
                    ->map(fn ($name) => mb_strtolower(trim((string) $name)))
                    ->filter();
                if ($names->duplicates()->isNotEmpty()) {
                    $validator->errors()->add('passengers', 'Passenger names must be unique.');
                }
            },
        ];
    }
}
