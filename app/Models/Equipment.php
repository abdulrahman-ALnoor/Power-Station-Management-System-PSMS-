<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    //

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'equipment_name',
        'serial_number',
        'status',
        'notes',
        'created_by',
    ];


    // the casts() function defines the data types for specific attributes of the Equipment model.
    // It ensures that the attributes are automatically cast to the specified data types when accessed or set
    // the realationship is one-to-many, where one equipment can have many attributes.
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // the creator() function defines a belongsTo relationship with the User model.
    // It allows you to access the user who created the equipment record.
    // the relationship is one-to-many, where one user can create many equipment records.
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
