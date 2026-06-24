<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;

class VehicleApiController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Vehicle::orderBy('name')->get(['id', 'name', 'plate_number', 'is_active'])
        );
    }

    public function show(Vehicle $vehicle): JsonResponse
    {
        return response()->json(
            $vehicle->only('id', 'name', 'plate_number', 'is_active', 'created_at')
        );
    }
}
