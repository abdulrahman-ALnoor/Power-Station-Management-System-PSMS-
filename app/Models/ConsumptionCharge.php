<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsumptionCharge extends Model
{
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function meter()
    {
        return $this->belongsTo(Meter::class);
    }

    public function meterReading()
    {
        return $this->belongsTo(MeterReading::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}