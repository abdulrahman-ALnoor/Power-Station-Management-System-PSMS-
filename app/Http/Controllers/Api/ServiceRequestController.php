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
    use ApiResponse;

    // Get all service requests
    public function index(Request $request)
    {
        $query = ServiceRequest::with([
            'meter',
            'customer',
            'creator',
            'assignedEngineer',
        ])->where('assigned_engineer_id', $request->user()->id);

        // فلترة حسب نوع الطلب
        if ($request->filled('request_type')) {
            $query->where('request_type', $request->request_type);
        }

        // فلترة حسب الأهمية
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // بحث بالاسم (اسم العميل) أو رقم العداد
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($sub) use ($search) {
                    $sub->where('full_name', 'like', "%{$search}%");
                })->orWhereHas('meter', function ($sub) use ($search) {
                    $sub->where('meter_number', 'like', "%{$search}%");
                });
            });
        }

        $serviceRequests = $query->latest()->paginate(10);

        return $this->success(
            'تم جلب طلبات الخدمة بنجاح.',
            ServiceRequestResource::collection($serviceRequests)
        );
    }

    // Store a newly created service request in storage.
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

    public function myDashboardStats(Request $request)
    {
        $engineerId = $request->user()->id;

        $requestsByStatus = ServiceRequest::where('assigned_engineer_id', $engineerId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalEquipment = \App\Models\Equipment::where('user_id', $engineerId)->count();

        return $this->success('تم جلب إحصائيات لوحة المهندس بنجاح.', [
            'total_requests'       => $requestsByStatus->sum(),
            'pending_requests'     => $requestsByStatus['pending'] ?? 0,
            'in_progress_requests' => $requestsByStatus['in_progress'] ?? 0,
            'completed_requests'   => $requestsByStatus['completed'] ?? 0,
            'total_equipment'      => $totalEquipment,
        ]);
    }

    public function myLatestRequests(Request $request)
    {
        $engineerId = $request->user()->id;

        $latestRequests = ServiceRequest::with([
            'meter',
            'customer',
        ])
            ->where('assigned_engineer_id', $engineerId)
            ->latest()
            ->take(5)
            ->get();

        return $this->success(
            'تم جلب أحدث الطلبات المسندة بنجاح.',
            ServiceRequestResource::collection($latestRequests)
        );
    }

    public function myMonthlyPerformance(Request $request)
    {
        $engineerId = $request->user()->id;

        $performance = ServiceRequest::where('assigned_engineer_id', $engineerId)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->selectRaw("DATE_FORMAT(completed_at, '%Y-%m') as month, count(*) as total")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->take(6)
            ->get();

        return $this->success('تم جلب الأداء الشهري بنجاح.', $performance);
    }

    // Update the specified service request in storage.
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

    // 3- إضافة طلب خدمة أو صيانة جديد بواسطة القارئ (بحالة معلقة تتطلب موافقة الأدمن)
    public function storeByReader(Request $request)
    {
        $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'type' => 'required|string',
            'description' => 'required|string',
        ]);

        $userId = $request->user()->id;

        $serviceRequest = ServiceRequest::create([
            'user_id' => $userId,
            'equipment_id' => $request->equipment_id,
            'type' => $request->type,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        return $this->success('تم تقديم طلب الخدمة بنجاح وهو بانتظار موافقة الإدارة.', $serviceRequest, 201);
    }
}