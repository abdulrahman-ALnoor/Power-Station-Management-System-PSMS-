<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class failed_jobs extends Model
{
     use HasFactory;

    protected $fillable = ['id', 'uuid', 'connection', 'queue', 'payload', 'exception', 'failed_at'];
}
