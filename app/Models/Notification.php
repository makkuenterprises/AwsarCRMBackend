<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $table='notifications';
    protected $primarykey='id';
    protected $fillable = [
        'title', 'description', 'sendTo'
    ];

   public function batches()
    {
        return $this->belongsToMany(Batch::class, 'notification_batch');
    }
}
