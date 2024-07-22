<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Section;
use App\Models\Question;
use App\Models\ExamQuestion;
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
            'responses.*.response' => 'nullable',
            'responses.*.marks' => 'nullable|numeric',
            'responses.*.negative_marks' => 'nullable|numeric'
        ]);

        // Initialize counters
        $totalMarks = 0;
        $gainedMarks = 0;
        $totalCorrectAnswers = 0;
        $totalWrongAnswers = 0;
        $totalQuestions = 0;

        // Fetch the questions and correct answers for the exam
        $examQuestions = ExamQuestion::where('exam_id', $validated['exam_id'])
            ->with('question')
            ->get();

        // Create a map of correct answers for quick lookup
        $correctAnswersMap = $examQuestions->mapWithKeys(function ($examQuestion) {
            return [$examQuestion->question_id => $examQuestion->question->correct_answers];
        });

        foreach ($validated['responses'] as $response) {
            $totalQuestions++;
            $marks = $response['marks'] ?? 0;
            $negativeMarks = $response['negative_marks'] ?? 0;

            $totalMarks += $marks;
            
            $question = $examQuestions->firstWhere('question_id', $response['question_id']);
            $correctAnswers = $correctAnswersMap[$response['question_id']] ?? null;

            // Determine if the response is correct based on question type
            if ($question) {
                switch ($question->question->question_type) {
                    case 'MCQ':
                        // For MCQ, compare if the selected options match correct answers
                        if (is_array($correctAnswers) && is_array($response['response'])) {
                            $isCorrect = !array_diff($correctAnswers, $response['response']) && !array_diff($response['response'], $correctAnswers);
                        } else {
                            $isCorrect = $response['response'] == $correctAnswers;
                        }
                        break;
                    
                    case 'Short Answer':
                    case 'Fill in the Blanks':
                        // For Short Answer and Fill in the Blanks, compare exact match
                        $isCorrect = $response['response'] == $correctAnswers;
                        break;

                    default:
                        $isCorrect = false;
                }

                if ($isCorrect) {
                    $gainedMarks += $marks;
                    $totalCorrectAnswers++;
                } else {
                    $gainedMarks -= $negativeMarks;
                    $totalWrongAnswers++;
                }
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
                'total_question' => $totalQuestions,
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
                    'response' => $response['response'] ?? '', // Ensure response is stored as a string
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



    public function getResponsesByBatchAndStudent(Request $request)
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'batch_id' => 'required|exists:exams,batch_id',
            'student_id' => 'required|exists:students,id'
        ]);

        try {
            // Retrieve all exams associated with the batch
            $exams = Exam::where('batch_id', $validated['batch_id'])->get();

            // Initialize an array to hold exam responses
            $responses = [];

            foreach ($exams as $exam) {
                // Fetch the response for the specific student and exam
                $examResponse = ExamResponse::where('exam_id', $exam->id)
                    ->where('student_id', $validated['student_id'])
                    ->first();

                if ($examResponse) {
                    // Retrieve detailed responses for the exam
                    $questionResponses = ExamQuestionResponse::where('exam_response_id', $examResponse->id)
                        ->get();

                    // Append exam response and question responses to the array
                    $responses[] = [
                        'exam' => $exam,
                        'exam_response' => $examResponse,
                        'question_responses' => $questionResponses
                    ];
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Responses retrieved successfully',
                'data' => $responses
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving responses',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
