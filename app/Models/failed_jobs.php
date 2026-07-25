<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class failed_jobs extends Model
{
     use HasFactory;

    protected $fillable = ['id', 'uuid', 'connection', 'queue', 'payload', 'exception', 'failed_at'];
}
