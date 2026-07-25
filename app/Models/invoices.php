<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class invoices extends Model
{
     use HasFactory;

    protected $fillable = ['id', 'invoice_number', 'customer_id', 'accountant_id', 'outstanding_before_payment', 'paid_amount', 'remaining_balance','status','payment_notes','created_by','	created_at','updated_at','	deleted_at'];
}
