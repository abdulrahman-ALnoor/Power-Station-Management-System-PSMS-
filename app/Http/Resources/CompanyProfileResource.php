<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
            'id'                 => $this->id,
            'company_name'       => $this->company_name,
            'logo'               => $this->logo,
            'address'            => $this->address,
            'whatsapp_number'    => $this->whatsapp_number,
            'support_number'     => $this->support_number,
            'currency'           => $this->currency,
            'price_per_kwh'      => $this->price_per_kwh,
            'reading_cycle_days' => $this->reading_cycle_days,
            'created_at'         => $this->created_at?->toDateTimeString(),
        ];
    }
}
