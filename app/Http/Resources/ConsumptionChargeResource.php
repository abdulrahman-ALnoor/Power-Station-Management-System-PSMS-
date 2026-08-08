<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsumptionChargeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'customer' => [
                'id' => $this->customer?->id,
                'full_name' => $this->customer?->full_name,
            ],

            'meter' => [
                'id' => $this->meter?->id,
                'meter_number' => $this->meter?->meter_number,
            ],

            'meter_reading' => [
                'id' => $this->meterReading?->id,
                'reading_date' => $this->meterReading?->reading_date,
            ],

            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
            'remaining_amount' => $this->remaining_amount,
            'status' => $this->status,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}