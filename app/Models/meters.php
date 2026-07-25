<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class meters extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'customer_id', 'meter_number', 'qr_code', 'installation_date', 'installation_location', 'status', 'installed_by', 'created_by', 'created_at','updated_at','deleted_at'];
}
