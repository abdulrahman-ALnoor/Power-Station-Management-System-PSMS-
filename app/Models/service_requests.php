<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class service_requests extends Model
{
    use HasFactory;
    
    protected $fillable = ['id', 'meter_id', 'customer_id', 'created_by', 'assigned_engineer_id', 'request_type', 'priority', 'status', 'description', 'completed_at','created_at','updated_at','deleted_at'];
    
}
