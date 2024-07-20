<?php
namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Section;
use App\Models\Question;
use App\Models\ExamQuestion;
use Illuminate\Http\Request;
class ExamController extends Controller
{ 
    public function createExam(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s',
            'batch_id' => 'required|exists:courses,id',
            'passing_marks' => 'required|numeric',
            'sections' => 'required|array',
            'sections.*.name' => 'required|string',
            'sections.*.questions' => 'required|array',
            'sections.*.questions.*.id' => 'required|exists:questions,id',
            'sections.*.questions.*.marks' => 'required|numeric',
            'sections.*.questions.*.negative_marks' => 'nullable|numeric', // Make negative_marks optional
        ]);

        $exam = Exam::create($request->only('name', 'start_time', 'end_time', 'batch_id', 'passing_marks'));

        foreach ($request->sections as $sectionData) {
            $section = Section::create([
                'exam_id' => $exam->id,
                'name' => $sectionData['name'],
            ]);

            foreach ($sectionData['questions'] as $questionData) {
                ExamQuestion::create([
                    'exam_id' => $exam->id,
                    'question_id' => $questionData['id'],
                    'section_id' => $section->id,
                    'marks' => $questionData['marks'],
                    'negative_marks' => $questionData['negative_marks'] ?? null, // Handle optional negative_marks
                ]);
            }
        }

        return response()->json(['message' => 'Exam created successfully'], 201);
    }
}

