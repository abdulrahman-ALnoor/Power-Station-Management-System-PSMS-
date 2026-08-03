<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsumptionCharge extends Model
{
    //


    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'meter_id',
        'meter_reading_id',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
        ];
    }

    // the customer() function defines a belongsTo relationship with the Customer model.
    // It allows you to access the customer associated with the consumption charge.
    // the relationship is one-to-many, where one customer can have many consumption charges.
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // the meter() function defines a belongsTo relationship with the Meter model.
    // It allows you to access the meter associated with the consumption charge.
    // the realationship is one-to-many, where one meter can have many consumption charges.
    public function meter()
    {
        return $this->belongsTo(Meter::class);
    }

    // the meterReading() function defines a belongsTo relationship with the MeterReading model.
    // It allows you to access the meter reading associated with the consumption charge.
    //the relationship is one-to-one, where each consumption charge is associated with one meter reading.
    public function meterReading()
    {
        return $this->belongsTo(MeterReading::class);
    }

    // the invoices() function defines a hasMany relationship with the Invoice model.
    // It allows you to access the invoices associated with the consumption charge.
    // the relationship is one-to-many, where one consumption charge can have many invoices.
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
