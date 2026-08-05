<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    // the roles() function defines a many-to-many 
    //  relationship between the User model and the Role model.
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }


    // the createdCustomers() function defines a one-to-many 
    //relationship between the User model and the Customer model.

    public function createdCustomers()
    {
        return $this->hasMany(Customer::class, 'created_by');
    }

    // the installedMeters() function defines a one-to-many 
    //relationship between the User model and the Meter model.
    public function installedMeters()
    {
        return $this->hasMany(Meter::class, 'installed_by');
    }

    // the createdMeters() function defines a one-to-many 
    //relationship between the User model and the Meter model.
    public function createdMeters()
    {
        return $this->hasMany(Meter::class, 'created_by');
    }

    // the createdMeterReadings() function defines a one-to-many
    //  relationship between the User model and the MeterReading model.
    public function createdMeterReadings()
    {
        return $this->hasMany(MeterReading::class, 'created_by');
    }

    // the createdInvoices() function defines a one-to-many
    //  relationship between the User model and the Invoice model.
    public function accountedInvoices()
    {
        return $this->hasMany(Invoice::class, 'accountant_id');
    }

    // the createdServiceRequests() function defines a one-to-many
    //  relationship between the User model and the ServiceRequest model.
    public function createdServiceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'created_by');
    }

    // the assignedServiceRequests() function defines a one-to-many
    //  relationship between the User model and the ServiceRequest model.
    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }

    // the assignedServiceRequests() function defines a one-to-many
    //  relationship between the User model and the ServiceRequest model.
    public function createdEquipment()
    {
        return $this->hasMany(Equipment::class, 'created_by');
    }
}
