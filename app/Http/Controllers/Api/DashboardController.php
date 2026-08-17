<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Dashboard\DashboardStatisticsResource;
use App\Http\Resources\InvoiceResource;
use App\Services\DashboardStatisticsService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;


    /**
     * Display the main dashboard statistics.
     */
    public function index(
        Request $request,
        DashboardStatisticsService $service
    ) {
        return $this->success(
            'Dashboard data retrieved successfully.',
            [
                'statistics' => new DashboardStatisticsResource(
                    $service->getStatistics()
                ),

                'monthly_revenue_chart' => $service->getMonthlyRevenueChart(),

                'electricity_consumption_chart' => $service->getElectricityConsumptionChart(
                    $request->query('period', 'monthly')
                ),

                'equipment_status' => $service->getEquipmentStatus(),

                'latest_readings' => $service->getLatestReadings(),

                'latest_service_requests' => $service->getLatestServiceRequests(),
                'latest_invoices' => $service->getLatestInvoices(),
            ]
        );
    }




    // function for admine
    // function to get the dashboard statistics
    // This function retrieves the dashboard statistics using the DashboardStatisticsService.

     public function statistics(DashboardStatisticsService $service)
    {
        return $this->success(
            'Dashboard statistics retrieved successfully.',
            new DashboardStatisticsResource(
                $service->getStatistics()
            )
        );
    }

    // function for admine                
    // function to get the monthly revenue chart data
    // This function retrieves the monthly revenue chart data using the DashboardStatisticsService.
    public function monthlyRevenueChart(
        DashboardStatisticsService $service
        ) {
            return $this->success(
            'Monthly revenue chart retrieved successfully.',
            $service->getMonthlyRevenueChart()
        );
    }

    // function for admine
    // function to get the electricity consumption chart data
    // function return the electricity consumption chart data for the specified period daily weekly monthly
    public function electricityConsumptionChart(
        Request $request,
        DashboardStatisticsService $service
    ) {
        return $this->success(
            'Electricity consumption chart retrieved successfully.',
            $service->getElectricityConsumptionChart(
                $request->query('period', 'monthly')
            )
        );
    }



    public function latestReadings(DashboardStatisticsService $service)
    {
        return $this->success(
            'Latest meter readings retrieved successfully.',
            $service->getLatestReadings()
        );
    }

    /**
     * Get latest service requests.
     */
    public function latestServiceRequests(DashboardStatisticsService $service)
    {
        return $this->success(
            'Latest service requests retrieved successfully.',
            $service->getLatestServiceRequests()

        );
    }


    public function latestInvoices(DashboardStatisticsService $service)
    {
        return $this->success(
            'Latest invoices retrieved successfully.',
            InvoiceResource::collection(
                $service->getLatestInvoices()
            )
        );
    }
}


