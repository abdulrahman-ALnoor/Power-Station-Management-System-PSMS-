<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    //

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'meter_reading_id',
        'invoice_id',
        'notification_type',
        'message',
        'status',
        'whatsapp_message_id',
        'sent_at',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }


    // the customer() function defines a belongsTo relationship with the Customer model.
    // It allows you to access the customer associated with the notification.
    // the relationship is one-to-many, where one customer can have many notifications.
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // the meterReading() function defines a belongsTo relationship with the MeterReading model.
    // It allows you to access the meter reading associated with the notification.
    // the relationship is many-to-one, where each notification is associated with one meter reading.
    public function meterReading()
    {
        return $this->belongsTo(MeterReading::class);
    }

    // the invoice() function defines a belongsTo relationship with the Invoice model.
    // It allows you to access the invoice associated with the notification.
    // the relationship is many-to-one, where each notification is associated with one invoice.
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
