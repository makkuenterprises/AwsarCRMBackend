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
        // Validate the request data
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'student_id' => 'required|exists:students,id',
            'responses' => 'required|array',
            'responses.*.question_id' => 'required|exists:questions,id',
            'responses.*.response' => 'nullable',
            'responses.*.marks' => 'nullable|numeric',
            'responses.*.negative_marks' => 'nullable|numeric',
            'passing_marks' => 'nullable|numeric'
        ]);

        // Initialize counters
        $totalMarks = 0;
        $gainedMarks = 0;
        $totalCorrectAnswers = 0;
        $totalWrongAnswers = 0;
        $totalQuestions = 0;

        // Fetch the questions for the exam
        $examQuestions = ExamQuestion::where('exam_id', $validated['exam_id'])
            ->with('question')
            ->get();

        // Calculate the total number of questions in the exam
        $totalQuestions = $examQuestions->count();

        // Create a map of correct answers for quick lookup
        $correctAnswersMap = $examQuestions->mapWithKeys(function ($examQuestion) {
            return [$examQuestion->question_id => $examQuestion->question->correct_answers];
        });

        // Initialize an array to keep track of question responses and marks
        $questionMarksMap = [];

        // Initialize a set to track unique questions answered
        $answeredQuestionIds = [];

        foreach ($validated['responses'] as $response) {
            $marks = $response['marks'] ?? 0;
            $negativeMarks = $response['negative_marks'] ?? 0;
            $questionId = $response['question_id'];
            $responseText = $response['response'] ?? '';

            // Aggregate marks for each question
            if (!isset($questionMarksMap[$questionId])) {
                $questionMarksMap[$questionId] = [
                    'marks' => 0,
                    'negative_marks' => 0,
                    'response' => $responseText,
                    'your_marks' => 0 // Initialize your_marks
                ];
            }
            $questionMarksMap[$questionId]['marks'] += $marks;
            $questionMarksMap[$questionId]['negative_marks'] += $negativeMarks;

            // Track answered question IDs
            $answeredQuestionIds[$questionId] = true;

            // Determine if the response is correct based on question type
            $question = $examQuestions->firstWhere('question_id', $questionId);
            $correctAnswers = $correctAnswersMap[$questionId] ?? [];

            if ($question) {
                switch ($question->question->question_type) {
                    case 'MCQ':
                        // For MCQ, compare if the selected options match correct answers
                        if (is_array($correctAnswers) && is_array($responseText)) {
                            $isCorrect = !array_diff($correctAnswers, $responseText) && !array_diff($responseText, $correctAnswers);
                        } else {
                            $isCorrect = $responseText == $correctAnswers;
                        }

                        if ($isCorrect) {
                            $gainedMarks += $marks;
                            $totalCorrectAnswers++;
                            $questionMarksMap[$questionId]['your_marks'] = $marks; // Add marks if correct
                        } else {
                            $gainedMarks -= $negativeMarks;
                            $totalWrongAnswers++;
                            $questionMarksMap[$questionId]['your_marks'] = -$negativeMarks; // Deduct marks if incorrect and negative marks are applicable
                        }
                        break;

                    case 'Short Answer':
                    case 'Fill in the Blanks':
                        // For Short Answer and Fill in the Blanks, keep the response for manual grading
                        $isCorrect = false; // We'll handle grading manually later
                        break;

                    default:
                        $isCorrect = false;
                }
            }
        }

        // Create or update exam response record
        $examResponse = ExamResponse::updateOrCreate(
            ['exam_id' => $validated['exam_id'], 'student_id' => $validated['student_id']],
            [
                'total_marks' => $totalMarks,
                'gained_marks' => $gainedMarks,
                'passing_marks' => $validated['passing_marks'] ?? 0,
                'negative_marks' => $request->input('negative_marks', 0),
                'total_correct_answers' => $totalCorrectAnswers,
                'total_wrong_answers' => $totalWrongAnswers,
                'total_question' => $totalQuestions,
            ]
        );
        // dd($totalQuestions);


        // Update the got_marks for the exam
        $exam = Exam::find($validated['exam_id']);
        $exam->got_marks = $gainedMarks;
        $exam->save();

        // Store individual question responses
        foreach ($questionMarksMap as $questionId => $marksData) {
            ExamQuestionResponse::updateOrCreate(
                [
                    'exam_response_id' => $examResponse->id,
                    'question_id' => $questionId
                ],
                [
                    'response' => json_encode($marksData['response']), // Ensure response is stored as JSON
                    'marks' => $marksData['marks'],
                    'negative_marks' => $marksData['negative_marks'],
                    'your_marks' => $marksData['your_marks'], // Store your_marks
                    'status' => in_array($examQuestions->firstWhere('question_id', $questionId)->question->question_type, ['Short Answer', 'Fill in the Blanks']) ? 'pending' : 'graded' // Set status
                ]
            );
        }

        // Return the stored exam response data
        return response()->json([
            'status' => true,
            'message' => 'Response stored successfully',
            'data' => [
                'exam_response' => $examResponse,
                'question_marks' => $questionMarksMap
            ]
        ], 201);
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


