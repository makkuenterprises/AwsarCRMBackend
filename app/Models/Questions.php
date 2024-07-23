<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Questions extends Model
{
    use HasFactory;
    protected $fillable = [
        'question_text',
        'question_type',
        'options',
        'correct_answers',
        'image',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answers' => 'array',
    ];

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_questions')->withPivot('marks', 'negative_marks');
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'exam_questions')->withPivot('marks', 'negative_marks');
    }
    public function responses()
    {
        return $this->hasMany(ExamQuestionResponse::class);
    }


}
