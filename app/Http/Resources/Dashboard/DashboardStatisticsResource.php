<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardStatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'users_count' => $this['users_count'],
            'customers_count' => $this['customers_count'],
            'meters_count' => $this['meters_count'],
            'service_requests_count' => $this['service_requests_count'],
            'monthly_revenue' => $this['monthly_revenue'],
            'uncollected_this_month' => $this['uncollected_this_month'],
        ];
    }
}
