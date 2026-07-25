<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class users extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name', 'email', 'email_verified_at', 'password', 'phone','status','remember_token','created_at','updated_at'];
}
