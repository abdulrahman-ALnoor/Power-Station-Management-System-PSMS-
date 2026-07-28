<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyProfile extends Model
{
    //

    use HasFactory, SoftDeletes;


    protected $fillable = [
        'company_name',
        'logo',
        'address',
        'whatsapp_number',
        'support_number',
        'currency',
        'price_per_kwh',
        'reading_cycle_days',
    ];

    protected function casts(): array
    {
        return [
            'price_per_kwh' => 'decimal:2',
            'reading_cycle_days' => 'integer',
        ];
    }
}
