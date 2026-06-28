<?php

namespace App\Http\Requests;

use App\Models\TravelOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTravelOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'issued_to'                      => ['required', 'exists:users,id'],
            'purpose'                        => ['required', 'string', 'max:1000'],
            'destination'                    => ['required', 'string', 'max:255'],
            'destination_scope'              => ['required', Rule::in(['within_sdn', 'outside_sdn'])],
            'date_start'                     => ['required', 'date'],
            'date_end'                       => ['required', 'date', 'after_or_equal:date_start'],
            'time_departure'                 => ['nullable', 'date_format:H:i'],
            'time_return'                    => ['nullable', 'date_format:H:i'],
            'transportation_mode'            => ['required', Rule::in(array_keys(TravelOrder::TRANSPORTATION_MODES))],
            'vehicle_id'                     => ['nullable', 'exists:vehicles,id', 'required_if:transportation_mode,government_vehicle'],
            'fund_source'                    => ['nullable', 'string', 'max:255'],
            'expense_actual'                 => ['nullable', 'boolean'],
            'expense_per_diem'               => ['nullable', 'boolean'],
            'expense_per_diem_accommodation' => ['nullable', 'boolean'],
            'expense_per_diem_subsistence'   => ['nullable', 'boolean'],
            'expense_per_diem_incidental'    => ['nullable', 'boolean'],
            'expense_transportation'                      => ['nullable', 'boolean'],
            'expense_transportation_official_vehicle'     => ['nullable', 'boolean'],
            'expense_transportation_public_conveyance'    => ['nullable', 'boolean'],
            'expense_transportation_others'               => ['nullable', 'boolean'],
            'approving_officer'              => ['required', 'string', 'max:255'],
            'approving_position'             => ['required', 'string', 'max:255'],
            'remarks'                        => ['nullable', 'string', 'max:2000'],
            'passengers'                     => ['nullable', 'array'],
            'passengers.*.name'              => ['required', 'string', 'max:255'],
            'passengers.*.designation'       => ['nullable', 'string', 'max:255'],
            'passengers.*.user_id'           => ['nullable', 'exists:users,id'],
        ];
    }
}
