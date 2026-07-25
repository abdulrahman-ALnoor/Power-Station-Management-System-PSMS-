<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class customers extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'customer_number', 'full_name', 'customer_type', 'phone', 'alternative_phone', 'address_description', 'notes', 'created_by', 'created_at', 'updated_at','deleted_at'];
}
