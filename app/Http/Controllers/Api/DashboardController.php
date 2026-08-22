<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Dashboard\DashboardStatisticsResource;
use App\Http\Resources\InvoiceResource;
use App\Models\ConsumptionCharge;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\Invoice;
use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * Display the main dashboard statistics (كل بيانات الداشبورد دفعة وحدة).
     */
    public function index(Request $request)
    {
        return $this->success(
            'Dashboard data retrieved successfully.',
            [
                'statistics' => new DashboardStatisticsResource($this->buildStatistics()),
                'monthly_revenue_chart' => $this->buildMonthlyRevenueChart(),
                'electricity_consumption_chart' => $this->buildElectricityConsumptionChart(
                    $request->query('period', 'monthly')
                ),
                'equipment_status' => $this->buildEquipmentStatus(),
                'latest_readings' => $this->buildLatestReadings(),
                'latest_service_requests' => $this->buildLatestServiceRequests(),
                'latest_invoices' => $this->buildLatestInvoices(),
            ]
        );
    }

    public function statistics()
    {
        return $this->success(
            'Dashboard statistics retrieved successfully.',
            new DashboardStatisticsResource($this->buildStatistics())
        );
    }

    public function monthlyRevenueChart()
    {
        return $this->success(
            'Monthly revenue chart retrieved successfully.',
            $this->buildMonthlyRevenueChart()
        );
    }

    public function electricityConsumptionChart(Request $request)
    {
        return $this->success(
            'Electricity consumption chart retrieved successfully.',
            $this->buildElectricityConsumptionChart($request->query('period', 'monthly'))
        );
    }

    public function equipmentStatus()
    {
        return $this->success(
            'Equipment status retrieved successfully.',
            $this->buildEquipmentStatus()
        );
    }

    public function latestReadings()
    {
        return $this->success(
            'Latest meter readings retrieved successfully.',
            $this->buildLatestReadings()
        );
    }

    public function latestServiceRequests()
    {
        return $this->success(
            'Latest service requests retrieved successfully.',
            $this->buildLatestServiceRequests()
        );
    }

    public function latestInvoices()
    {
        return $this->success(
            'Latest invoices retrieved successfully.',
            InvoiceResource::collection($this->buildLatestInvoices())
        );
    }

    // ====================================================================
    // ==================== دوال داخلية (منطق الحسابات) ===================
    // ====================================================================

    private function buildStatistics(): array
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

    /**
     * بيانات الإيرادات الشهرية لآخر 12 شهر (بما فيها الشهر الحالي).
     */
    private function buildMonthlyRevenueChart(): array
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

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            Carbon::setLocale('ar');
            $labels[] = $date->translatedFormat('F Y');

            $revenue = $revenues->first(function ($item) use ($date) {
                return $item->year == $date->year && $item->month == $date->month;
            });

            $values[] = $revenue ? (float) $revenue->total : 0;
        }

        return [
            'total' => array_sum($values),
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * بيانات استهلاك الكهرباء — يومي (آخر 30 يوم)، أسبوعي (4-5 أسابيع)، شهري (آخر 12 شهر).
     */
    private function buildElectricityConsumptionChart(string $period = 'monthly'): array
    {
        $labels = [];
        $values = [];

        switch ($period) {
            case 'daily':
                $consumptions = MeterReading::query()
                    ->selectRaw("DAY(reading_date) as day, SUM(consumption) as total")
                    ->whereMonth('reading_date', now()->month)
                    ->whereYear('reading_date', now()->year)
                    ->groupByRaw("DAY(reading_date)")
                    ->orderByRaw("DAY(reading_date)")
                    ->get()
                    ->keyBy('day');

                $daysInMonth = now()->daysInMonth;

                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $labels[] = $day;
                    $values[] = isset($consumptions[$day]) ? (float) $consumptions[$day]->total : 0;
                }
                break;

            case 'weekly':
                $consumptions = MeterReading::query()
                    ->selectRaw("CEIL(DAY(reading_date) / 7) as week, SUM(consumption) as total")
                    ->whereMonth('reading_date', now()->month)
                    ->whereYear('reading_date', now()->year)
                    ->groupByRaw("CEIL(DAY(reading_date) / 7)")
                    ->orderByRaw("week")
                    ->get()
                    ->keyBy('week');

                for ($week = 1; $week <= 5; $week++) {
                    $labels[] = "الأسبوع {$week}";
                    $values[] = isset($consumptions[$week]) ? (float) $consumptions[$week]->total : 0;
                }
                break;

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
                    $values[] = isset($consumptions[$key]) ? (float) $consumptions[$key]->total : 0;
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

    private function buildEquipmentStatus(): array
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

    private function buildLatestReadings(int $limit = 5): array
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

    private function buildLatestServiceRequests(int $limit = 5): array
    {
        return ServiceRequest::query()
            ->with(['customer:id,full_name'])
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

    private function buildLatestInvoices(int $limit = 5)
    {
        return Invoice::query()
            ->with(['customer:id,full_name'])
            ->latest()
            ->take($limit)
            ->get();
    }
}
