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
}
