<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class roles extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name', 'description', 'created_at', 'updated_at', 'deleted_at'];
}
