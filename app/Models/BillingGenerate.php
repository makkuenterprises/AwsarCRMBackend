<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingGenerate extends Model
{
    use HasFactory;
    protected $table='billing_generates';
    protected $primarykey='id';
}
