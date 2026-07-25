<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class cache_locks extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'owner', 'expiration'];
    // public function ()
    // {
    //     return $this->hasMany(cache_locks::class);
    // }
}
