<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeterReading extends Model
{
    //

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'meter_id',
        'created_by',
        'previous_reading',
        'current_reading',
        'consumption',
        'price_per_kwh',
        'reading_cost',
        'reading_date',
        'reading_method',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'previous_reading' => 'decimal:2',
            'current_reading' => 'decimal:2',
            'consumption' => 'decimal:2',
            'price_per_kwh' => 'decimal:2',
            'reading_cost' => 'decimal:2',
            'reading_date' => 'date',
        ];
    }


   

    // the creator() function defines a belongsTo relationship with the User model.
    // It allows you to access the user who created the meter reading record.
    // the relationship is one-to-many, where one user can create many meter readings.
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // the meter() function defines a belongsTo relationship with the Meter model. 
    // It allows you to access the meter associated with the meter reading.
    // the relationship is one-to-many, where one meter can have many meter readings.
    public function meter()
    {
        return $this->belongsTo(Meter::class);
    }


    // the consumptionCharge() function defines a hasOne relationship with the ConsumptionCharge model.
    // It allows you to access the consumption charge associated with the meter reading.
    // the relationship is one-to-one, where each meter reading is associated with one consumption charge.
    public function consumptionCharge()
    {
        return $this->hasOne(ConsumptionCharge::class);
    }


    // the notifications() function defines a hasMany relationship with the Notification model.
    // It allows you to access all the notifications associated with the meter reading.
    // the relationship is one-to-many, where one meter reading can have many notifications.
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
