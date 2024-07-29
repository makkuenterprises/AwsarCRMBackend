<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoomToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'access_token',
        'expires_at',
    ];
}
