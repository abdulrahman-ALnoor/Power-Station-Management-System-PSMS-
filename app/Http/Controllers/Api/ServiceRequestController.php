<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Meter;
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
        $this->authorize('viewAny', ServiceRequest::class);

        $user = $request->user();

        $query = ServiceRequest::with([
            'meter',
            'customer',
            'creator',
            'assignedEngineer',
        ]);

        // فلترة على مستوى السجل حسب الدور (الأدمن يشوف الكل، Gate::before يتجاوز هذا الشرط أصلاً
        // بس نحطه بالكنترولر لأن الفلترة مو Policy، هذا استعلام قاعدة بيانات)
        if (! $user->hasRole('admin')) {
            if ($user->hasRole('engineer')) {
                // المهندس: بس الطلبات المسندة له
                $query->where('assigned_engineer_id', $user->id);
            } elseif ($user->hasRole('reader')) {
                // القارئ: بس الطلبات اللي أنشأها هو
                $query->where('created_by', $user->id);
            }
            // المحاسب: ما فيه شرط إضافي، يشوف كل الطلبات (حسب المستند)
        }

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
        $this->authorize('view', $serviceRequest);

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
        $this->authorize('update', $serviceRequest);

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
        $this->authorize('delete', $serviceRequest);

        $serviceRequest->delete();

        return $this->success(
            'Service request deleted successfully.'
        );
    }

    // تغيير حالة الطلب فقط (مو تعديل كامل) — للمهندس على طلبه المسند له بس
    public function changeStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorize('update', $serviceRequest);

        $validated = $request->validate([
            'status' => 'required|string|in:pending,in_progress,completed,rejected',
        ]);

        $serviceRequest->update(['status' => $validated['status']]);

        $serviceRequest->load([
            'meter',
            'customer',
            'creator',
            'assignedEngineer',
        ]);

        return $this->success(
            'تم تحديث حالة الطلب بنجاح.',
            new ServiceRequestResource($serviceRequest)
        );
    }

    // توجيه الطلب لمهندس معيّن — admin بس (الميدلوير يمنع أي دور ثاني من الوصول أصلاً)
    public function assignEngineer(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'assigned_engineer_id' => 'required|exists:users,id',
        ]);

        $serviceRequest->update([
            'assigned_engineer_id' => $validated['assigned_engineer_id'],
        ]);

        $serviceRequest->load([
            'meter',
            'customer',
            'creator',
            'assignedEngineer',
        ]);

        return $this->success(
            'تم توجيه الطلب للمهندس بنجاح.',
            new ServiceRequestResource($serviceRequest)
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

    return $this->success(
        'تم جلب طلبات الصيانة المسندة بنجاح.',
        ServiceRequestResource::collection($serviceRequests)
    );
}

    // 3- إضافة طلب خدمة أو صيانة جديد بواسطة القارئ (بحالة معلقة تتطلب موافقة الأدمن)
    public function storeByReader(Request $request)
    {
        $request->validate([
            'meter_id' => 'required|exists:meters,id',
            'customer_id' => 'required|exists:customers,id',
            'request_type' => 'required|in:new_connection,maintenance,disconnection',
            'description' => 'nullable|string',
        ]);

        $serviceRequest = ServiceRequest::create([
            'created_by' => $request->user()->id,
            'meter_id' => $request->meter_id,
            'customer_id' => $request->customer_id,
            'request_type' => $request->request_type,
            'description' => $request->description,
            'status' => 'pending', // بانتظار موافقة الإدارة (حسب متطلبات القارئ بالمستند)
        ]);

        $serviceRequest->load(['meter', 'customer', 'creator']);

        return $this->success(
            'تم تقديم طلب الخدمة بنجاح وهو بانتظار موافقة الإدارة.',
            new ServiceRequestResource($serviceRequest),
            201
        );
    }
}
