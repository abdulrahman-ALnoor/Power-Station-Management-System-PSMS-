<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class jobs extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'queue', 'payload', 'attempts', 'reserved_at', 'available_at', 'created_at'];
}
