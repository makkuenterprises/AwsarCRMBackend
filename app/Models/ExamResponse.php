<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamResponse extends Model
{
    use HasFactory;
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
     public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function questionResponses()
    {
        return $this->hasMany(ExamQuestionResponse::class);
    }
        
}
