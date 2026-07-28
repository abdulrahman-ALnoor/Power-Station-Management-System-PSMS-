<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    //

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
    ];


    // the users() function defines a many-to-many
    //  relationship between the Role model and the User model.
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

}
