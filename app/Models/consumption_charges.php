<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class consumption_charges extends Model
{
     use HasFactory;

    protected $fillable = ['id', 'customer_id', 'meter_id', 'meter_reading_id', 'total_amount', 'paid_amount', 'remaining_amount', 'status', 'created_at', 'updated_at', 'deleted_at'];
}
