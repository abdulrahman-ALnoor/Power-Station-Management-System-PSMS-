<?php

namespace App\Http\Controllers\Api;


use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests\Notification\StoreNotificationRequest;
use App\Http\Requests\Notification\UpdateNotificationRequest;
use App\Http\Resources\Notification\NotificationResource;
use App\Traits\ApiResponse;

class NotificationController extends Controller
{
    use ApiResponse;


    // Get all notifications
    // user the NotificationResource to format the response
    //
    public function index()
    {
        $notifications = Notification::with([
            'customer',
            'meterReading',
            'invoice',
        ])
            ->latest()
            ->get();

        return $this->success(
            'Notifications retrieved successfully.',
            NotificationResource::collection($notifications)
        );
    }


    // Store a newly created notification in storage.
    // use the StoreNotificationRequest to validate the request data
    // use the NotificationResource to format the response
    // send id for customer &meterReading & invoice
    public function store(StoreNotificationRequest $request)
    {
        $notification = Notification::create($request->validated());

        $notification->load([
            'customer',
            'meterReading',
            'invoice',
        ]);

        return $this->success(
            'Notification created successfully.',
            new NotificationResource($notification),
            201
        );
    }

    // Get notification by ID
    public function show(Notification $notification)
    {
        $notification->load([
            'customer',
            'meterReading',
            'invoice',
        ]);

        return $this->success(
            'Notification retrieved successfully.',
            new NotificationResource($notification)
        );
    }


    // Update the specified notification in storage.
    // use the UpdateNotificationRequest to validate the request data
    // use the NotificationResource to format the response
    // send id for customer &meterReading & invoice
    public function update(UpdateNotificationRequest $request, Notification $notification)
    {
        $notification->update($request->validated());

        $notification->load([
            'customer',
            'meterReading',
            'invoice',
        ]);

        return $this->success(
            'Notification updated successfully.',
            new NotificationResource($notification)
        );
    }



    public function destroy(Notification $notification)
    {
        $notification->delete();

        return $this->success(
            'Notification deleted successfully.',
            new NotificationResource($notification)
        );
    }


    // Get notifications by customer
    // 
    public function showByCustomer($customerId)
    {
        $notifications = Notification::with([
            'customer',
            'meterReading',
            'invoice',
        ])
            ->where('customer_id', $customerId)
            ->latest()
            ->get();

        return response()->json($notifications);
    }
}
