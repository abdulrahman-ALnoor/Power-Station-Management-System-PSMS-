<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class equipment extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'user_id', 'equipment_name', 'serial_number', 'status', 'notes', 'created_by','created_at','updated_at','deleted_at'];
}
