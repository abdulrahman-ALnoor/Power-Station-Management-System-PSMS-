<?php

namespace App\Http\Resources\ServiceRequest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestResource extends JsonResource
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

            'meter' => $this->whenLoaded('meter', fn() => [
                'id' => $this->meter->id,
                'meter_number' => $this->meter->meter_number,
            ]),

            'customer' => $this->whenLoaded('customer', fn() => [
                'id' => $this->customer->id,
                'full_name' => $this->customer->full_name,
            ]),

            'creator' => $this->whenLoaded('creator', fn() => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),

            'assigned_engineer' => $this->whenLoaded('assignedEngineer', fn() => [
                'id' => $this->assignedEngineer->id,
                'name' => $this->assignedEngineer->name,
            ]),

            'request_type' => $this->request_type,

            'priority' => $this->priority,

            'status' => $this->status,

            'description' => $this->description,

            'completed_at' => $this->completed_at?->toDateTimeString(),

            'created_at' => $this->created_at?->toDateTimeString(),

            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
