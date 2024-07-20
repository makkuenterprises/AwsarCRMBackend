<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    protected $table = 'exam_questions';
    protected $fillable = ['exam_id', 'question_id', 'section_id', 'marks', 'negative_marks'];
      public function question()
    {
        return $this->belongsTo(Questions::class);
    }
}
