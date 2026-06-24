<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'name'         => ['required', 'string', 'max:255'],
            'plate_number' => ['required', 'string', 'max:20', 'unique:vehicles,plate_number'],
            'is_active'    => ['boolean'],
        ]);

        Vehicle::create($request->only('name', 'plate_number', 'is_active'));

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
            'name'         => ['required', 'string', 'max:255'],
            'plate_number' => ['required', 'string', 'max:20', 'unique:vehicles,plate_number,' . $vehicle->id],
            'is_active'    => ['boolean'],
        ]);

        $vehicle->update($request->only('name', 'plate_number', 'is_active'));

        return redirect()->route('admin.vehicles.index')
                         ->with('success', 'Vehicle updated.');
    }
}
