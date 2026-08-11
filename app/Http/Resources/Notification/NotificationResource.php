<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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

            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id'   => $this->customer->id,
                    'name' => $this->customer->name,
                ];
            }),

            'meter_reading' => $this->whenLoaded('meterReading', function () {
                return [
                    'id' => $this->meterReading->id,
                    'reading' => $this->meterReading->reading,
                ];
            }),

            'invoice' => $this->whenLoaded('invoice', function () {
                return [
                    'id' => $this->invoice->id,
                    'invoice_number' => $this->invoice->invoice_number,
                ];
            }),

            'notification_type' => $this->notification_type,

            'message' => $this->message,

            'status' => $this->status,

            'whatsapp_message_id' => $this->whatsapp_message_id,

            'sent_at' => $this->sent_at?->toDateTimeString(),

            'read_at' => $this->read_at?->toDateTimeString(),

            'created_at' => $this->created_at?->toDateTimeString(),

            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
