<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'invoice_number' => $this->invoice_number,
            'customer' => [
                'id' => $this->customer?->id,
                'name' => $this->customer?->full_name,
            ],
            'consumption_charge' => [
                'id' => $this->consumptionCharge?->id,
                'total_amount' => $this->consumptionCharge?->total_amount,
                'remaining_amount' => $this->consumptionCharge?->remaining_amount,
            ],
            'paid_amount' => $this->paid_amount,
            'remaining_balance' => $this->remaining_balance,
            'status' => $this->status,
            'payment_notes' => $this->payment_notes,
            'created_at' => $this->created_at,
        ];
    }
}
