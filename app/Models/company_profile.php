<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class company_profile extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'company_name', 'logo', 'address', 'whatsapp_number', 'support_number', 'currency', 'price_per_kwh', 'reading_cycle_days', 'created_at', 'updated_at', 'deleted_at'];
}
