<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meter extends Model
{
    //

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'meter_number',
        'qr_code',
        'installation_date',
        'installation_location',
        'status',
        'installed_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'installation_date' => 'date',
        ];
    }

    // the customer() function defines a belongsTo relationship with the Customer model.
    // It allows you to access the customer associated with the meter.
    // the relationship is one-to-many, where one customer can have many meters.
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // the installer() function defines a belongsTo relationship with the User model.
    // It allows you to access the user who installed the meter.
    // the relationship is one-to-many, where one user can install many meters.
    public function installer()
    {
        return $this->belongsTo(User::class, 'installed_by');
    }
    // the creator() function defines a belongsTo relationship with the User model.
    // It allows you to access the user who created the meter record.
    // the relationship is one-to-many, where one user can create many meters.
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    // the readings() function defines a hasMany relationship with the MeterReading model.
    // It allows you to access all the meter readings associated with the meter.
    // the relationship is one-to-many, where one meter can have many meter readings.
    public function readings()
    {
        return $this->hasMany(MeterReading::class);
    }

    // the consumptionCharges() function defines a hasMany relationship with the ConsumptionCharge model.
    // It allows you to access all the consumption charges associated with the meter.
    // the relationship is one-to-many, where one meter can have many consumption charges.
    public function consumptionCharges()
    {
        return $this->hasMany(ConsumptionCharge::class);
    }

    // the serviceRequests() function defines a hasMany relationship with the ServiceRequest model.
    // It allows you to access all the service requests associated with the meter.
    // the relationship is one-to-many, where one meter can have many service requests.
    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    protected static function booted()
{
    static::observe(\App\Observers\MeterObserver::class);
}
}
