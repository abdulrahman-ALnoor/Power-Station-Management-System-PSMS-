<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeterResource extends JsonResource
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

            'meter_number' => $this->meter_number,
            'qr_code' => $this->qr_code,
            'installation_date' => $this->installation_date,
            'installation_location' => $this->installation_location,
            'status' => $this->status,

            'installer' => [
                'id' => $this->installer?->id,
                'name' => $this->installer?->name,
            ],

            'creator' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}