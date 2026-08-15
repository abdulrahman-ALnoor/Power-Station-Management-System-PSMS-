<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRequest extends Model
{
    //

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'meter_id',
        'customer_id',
        'created_by',
        'assigned_engineer_id',
        'request_type',
        'priority',
        'status',
        'description',
        'completed_at',
        'equipment_id',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }


    // the meter() function defines a belongsTo relationship with the Meter model.
    // It allows you to access the meter associated with the service request.
    // the relationship is one-to-many, where one meter can have many service requests.
    public function meter()
    {
        return $this->belongsTo(Meter::class);
    }

    // the customer() function defines a belongsTo relationship with the Customer model.
    // It allows you to access the customer associated with the service request.
    // the relationship is one-to-many, where one customer can have many service requests.
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // the creator() function defines a belongsTo relationship with the User model.
    // It allows you to access the user who created the service request.
    // the relationship is one-to-many, where one user can create many service requests.
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // the assignedEngineer() function defines a belongsTo relationship with the User model.
    // It allows you to access the user who is assigned as the engineer for the service request
    // the relationship is one-to-many, where one user can be assigned as the engineer for many service requests.
    public function assignedEngineer()
    {
        return $this->belongsTo(User::class, 'assigned_engineer_id');
    }
}
