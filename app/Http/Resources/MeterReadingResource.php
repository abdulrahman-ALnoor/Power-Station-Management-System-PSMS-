<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeterReadingResource extends JsonResource
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
            'meter' => [
                'id' => $this->meter?->id,
                'meter_number' => $this->meter?->meter_number,
                'customer' => [
                    'id' => $this->meter?->customer?->id,
                    'full_name' => $this->meter?->customer?->full_name,
                ],
            ],
            'creator' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ],
            'previous_reading' => $this->previous_reading,
            'current_reading' => $this->current_reading,
            'consumption' => $this->consumption,
            'price_per_kwh' => $this->price_per_kwh,
            'reading_cost' => $this->reading_cost,
            'reading_date' => $this->reading_date,
            'reading_method' => $this->reading_method,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
        ];
    }
}
