<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VehicleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Vehicles/Index', [
            'vehicles' => Vehicle::orderBy('name')->get(['id', 'name', 'plate_number', 'is_active']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Vehicles/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'plate_number' => ['required', 'string', 'max:20', 'unique:vehicles,plate_number'],
            'is_active' => ['boolean'],
        ]);

        DB::transaction(function () use ($request) {
            $active = $request->boolean('is_active');

            if ($active) {
                Vehicle::query()->update(['is_active' => false]);
            } elseif (! Vehicle::where('is_active', true)->exists()) {
                throw ValidationException::withMessages([
                    'is_active' => 'At least one vehicle must remain active.',
                ]);
            }

            Vehicle::create([
                ...$request->only('name', 'plate_number'),
                'is_active' => $active,
            ]);
        });

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle added.');
    }

    public function edit(Vehicle $vehicle): Response
    {
        return Inertia::render('Admin/Vehicles/Edit', [
            'vehicle' => $vehicle->only('id', 'name', 'plate_number', 'is_active'),
        ]);
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'plate_number' => ['required', 'string', 'max:20', 'unique:vehicles,plate_number,'.$vehicle->id],
            'is_active' => ['boolean'],
        ]);

        DB::transaction(function () use ($request, $vehicle) {
            $active = $request->boolean('is_active');

            if ($active) {
                Vehicle::where('id', '!=', $vehicle->id)->update(['is_active' => false]);
            } elseif ($vehicle->is_active && ! Vehicle::where('id', '!=', $vehicle->id)->where('is_active', true)->exists()) {
                throw ValidationException::withMessages([
                    'is_active' => 'At least one vehicle must remain active.',
                ]);
            }

            $vehicle->update([
                ...$request->only('name', 'plate_number'),
                'is_active' => $active,
            ]);
        });

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle updated.');
    }
}
