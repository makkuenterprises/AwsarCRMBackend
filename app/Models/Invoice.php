<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{ 
    use HasFactory;
    protected $fillable = [
        'enrollment_id',
        'student_id',
        'course_id',
        'invoice_no',
        'student_name',
        'course_name',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'invoice_date',
    ];
}
