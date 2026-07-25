<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class password_reset_tokens extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'token', 'created_at'];
}
