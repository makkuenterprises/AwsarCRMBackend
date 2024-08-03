<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoomMeeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'meeting_id',
        'host_id',
        'host_email',
        'topic',
        'type',
        'status',
        'start_time',
        'duration',
        'timezone',
        'agenda',
        'start_url',
        'join_url',
        'password',
        'batch_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
    ];
}
