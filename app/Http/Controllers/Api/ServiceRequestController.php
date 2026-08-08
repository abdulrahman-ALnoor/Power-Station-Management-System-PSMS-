<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    //

    public function index()
    {
        $serviceRequests = ServiceRequest::with([
            'meter',
            'customer',
            'creator',
            'assignedEngineer',
        ])
            ->latest()
            ->paginate(10);

        return response()->json($serviceRequests);
    }

    
    //  Get a specific service request.
     
    public function show(ServiceRequest $serviceRequest)
    {
        $serviceRequest->load([
            'meter',
            'customer',
            'creator',
            'assignedEngineer',
        ]);

        return response()->json($serviceRequest);
    }

    
    // Get service requests assigned to a specific engineer.
     
    public function showByEngineer($engineerId)
    {
        $serviceRequests = ServiceRequest::with([
            'meter',
            'customer',
            'creator',
            'assignedEngineer',
        ])
            ->where('assigned_engineer_id', $engineerId)
            ->latest()
            ->get();

        return response()->json($serviceRequests);
    }
}
