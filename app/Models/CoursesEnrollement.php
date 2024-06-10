<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursesEnrollement extends Model
{
    use HasFactory;
     protected $table='courses_enrollements';
    
    protected $primarykey='id';
}
