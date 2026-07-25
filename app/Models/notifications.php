<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class notifications extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'customer_id', 'meter_reading_id', 'invoice_id', 'notification_type', 'message', 'status', 'whatsapp_message_id', 'sent_at', 'read_at','created_at','updated_at','deleted_at'];
}
