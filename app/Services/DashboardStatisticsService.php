<?php

namespace App\Services;

use App\Models\ConsumptionCharge;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\MeterReading;
use App\Models\Meter;
use App\Models\ServiceRequest;
use App\Models\Equipment;
use App\Models\User;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardStatisticsService
{
    public function getStatistics(): array
    {
        return [
            'users_count' => User::count(),

            'customers_count' => Customer::count(),

            'meters_count' => Meter::count(),

            'service_requests_count' => ServiceRequest::count(),

            'monthly_revenue' => Invoice::query()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('paid_amount'),

            'uncollected_this_month' => ConsumptionCharge::query()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('remaining_amount'),
        ];
    }


    // function for admine 
    // this function is used to get the monthly revenue chart data for the last 12 months
    public function getMonthlyRevenueChart(): array
    {
        $revenues = Invoice::query()
            ->selectRaw("
            YEAR(created_at) as year,
            MONTH(created_at) as month,
            SUM(paid_amount) as total
        ")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupByRaw("YEAR(created_at), MONTH(created_at)")
            ->orderByRaw("YEAR(created_at), MONTH(created_at)")
            ->get();

        $labels = [];
        $values = [];

        // the last 12 months including the current month
        for ($i = 11; $i >= 0; $i--) {

            $date = Carbon::now()->subMonths($i);
            Carbon::setLocale('ar');
            $labels[] = $date->translatedFormat('F Y');

            $revenue = $revenues->first(function ($item) use ($date) {
                return $item->year == $date->year
                    && $item->month == $date->month;
            });

            $values[] = $revenue
                ? (float) $revenue->total
                : 0;
        }

        $totalRevenue = array_sum($values);

        return [
            'total' => $totalRevenue,
            'labels' => $labels,
            'values' => $values,
        ];
       
    }



    public function getElectricityConsumptionChart(string $period = 'monthly'): array
    {
        $labels = [];
        $values = [];

        switch ($period) {

            /**
             * ==========================
             * the last 30 Days
             * ==========================
             */
            case 'daily':

                $consumptions = MeterReading::query()
                    ->selectRaw("
                    DAY(reading_date) as day,
                    SUM(consumption) as total
                ")
                    ->whereMonth('reading_date', now()->month)
                    ->whereYear('reading_date', now()->year)
                    ->groupByRaw("DAY(reading_date)")
                    ->orderByRaw("DAY(reading_date)")
                    ->get()
                    ->keyBy('day');

                $daysInMonth = now()->daysInMonth;

                for ($day = 1; $day <= $daysInMonth; $day++) {

                    $labels[] = $day;

                    $values[] = isset($consumptions[$day])
                        ? (float) $consumptions[$day]->total
                        : 0;
                }

                break;

            /**
             * ==========================
             *  the last  4 Weekly 
             * ==========================
             */
            case 'weekly':

                $consumptions = MeterReading::query()
                    ->selectRaw("
                    CEIL(DAY(reading_date) / 7) as week,
                    SUM(consumption) as total
                ")
                    ->whereMonth('reading_date', now()->month)
                    ->whereYear('reading_date', now()->year)
                    ->groupByRaw("CEIL(DAY(reading_date) / 7)")
                    ->orderByRaw("week")
                    ->get()
                    ->keyBy('week');

                for ($week = 1; $week <= 5; $week++) {

                    $labels[] = "الأسبوع {$week}";

                    $values[] = isset($consumptions[$week])
                        ? (float) $consumptions[$week]->total
                        : 0;
                }

                break;

            /**
             * ==========================
             *  the last 12 Monthly 
             * ==========================
             */
            case 'monthly':
            default:

                $consumptions = MeterReading::query()
                    ->selectRaw("
                    YEAR(reading_date) as year,
                    MONTH(reading_date) as month,
                    SUM(consumption) as total
                ")
                    ->where('reading_date', '>=', now()->subMonths(11)->startOfMonth())
                    ->groupByRaw("YEAR(reading_date), MONTH(reading_date)")
                    ->orderByRaw("YEAR(reading_date), MONTH(reading_date)")
                    ->get()
                    ->keyBy(function ($item) {
                        return sprintf('%04d-%02d', $item->year, $item->month);
                    });

                for ($i = 11; $i >= 0; $i--) {

                    $date = now()->subMonths($i);

                    $key = $date->format('Y-m');

                    $labels[] = $date->translatedFormat('M Y');

                    $values[] = isset($consumptions[$key])
                        ? (float) $consumptions[$key]->total
                        : 0;
                }

                break;
        }

        return [
            'period' => $period,
            'total_consumption' => array_sum($values),
            'unit' => 'kWh',
            'labels' => $labels,
            'values' => $values,
        ];
    }




    public function getEquipmentStatus(): array
    {
        $equipment = Equipment::query()
            ->selectRaw("
            COUNT(*) as total_equipment,
            SUM(CASE WHEN user_id IS NOT NULL THEN 1 ELSE 0 END) as used_equipment,
            SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance_equipment,
            SUM(CASE WHEN status = 'damaged' THEN 1 ELSE 0 END) as damaged_equipment,
            SUM(CASE WHEN status = 'lost' THEN 1 ELSE 0 END) as lost_equipment
        ")
            ->first();

        return [
            'total_equipment' => (int) $equipment->total_equipment,
            'used_equipment' => (int) $equipment->used_equipment,
            'maintenance_equipment' => (int) $equipment->maintenance_equipment,
            'damaged_equipment' => (int) $equipment->damaged_equipment,
            'lost_equipment' => (int) $equipment->lost_equipment,
        ];
    }



    public function getLatestReadings(int $limit = 5): array
    {
        return MeterReading::query()
            ->with([
                'meter:id,meter_number,customer_id',
                'meter.customer:id,full_name',
                'creator:id,name',
            ])
            ->latest('reading_date')
            ->take($limit)
            ->get()
            ->map(function ($reading) {

                return [

                    'id' => $reading->id,

                    'meter_number' => $reading->meter?->meter_number,

                    'customer_name' => $reading->meter?->customer?->full_name,

                    'reader_name' => $reading->creator?->name,

                    'reading' => (float) $reading->current_reading,

                    'consumption' => (float) $reading->consumption,

                    'reading_date' => $reading->reading_date,

                    'status' => $reading->status,

                ];
            })
            ->toArray();
    }




    public function getLatestServiceRequests(int $limit = 5): array
    {
        return ServiceRequest::query()
            ->with([
                'customer:id,full_name',
            ])
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($request) {

                return [

                    'id' => $request->id,

                    'request_number' => $request->request_number,

                    'customer_name' => $request->customer?->full_name,

                    'request_type' => $request->request_type,

                    'priority' => $request->priority,

                    'status' => $request->status,

                    'created_at' => $request->created_at,

                ];
            })
            ->toArray();
    }


    public function getLatestInvoices(int $limit = 5)
    {
        return Invoice::query()
            ->with([
                'customer:id,full_name',
            ])
            ->latest()
            ->take($limit)
            ->get();
    }
}
