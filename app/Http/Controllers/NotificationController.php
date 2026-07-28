<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    //

    // Get all notifications
    public function index()
    {
        $notifications = Notification::with([
            'customer',
            'meterReading',
            'invoice',
        ])
            ->latest()
            ->paginate(10);

        return response()->json($notifications);
    }


    // Get notification by ID
    public function show(Notification $notification)
    {
        $notification->load([
            'customer',
            'meterReading',
            'invoice',
        ]);

        return response()->json($notification);
    }


    // Get notifications by customer
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
