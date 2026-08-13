<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'customer_number'     => $this->customer_number,
            'full_name'           => $this->full_name,
            'customer_type'       => $this->customer_type,
            'phone'               => $this->phone,
            'alternative_phone'   => $this->alternative_phone,
            'address_description' => $this->address_description,
            'notes'               => $this->notes,
            'created_by'          => $this->created_by,
            'creator'             => $this->whenLoaded('creator', function () {
                return [
                    'id'   => $this->creator?->id,
                    'name' => $this->creator?->name,
                ];
            }),
            'created_at'          => $this->created_at?->toDateTimeString(),
        ];
    }
}
