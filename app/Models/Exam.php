<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = ['name', 'start_time', 'end_time', 'batch_id', 'passing_marks'];

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Questions::class, 'exam_questions')->withPivot('marks', 'negative_marks');
    }
     public function responses()
    {
        return $this->hasMany(ExamResponse::class);
    }
}

