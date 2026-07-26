<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserRole extends Model
{
    //
   use HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'role_id',
    ];

    // the functions below are used to define the relationship between the UserRole model and the User and Role models.
    // The user() function defines a belongsTo relationship with the User model, while the role() function defines a belongsTo relationship with the Role model.
   // the relationship is one-to-many, where one user can have many roles and one role can be assigned to many users.
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