public function gradeShortAnswerResponses(Request $request)
{
    try {
        // Validate the request data
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'student_id' => 'required|exists:students,id',
            'manual_grades' => 'required|array',
            'manual_grades.*.question_id' => 'required|exists:questions,id',
            'manual_grades.*.marks' => 'required|numeric',
        ]);

        // Initialize counters
        $totalMarks = 0;
        $totalCorrectAnswers = 0;
        $totalWrongAnswers = 0;

        // Fetch the existing exam responses
        $examResponse = ExamResponse::where('exam_id', $validated['exam_id'])
            ->where('student_id', $validated['student_id'])
            ->firstOrFail();

        // Fetch the exam questions
        $examQuestions = ExamQuestion::where('exam_id', $validated['exam_id'])
            ->with('question')
            ->get()
            ->keyBy('question_id');

        // Update question responses based on manual grades
        foreach ($validated['manual_grades'] as $grade) {
            $questionId = $grade['question_id'];
            $manualMarks = $grade['marks'];

            // Fetch the question and its current response
            $question = $examQuestions->get($questionId);
            $response = ExamQuestionResponse::where('exam_response_id', $examResponse->id)
                ->where('question_id', $questionId)
                ->first();

            if ($response && in_array($question->question->question_type, ['Short Answer', 'Fill in the Blanks'])) {
                // Update the response with the manual marks
                $response->your_marks = $manualMarks;
                // $response->status = 'graded'; // Mark as graded
                $response->save();

                // Update total marks and counters
                $totalMarks += $manualMarks;
                $totalCorrectAnswers += $manualMarks > 0 ? 1 : 0;
                $totalWrongAnswers += $manualMarks <= 0 ? 1 : 0;
            }
        }

        // Update the total marks and correct/wrong answers in the exam response
        $examResponse->gained_marks += $totalMarks;
        $examResponse->total_correct_answers += $totalCorrectAnswers;
        $examResponse->total_wrong_answers += $totalWrongAnswers;
        $examResponse->save();

        // Return the updated exam response data
        return response()->json([
            'status' => true,
            'message' => 'Manual grading updated successfully',
            'data' => $examResponse
        ], 200);
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
        'student_id' => 'required|exists:students,id',
        'exam_id' => 'required|exists:exams,id' // Optional filter for a specific exam
    ]);

    try {
        // Retrieve all exams associated with the batch
        $query = Exam::where('batch_id', $validated['batch_id']);

        // If an exam_id is provided, filter by it
        if (isset($validated['exam_id'])) {
            $query->where('id', $validated['exam_id']);
        }

        $exams = $query->get();

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
                    'exam_id' => $exam->id,
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
