<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreateExamResponse extends Model
{
    use HasFactory;
    // Define the table name
    protected $table = 'exam_responses';

    // Define the primary key
    protected $primaryKey = 'id';

    // If your primary key is not an auto-incrementing integer, set the following properties
    // public $incrementing = false;
    // protected $keyType = 'string';

    // Define the fillable properties for mass assignment
    protected $fillable = [
        'exam_id', 
        'student_id', 
        'total_marks', 
        'gained_marks', 
        'passing_marks', 
        'negative_marks', 
        'total_correct_answers', 
        'total_wrong_answers',
        'total_questions'
    ];
}
