<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class meter_readings extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'meter_id', 'meter_reader_id', 'previous_reading', 'current_reading', 'consumption', 'price_per_kwh', 'reading_cost', 'reading_date', 'reading_method','status','notes	created_by','created_at','updated_at','deleted_at'];
}
