<?php

namespace App\Http\Requests\Concerns;

use App\Models\TravelOrder;
use Illuminate\Validation\Rule;

trait HasTravelOrderRules
{
    protected function travelOrderRules(): array
    {
        return [
            'issued_to' => ['required', Rule::exists('users', 'id')->where('is_active', true)],
            'purpose' => ['required', 'string', 'max:1000'],
            'destination' => ['required', 'string', 'max:255'],
            'destination_scope' => ['required', Rule::in(['within_sdn', 'outside_sdn'])],
            'date_start' => ['required', 'date'],
            'date_end' => ['required', 'date', 'after_or_equal:date_start'],
            'time_departure' => ['nullable', 'date_format:H:i'],
            'time_return' => ['nullable', 'date_format:H:i'],
            'transportation_mode' => ['required', Rule::in(array_keys(TravelOrder::TRANSPORTATION_MODES))],
            'vehicle_id' => [
                'nullable',
                Rule::exists('vehicles', 'id')->where('is_active', true),
                'required_if:transportation_mode,government_vehicle',
                Rule::prohibitedIf(fn () => $this->input('transportation_mode') !== 'government_vehicle'),
            ],
            'fund_source' => ['nullable', 'string', 'max:255'],
            'expense_actual' => ['nullable', 'boolean'],
            'expense_per_diem' => ['nullable', 'boolean'],
            'expense_per_diem_accommodation' => ['nullable', 'boolean'],
            'expense_per_diem_subsistence' => ['nullable', 'boolean'],
            'expense_per_diem_incidental' => ['nullable', 'boolean'],
            'expense_transportation' => ['nullable', 'boolean'],
            'expense_transportation_official_vehicle' => ['nullable', 'boolean'],
            'expense_transportation_public_conveyance' => ['nullable', 'boolean'],
            'expense_transportation_others' => ['nullable', 'boolean'],
            'approving_officer' => ['required', 'string', 'max:255'],
            'approving_position' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'passengers' => ['nullable', 'array', 'max:30'],
            'passengers.*.name' => ['required', 'string', 'max:255'],
            'passengers.*.designation' => ['nullable', 'string', 'max:255'],
            'passengers.*.user_id' => ['nullable', 'distinct', Rule::exists('users', 'id')->where('is_active', true)],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                if (! $validator->errors()->hasAny(['date_start', 'date_end'])
                    && $this->date('date_start')->diffInDays($this->date('date_end')) > 365) {
                    $validator->errors()->add('date_end', 'Travel may not span more than 365 days.');
                }

                if ($this->input('date_start') === $this->input('date_end')
                    && $this->filled('time_departure') && $this->filled('time_return')
                    && $this->input('time_return') <= $this->input('time_departure')) {
                    $validator->errors()->add('time_return', 'Return time must be after departure time for same-day travel.');
                }

                $this->validateExpenseChildren($validator, 'expense_per_diem', [
                    'expense_per_diem_accommodation',
                    'expense_per_diem_subsistence',
                    'expense_per_diem_incidental',
                ]);
                $this->validateExpenseChildren($validator, 'expense_transportation', [
                    'expense_transportation_official_vehicle',
                    'expense_transportation_public_conveyance',
                    'expense_transportation_others',
                ]);
            },
        ];
    }

    private function validateExpenseChildren($validator, string $parent, array $children): void
    {
        if ($this->boolean($parent)) {
            return;
        }

        foreach ($children as $child) {
            if ($this->boolean($child)) {
                $validator->errors()->add($child, 'This option requires its parent expense category.');
            }
        }
    }
}
