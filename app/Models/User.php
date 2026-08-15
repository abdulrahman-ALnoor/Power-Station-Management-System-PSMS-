<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

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

    /**
     * The createdCustomers() function defines a one-to-many
     * relationship between the User model and the Customer model.
     */
    public function createdCustomers()
    {
        return $this->hasMany(Customer::class, 'created_by');
    }

    /**
     * The installedMeters() function defines a one-to-many
     * relationship between the User model and the Meter model.
     */
    public function installedMeters()
    {
        return $this->hasMany(Meter::class, 'installed_by');
    }

    /**
     * The createdMeters() function defines a one-to-many
     * relationship between the User model and the Meter model.
     */
    public function createdMeters()
    {
        return $this->hasMany(Meter::class, 'created_by');
    }

    /**
     * The createdMeterReadings() function defines a one-to-many
     * relationship between the User model and the MeterReading model.
     */
    public function createdMeterReadings()
    {
        return $this->hasMany(MeterReading::class, 'created_by');
    }

    /**
     * The accountedInvoices() function defines a one-to-many
     * relationship between the User model and the Invoice model.
     */
    public function accountedInvoices()
    {
        return $this->hasMany(Invoice::class, 'accountant_id');
    }

    /**
     * The createdServiceRequests() function defines a one-to-many
     * relationship between the User model and the ServiceRequest model.
     */
    public function createdServiceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'created_by');
    }

    /**
     * The equipment() function defines a one-to-many
     * relationship between the User model and the Equipment model.
     */
    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }

    /**
     * The createdEquipment() function defines a one-to-many
     * relationship between the User model and the Equipment model.
     */
    public function createdEquipment()
    {
        return $this->hasMany(Equipment::class, 'created_by');
    }
}
