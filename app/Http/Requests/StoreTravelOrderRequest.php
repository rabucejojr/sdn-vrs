<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasTravelOrderRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreTravelOrderRequest extends FormRequest
{
    use HasTravelOrderRules;

    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return $this->travelOrderRules();
    }
}
