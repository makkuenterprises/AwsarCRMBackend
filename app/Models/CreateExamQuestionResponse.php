<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreateExamQuestionResponse extends Model
{
    use HasFactory;

    protected $table = 'exam_question_responses';
    protected $primaryKey = 'id';
    protected $fillable = [
        'exam_response_id', 
        'question_id', 
        'response', 
        'marks', 
        'negative_marks'
    ];

    // Cast the response attribute to an array when accessing
    protected $casts = [
        'response' => 'array',
    ];
}
