<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    //

    // Get all equipment
    public function index()
    {
        $equipment = Equipment::with([
            'user',
            'creator',
        ])
            ->latest()
            ->paginate(10);

        return response()->json($equipment);
    }

    // Get equipment by ID
    public function show(Equipment $equipment)
    {
        $equipment->load([
            'user',
            'creator',
        ]);

        return response()->json($equipment);
    }

    // Get all equipment assigned to a specific user
    public function showByUser($userId)
    {
        $equipment = Equipment::with([
            'user',
            'creator',
        ])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return response()->json($equipment);
    }
}
