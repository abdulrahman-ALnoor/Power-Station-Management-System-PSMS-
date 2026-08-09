<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use App\Http\Requests\ServiceRequest\StoreServiceRequestRequest;
use App\Http\Requests\ServiceRequest\UpdateServiceRequestRequest;
use App\Http\Resources\ServiceRequest\ServiceRequestResource;
use App\Traits\ApiResponse;

class ServiceRequestController extends Controller
{
    //
    use ApiResponse;


    // Get all service requests
    // use the ServiceRequestResource to format the response
    // use the with() method to eager load the related models
    public function index()
    {
        $serviceRequests = ServiceRequest::with([
            'meter',
            'customer',
            'creator',
            'assignedEngineer',
        ])
            ->latest()
            ->get();

        return $this->success(
            'Service requests retrieved successfully.',
            ServiceRequestResource::collection($serviceRequests)
        );
    }

    // Store a newly created service request in storage.
    // use the ServiceRequestResource to format the response
    // use the with() method to eager load the related models
    // use the StoreServiceRequestRequest to validate the request data
    // send id for meter & customer & creator & assignedEngineer
    public function store(StoreServiceRequestRequest $request)
    {
        $serviceRequest = ServiceRequest::create(
            $request->validated()
        );

        $serviceRequest->load([
            'meter',
            'customer',
            'creator',
            'assignedEngineer',
        ]);

        return $this->success(
            'Service request created successfully.',
            new ServiceRequestResource($serviceRequest),
            201
        );
    }



    // Get service request by ID
    // use the ServiceRequestResource to format the response
    // use the load method to eager load the related models
    public function show(ServiceRequest $serviceRequest)
    {
        $serviceRequest->load([
            'meter',
            'customer',
            'creator',
            'assignedEngineer',
        ]);

        return $this->success(
            'Service request retrieved successfully.',
            new ServiceRequestResource($serviceRequest)
        );
    }



    // Update the specified service request in storage.
    // use the ServiceRequestResource to format the response
    // use the load method to eager load the related models
    // use the UpdateServiceRequestRequest to validate the request data
    public function update(
        UpdateServiceRequestRequest $request,
        ServiceRequest $serviceRequest
    ) {
        $data = $request->validated();

        $serviceRequest->update($data);

        $serviceRequest->load([
            'meter',
            'customer',
            'creator',
            'assignedEngineer',
        ]);

        return $this->success(
            'Service request updated successfully.',
            new ServiceRequestResource($serviceRequest)
        );
    }


    // Delete the specified service request from storage.
    // use the ServiceRequestResource to format the response    

    public function destroy(ServiceRequest $serviceRequest)
    {
        $serviceRequest->delete();

        return $this->success(
            'Service request deleted successfully.'
        );
    }
    
    // the function with out route
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