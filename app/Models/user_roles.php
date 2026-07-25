<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class user_roles extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'role_id'];
}
