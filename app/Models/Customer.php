<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    //

    use HasFactory, SoftDeletes;
    protected $fillable = [
        'customer_number',
        'full_name',
        'customer_type',
        'phone',
        'alternative_phone',
        'address_description',
        'notes',
        'created_by',
    ];

    // the creator() function defines a belongsTo relationship with the User model.
    // It allows you to access the user who created the customer record.
    // the relationship is one-to-many, where one user can create many customers.
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // the meters() function defines a hasMany relationship with the Meter model.
    // It allows you to access all the meters associated with the customer.
    // the relationship is one-to-many, where one customer can have many meters.
    public function meters()
    {
        return $this->hasMany(Meter::class);
    }

    // the consumptionCharges() function defines a hasMany relationship with the ConsumptionCharge model.
    // It allows you to access all the consumption charges associated with the customer.
    // the relationship is one-to-many, where one customer can have many consumption charges.
    public function consumptionCharges()
    {
        return $this->hasMany(ConsumptionCharge::class);
    }

    // the invoices() function defines a hasMany relationship with the Invoice model.
    // It allows you to access all the invoices associated with the customer.
    // the relationship is one-to-many, where one customer can have many invoices.
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // the serviceRequests() function defines a hasMany relationship with the ServiceRequest model.
    // It allows you to access all the service requests associated with the customer.
    // the relationship is one-to-many, where one customer can have many service requests.
    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    // the notifications() function defines a hasMany relationship with the Notification model.
    // It allows you to access all the notifications associated with the customer.
    // the relationship is one-to-many, where one customer can have many notifications.
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
