<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $guarded = [];
    protected $casts = ['expires_at' => 'datetime', 'paid_at' => 'datetime'];
}
