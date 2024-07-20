<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamResponse;
use App\Models\ExamQuestionResponse;

class ExamResponseController extends Controller
{

  public function storeExamResponse(Request $request)
{
    try {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'student_id' => 'required|exists:students,id',
            'responses' => 'required|array',
            'responses.*.question_id' => 'required|exists:questions,id',
            'responses.*.response' => 'nullable|string',
            'responses.*.marks' => 'nullable|numeric',
            'responses.*.negative_marks' => 'nullable|numeric'
        ]);

        // Initialize counters
        $totalMarks = 0;
        $gainedMarks = 0;
        $totalCorrectAnswers = 0;
        $totalWrongAnswers = 0;
        $totalQuestions = 0;

        // Fetch the correct answers and question types for the exam
        $examQuestions = ExamQuestion::where('exam_id', $validated['exam_id'])
            ->with('question')
            ->get();

        // Create a map of correct answers and negative marks for quick lookup
        $correctAnswersMap = $examQuestions->mapWithKeys(function ($examQuestion) {
            return [$examQuestion->question_id => [
                'correct_answers' => $examQuestion->question->correct_answers,
                'negative_marks' => $examQuestion->question->negative_marks ?? 0 // default to 0 if not set
            ]];
        });

        foreach ($validated['responses'] as $response) {
            $totalQuestions++;
            $marks = $response['marks'] ?? 0;

            // Determine the negative marks based on the question type
            $negativeMarks = isset($correctAnswersMap[$response['question_id']]['negative_marks'])
                ? $correctAnswersMap[$response['question_id']]['negative_marks']
                : 0;

            $totalMarks += $marks;

            // Check if the response is correct
            if (isset($correctAnswersMap[$response['question_id']]['correct_answers']) &&
                $response['response'] == $correctAnswersMap[$response['question_id']]['correct_answers']) {
                $gainedMarks += $marks;
                $totalCorrectAnswers++;
            } else {
                $gainedMarks -= $negativeMarks;
                $totalWrongAnswers++;
            }
        }

        // Create or update exam response record
        $examResponse = ExamResponse::updateOrCreate(
            ['exam_id' => $validated['exam_id'], 'student_id' => $validated['student_id']],
            [
                'total_marks' => $totalMarks,
                'gained_marks' => $gainedMarks,
                'passing_marks' => $request->input('passing_marks', 0),
                'negative_marks' => $request->input('negative_marks', 0),
                'total_correct_answers' => $totalCorrectAnswers,
                'total_wrong_answers' => $totalWrongAnswers,
                'total_questions' => $totalQuestions,
            ]
        );

        // Store individual question responses
        foreach ($validated['responses'] as $response) {
            ExamQuestionResponse::updateOrCreate(
                [
                    'exam_response_id' => $examResponse->id,
                    'question_id' => $response['question_id']
                ],
                [
                    'response' => $response['response'] ?? null,
                    'marks' => $response['marks'] ?? null,
                    'negative_marks' => $response['negative_marks'] ?? null,
                ]
            );
        }

        return response()->json(['status' => true, 'message' => 'Response stored successfully'], 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'An error occurred',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function calculateMarks($examId, $studentId)
{
    $examResponse = ExamResponse::where('exam_id', $examId)
        ->where('student_id', $studentId) 
        ->first();

    if (!$examResponse) { 
        return response()->json(['status' => false, 'message' => 'No exam response found'], 404);
    }

    $questions = ExamQuestionResponse::where('exam_response_id', $examResponse->id)->get();

    $totalMarks = $questions->sum('marks');
    $gainedMarks = $questions->sum('marks') - $questions->sum('negative_marks');

    $examResponse->update([
        'total_marks' => $totalMarks,
        'gained_marks' => $gainedMarks,
        'passing_marks' => $examResponse->passing_marks,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Marks calculated successfully',
        'data' => [
            'total_marks' => $totalMarks,
            'gained_marks' => $gainedMarks,
            'passing_marks' => $examResponse->passing_marks,
        ]
    ], 200);
}

}
