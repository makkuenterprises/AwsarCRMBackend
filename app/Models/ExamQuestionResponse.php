<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestionResponse extends Model
{
    use HasFactory;
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
       public function examResponse()
    {
        return $this->belongsTo(ExamResponse::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
