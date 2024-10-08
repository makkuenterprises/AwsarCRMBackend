<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Section;
use App\Models\Question;
use App\Models\ExamQuestion; 
use Illuminate\Http\Request;
use App\Models\ExamResponse; 
use App\Models\Student; 

use App\Models\ExamQuestionResponse; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


use DB;
class ExamResponseController extends Controller
{
 

// public function storeExamResponse(Request $request)
// {

//     try {
//         // Validate the request data
//         $validated = $request->validate([
//             'exam_id' => 'required|exists:exams,id',
//             'student_id' => 'required|exists:students,id',
//             'responses' => 'required|array',
//             'responses.*.question_id' => 'required|exists:questions,id',
//             'responses.*.response' => 'nullable',
//             'responses.*.marks' => 'nullable|numeric',
//             'responses.*.negative_marks' => 'nullable|numeric',
//             'passing_marks' => 'nullable|numeric'
//         ]);

      
//        $timezone = 'Asia/Kolkata';

//         // Find the exam
//         $exam = Exam::find($validated['exam_id']);

//         // Parse the start and end time into Carbon instances with the specified timezone
//         $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $exam->start_time, $timezone);
//         $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $exam->end_time, $timezone);

//         // Get the current time and date in the specified timezone
//         $currentTime = Carbon::now($timezone);
//         $currentDate = $currentTime->toDateString(); // Get date in "Y-m-d" format
//         $examDate = $startTime->toDateString();      // Get exam date in "Y-m-d" format

//         // Check if the current date matches the exam date
//         if ($currentDate !== $examDate) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'Exam submission is only allowed on the exam date',
//             ], 403);
//         }

//         // Check if the current time is within the allowed time frame
//         if ($currentTime->lt($startTime) || $currentTime->gt($endTime)) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'Exam submission is not allowed outside the designated time period',
//             ], 403);
//         }

//         // Initialize counters
//         $totalMarks = 0;
//         $gainedMarks = 0;
//         $totalCorrectAnswers = 0;
//         $totalWrongAnswers = 0;
//         $totalQuestions = 0;

//         // Fetch the questions for the exam
//         $examQuestions = ExamQuestion::where('exam_id', $validated['exam_id'])
//             ->with('question')
//             ->get();

//         // Create a map of correct answers for quick lookup
//         $correctAnswersMap = $examQuestions->mapWithKeys(function ($examQuestion) {
//             return [$examQuestion->question_id => $examQuestion->question->correct_answers];
//         });

//         // Initialize an array to keep track of question responses and marks
//         $questionMarksMap = [];

//         // Track unique question IDs
//         $answeredQuestionIds = [];

//         foreach ($validated['responses'] as $response) {
//             $marks = $response['marks'] ?? 0;
//             $negativeMarks = $response['negative_marks'] ?? 0;
//             $questionId = $response['question_id'];
//             $responseText = $response['response'] ?? '';

//             // Aggregate marks for each question
//             if (!isset($questionMarksMap[$questionId])) {
//                 $questionMarksMap[$questionId] = [
//                     'marks' => 0,
//                     'negative_marks' => 0,
//                     'response' => $responseText,
//                     'your_marks' => 0
//                 ];
//             }
//             $questionMarksMap[$questionId]['marks'] += $marks;
//             $questionMarksMap[$questionId]['negative_marks'] += $negativeMarks;

//             // Track answered question IDs
//             $answeredQuestionIds[$questionId] = true;

//             // Determine if the response is correct based on question type
//             $question = $examQuestions->firstWhere('question_id', $questionId);
//             $correctAnswers = $correctAnswersMap[$questionId] ?? []; 

//             if ($question) { 
//                 switch ($question->question->question_type) {
//                     case 'MCQ':
//                         // For MCQ, compare if the selected options match correct answers
//                         if (is_array($correctAnswers) && is_array($responseText)) {
//                             $isCorrect = !array_diff($correctAnswers, $responseText) && !array_diff($responseText, $correctAnswers);
//                         } else {
//                             $isCorrect = $responseText == $correctAnswers;
//                         }

//                         if ($isCorrect) {
//                             $gainedMarks += $marks;
//                             $totalCorrectAnswers++;
//                             $questionMarksMap[$questionId]['your_marks'] = $marks;
//                         } else {
//                             $gainedMarks -= $negativeMarks;
//                             $totalWrongAnswers++;
//                             $questionMarksMap[$questionId]['your_marks'] = -$negativeMarks;
//                         }
//                         break;

//                     case 'Short Answer':
//                     case 'Fill in the Blanks':
//                         // For Short Answer and Fill in the Blanks, keep the response for manual grading
//                         $isCorrect = false;
//                         break;

//                     default:
//                         $isCorrect = false;
//                 }
//             }
//         }

//         // Compute total marks by summing the marks of all questions for the exam
//         $totalMarks = $examQuestions->sum('marks');

//         // Count the total number of unique questions answered
//         $totalQuestions = count($answeredQuestionIds);

//         // Check if an exam response already exists
//         $examResponse = ExamResponse::where('exam_id', $validated['exam_id'])
//             ->where('student_id', $validated['student_id'])
//             ->first();

//         if ($examResponse) {
          
//             return response()->json([
//             'status' => true,
//             'message' => 'You have already completed this exam..',
            
//         ], 422); 
//         } else {
//             // Create a new record
//             $examResponse = new ExamResponse();
//             $examResponse->exam_id = $validated['exam_id'];
//             $examResponse->student_id = $validated['student_id'];
//             $examResponse->total_marks = $totalMarks;
//             $examResponse->gained_marks = $gainedMarks;
//             $examResponse->passing_marks = $validated['passing_marks'] ?? 0;
//             $examResponse->negative_marks = $request->input('negative_marks', 0);
//             $examResponse->total_correct_answers = $totalCorrectAnswers;
//             $examResponse->total_wrong_answers = $totalWrongAnswers;
//             $examResponse->save();
//         }

//         // Debugging to confirm what was saved
//         \Log::info('ExamResponse after create or update:', $examResponse->toArray());

//         // Update the got_marks for the exam
//         $exam = Exam::find($validated['exam_id']); 
//         $exam->got_marks = $gainedMarks;
//         $exam->save();

//         // Store individual question responses
//         foreach ($questionMarksMap as $questionId => $marksData) {
//             $existingResponse = ExamQuestionResponse::where([
//                 'exam_response_id' => $examResponse->id,
//                 'question_id' => $questionId
//             ])->first();

//             if ($existingResponse) {
//                 // Update the existing record
//                 $existingResponse->response = json_encode($marksData['response']);
//                 $existingResponse->marks = $marksData['marks'];
//                 $existingResponse->negative_marks = $marksData['negative_marks'];
//                 $existingResponse->your_marks = $marksData['your_marks']; 
//                 $existingResponse->status = in_array(
//                 $examQuestions->firstWhere('question_id', $questionId)->question->question_type,
//                     ['Short Answer', 'Fill in the Blanks']
//                 ) ? 'pending' : 'correct';   
//                 $existingResponse->save(); 
//             } else {
//                 // Create a new record
//                 $newResponse = new ExamQuestionResponse();
//                 $newResponse->exam_response_id = $examResponse->id;
//                 $newResponse->question_id = $questionId;
//                 $newResponse->response = json_encode($marksData['response']);
//                 $newResponse->marks = $marksData['marks'];
//                 $newResponse->negative_marks = $marksData['negative_marks'];
//                 $newResponse->your_marks = $marksData['your_marks'];
//                 $newResponse->status = in_array(
//                 $examQuestions->firstWhere('question_id', $questionId)->question->question_type,
//                     ['Short Answer', 'Fill in the Blanks']
//                 ) ? 'pending' : 'correct';
//                 $newResponse->save();
//             }
//         }

//         // Return the stored exam response data
//         return response()->json([
//             'status' => true,
//             'message' => 'Response stored successfully',
//             'data' => [
//                 'exam_response' => $examResponse,
//                 'question_marks' => $questionMarksMap,
//                 'total_question' => $totalQuestions
//             ]

//         ], 201);
//     } catch (\Illuminate\Validation\ValidationException $e) {
//         return response()->json([
//             'status' => false,
//             'message' => 'Validation failed',
//             'errors' => $e->errors()
//         ], 422);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => false,
//             'message' => 'An error occurred',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }

// public function storeExamResponse(Request $request)
// {
//     try {
//         // Validate the request data
//         $validated = $request->validate([
//             'exam_id' => 'required|exists:exams,id',
//             'student_id' => 'required|exists:students,id',
//             'responses' => 'required|array',
//             'responses.*.question_id' => 'required|exists:questions,id',
//             'responses.*.response' => 'nullable',
//             'responses.*.marks' => 'nullable|numeric',
//             'responses.*.negative_marks' => 'nullable|numeric',
//             'passing_marks' => 'nullable|numeric'
//         ]);

//         $timezone = 'Asia/Kolkata';

//         // Find the exam
//         $exam = Exam::find($validated['exam_id']);

//         // Parse the start and end time into Carbon instances with the specified timezone
//         $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $exam->start_time, $timezone);
//         $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $exam->end_time, $timezone);

//         // Get the current time and date in the specified timezone
//         $currentTime = Carbon::now($timezone);
//         $currentDate = $currentTime->toDateString(); // Get date in "Y-m-d" format
//         $examDate = $startTime->toDateString();      // Get exam date in "Y-m-d" format

//         // Check if the current date matches the exam date
//         if ($currentDate !== $examDate) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'Exam submission is only allowed on the exam date',
//             ], 403);
//         }

//         // Check if the current time is within the allowed time frame
//         if ($currentTime->lt($startTime) || $currentTime->gt($endTime)) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'Exam submission is not allowed outside the designated time period',
//             ], 403);
//         }

//         // Initialize counters
//         $totalMarks = 0;
//         $gainedMarks = 0;
//         $totalCorrectAnswers = 0;
//         $totalWrongAnswers = 0;
//         $totalQuestions = 0;

//         // Fetch the questions for the exam
//         $examQuestions = ExamQuestion::where('exam_id', $validated['exam_id'])
//             ->with('question')
//             ->get();

//         // Create a map of correct answers for quick lookup
//         $correctAnswersMap = $examQuestions->mapWithKeys(function ($examQuestion) {
//             return [$examQuestion->question_id => $examQuestion->question->correct_answers];
//         });

//         // Initialize an array to keep track of question responses and marks
//         $questionMarksMap = [];

//         // Track unique question IDs
//         $answeredQuestionIds = [];

//         foreach ($validated['responses'] as $response) {
//             $marks = $response['marks'] ?? 0;
//             $negativeMarks = $response['negative_marks'] ?? 0;
//             $questionId = $response['question_id'];
//             $responseText = $response['response'] ?? '';

//             // Aggregate marks for each question
//             if (!isset($questionMarksMap[$questionId])) {
//                 $questionMarksMap[$questionId] = [
//                     'marks' => 0,
//                     'negative_marks' => 0,
//                     'response' => $responseText,
//                     'your_marks' => 0
//                 ];
//             }
//             $questionMarksMap[$questionId]['marks'] += $marks;
//             $questionMarksMap[$questionId]['negative_marks'] += $negativeMarks;

//             // Track answered question IDs
//             $answeredQuestionIds[$questionId] = true;

//             // Determine if the response is correct based on question type
//             $question = $examQuestions->firstWhere('question_id', $questionId);
//             $correctAnswers = $correctAnswersMap[$questionId] ?? [];

//             if ($question) {
//                 switch ($question->question->question_type) {
//                     case 'MCQ':
//                         // For MCQ, compare if the selected options match correct answers
//                         if (is_array($correctAnswers) && is_array($responseText)) {
//                             $isCorrect = !array_diff($correctAnswers, $responseText) && !array_diff($responseText, $correctAnswers);
//                         } else {
//                             $isCorrect = $responseText == $correctAnswers;
//                         }

//                         if ($isCorrect) {
//                             $gainedMarks += $marks;
//                             $totalCorrectAnswers++;
//                             $questionMarksMap[$questionId]['your_marks'] = $marks;
//                             $questionMarksMap[$questionId]['status'] = 'correct'; // Set status to correct
//                         } else {
//                             $gainedMarks -= $negativeMarks;
//                             $totalWrongAnswers++;
//                             $questionMarksMap[$questionId]['your_marks'] = -$negativeMarks;
//                             $questionMarksMap[$questionId]['status'] = 'incorrect'; // Set status to incorrect
//                         }
//                         break;

//                     case 'Short Answer':
//                     case 'Fill in the Blanks':
//                         // For Short Answer and Fill in the Blanks, keep the response for manual grading
//                         $questionMarksMap[$questionId]['status'] = 'pending'; // Status for manual grading
//                         break;

//                     default:
//                         $questionMarksMap[$questionId]['status'] = 'unknown'; // Handle unknown question types
//                 }
//             }
//         }

//         // Compute total marks by summing the marks of all questions for the exam
//         $totalMarks = $examQuestions->sum('marks');

//         // Count the total number of unique questions answered
//         $totalQuestions = count($answeredQuestionIds);

//         // Check if an exam response already exists
//         $examResponse = ExamResponse::where('exam_id', $validated['exam_id'])
//             ->where('student_id', $validated['student_id'])
//             ->first();

//         // if ($examResponse) {
//         //     return response()->json([
//         //         'status' => true,
//         //         'message' => 'You have already completed this exam.',
//         //     ], 422);
//         // } else {
//             // Create a new record
//             $examResponse = new ExamResponse();
//             $examResponse->exam_id = $validated['exam_id'];
//             $examResponse->student_id = $validated['student_id'];
//             $examResponse->total_marks = $totalMarks;
//             $examResponse->gained_marks = $gainedMarks;
//             $examResponse->passing_marks = $validated['passing_marks'] ?? 0;
//             $examResponse->negative_marks = $request->input('negative_marks', 0);
//             $examResponse->total_correct_answers = $totalCorrectAnswers;
//             $examResponse->total_wrong_answers = $totalWrongAnswers;

//             $examResponse->save();
//         // }

//         // Debugging to confirm what was saved
//         \Log::info('ExamResponse after create or update:', $examResponse->toArray());

//         // Update the got_marks for the exam
//         $exam = Exam::find($validated['exam_id']);
//         $exam->got_marks = $gainedMarks;
//         $exam->save();

//         // Store individual question responses
//         foreach ($questionMarksMap as $questionId => $marksData) {
//             $existingResponse = ExamQuestionResponse::where([
//                 'exam_response_id' => $examResponse->id,
//                 'question_id' => $questionId
//             ])->first();

//             if ($existingResponse) {
//                 // Update the existing record
//                 $existingResponse->response = json_encode($marksData['response']);
//                 $existingResponse->marks = $marksData['marks'];
//                 $existingResponse->negative_marks = $marksData['negative_marks'];
//                 $existingResponse->your_marks = $marksData['your_marks'];
//                 $existingResponse->status = $marksData['status']; // Set status based on grading
//                 $existingResponse->save();
//             } else {
//                 // Create a new record
//                 $newResponse = new ExamQuestionResponse();
//                 $newResponse->exam_response_id = $examResponse->id;
//                 $newResponse->question_id = $questionId;
//                 $newResponse->response = json_encode($marksData['response']);
//                 $newResponse->marks = $marksData['marks'];
//                 $newResponse->negative_marks = $marksData['negative_marks'];
//                 $newResponse->your_marks = $marksData['your_marks'];
//                 $newResponse->status = $marksData['status']; // Set status based on grading
//                 $newResponse->save();
//             }
//         }

//         // Return the stored exam response data
//         return response()->json([
//             'status' => true,
//             'message' => 'Response stored successfully',
//             'data' => [
//                 'exam_response' => $examResponse,
//                 'question_marks' => $questionMarksMap,
//                 'total_question' => $totalQuestions
//             ]
//         ], 201);
//     } catch (\Illuminate\Validation\ValidationException $e) {
//         return response()->json([
//             'status' => false,
//             'message' => 'Validation failed',
//             'errors' => $e->errors()
//         ], 422);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => false,
//             'message' => 'An error occurred',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }
 
// public function storeExamResponse(Request $request)
// {
//     try { 
//         // Validate the request data
//         $validated = $request->validate([
//             'exam_id' => 'required|exists:exams,id',
//             'student_id' => 'required|exists:students,id',
//             'responses' => 'required|array',
//             'responses.*.question_id' => 'required|exists:questions,id',
//             'responses.*.response' => 'nullable',
//             'responses.*.marks' => 'nullable|numeric',
//             'responses.*.negative_marks' => 'nullable|numeric',
//             'passing_marks' => 'nullable|numeric'
//         ]);

//         $timezone = 'Asia/Kolkata';

//         // Find the exam
//         $exam = Exam::find($validated['exam_id']);

//         // Parse the start and end time into Carbon instances with the specified timezone
//         $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $exam->start_time, $timezone);
//         $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $exam->end_time, $timezone);

//         // Get the current time and date in the specified timezone
//         $currentTime = Carbon::now($timezone);
//         $currentDate = $currentTime->toDateString(); // Get date in "Y-m-d" format
//         $examDate = $startTime->toDateString();      // Get exam date in "Y-m-d" format

//         // Check if the current date matches the exam date
//         if ($currentDate !== $examDate) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'Exam submission is only allowed on the exam date',
//             ], 403);
//         }

//         // Check if the current time is within the allowed time frame
//         if ($currentTime->lt($startTime) || $currentTime->gt($endTime)) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'Exam submission is not allowed outside the designated time period',
//             ], 403);
//         }

//         // Initialize counters
//         $totalMarks = 0;
//         $gainedMarks = 0;
//         $totalCorrectAnswers = 0;
//         $totalWrongAnswers = 0;
//         $totalQuestions = 0;

//         // Fetch the questions for the exam
//         $examQuestions = ExamQuestion::where('exam_id', $validated['exam_id'])
//             ->with('question')
//             ->get();

//         // Create a map of correct answers for quick lookup
//         $correctAnswersMap = $examQuestions->mapWithKeys(function ($examQuestion) {
//             return [$examQuestion->question_id => $examQuestion->question->correct_answers];
//         });

//         // Initialize an array to keep track of question responses and marks
//         $questionMarksMap = [];

//         // Track unique question IDs
//         $answeredQuestionIds = [];
        
//         // Track if all questions are MCQs
//         $allQuestionsAreMCQ = true;

//         foreach ($validated['responses'] as $response) {
//             $marks = $response['marks'] ?? 0;
//             $negativeMarks = $response['negative_marks'] ?? 0;
//             $questionId = $response['question_id'];
//             $responseText = $response['response'] ?? '';

//             // Aggregate marks for each question
//             if (!isset($questionMarksMap[$questionId])) {
//                 $questionMarksMap[$questionId] = [
//                     'marks' => 0,
//                     'negative_marks' => 0,
//                     'response' => $responseText,
//                     'your_marks' => 0
//                 ];
//             }
//             $questionMarksMap[$questionId]['marks'] += $marks;
//             $questionMarksMap[$questionId]['negative_marks'] += $negativeMarks;

//             // Track answered question IDs
//             $answeredQuestionIds[$questionId] = true;

//             // Determine if the response is correct based on question type
//             $question = $examQuestions->firstWhere('question_id', $questionId);
//             $correctAnswers = $correctAnswersMap[$questionId] ?? [];

//             if ($question) {
//                 if ($question->question->question_type !== 'MCQ') {
//                     $allQuestionsAreMCQ = false; // If any question is not MCQ
//                 }

//                 switch ($question->question->question_type) {
//                     case 'MCQ':
//                         // For MCQ, compare if the selected options match correct answers
//                         if (is_array($correctAnswers) && is_array($responseText)) {
//                             $isCorrect = !array_diff($correctAnswers, $responseText) && !array_diff($responseText, $correctAnswers);
//                         } else {
//                             $isCorrect = $responseText == $correctAnswers;
//                         }

//                         if ($isCorrect) {
//                             $gainedMarks += $marks;
//                             $totalCorrectAnswers++;
//                             $questionMarksMap[$questionId]['your_marks'] = $marks;
//                             $questionMarksMap[$questionId]['status'] = 'correct'; // Set status to correct
//                         } else {
//                             $gainedMarks -= $negativeMarks;
//                             $totalWrongAnswers++;
//                             $questionMarksMap[$questionId]['your_marks'] = -$negativeMarks;
//                             $questionMarksMap[$questionId]['status'] = 'incorrect'; // Set status to incorrect
//                         }
//                         break; 

//                     case 'Short Answer':
//                     case 'Fill in the Blanks':
//                         // For Short Answer and Fill in the Blanks, keep the response for manual grading
//                         $questionMarksMap[$questionId]['status'] = 'pending'; // Status for manual grading
//                         break;

//                     default:
//                         $questionMarksMap[$questionId]['status'] = 'unknown'; // Handle unknown question types
//                 }
//             }
//         }

//         // Compute total marks by summing the marks of all questions for the exam
//         $totalMarks = $examQuestions->sum('marks');

//         // Count the total number of unique questions answered
//         $totalQuestions = count($answeredQuestionIds);

//         // Check if an exam response already exists
//         $examResponse = ExamResponse::where('exam_id', $validated['exam_id'])
//             ->where('student_id', $validated['student_id'])
//             ->first();

//         // if ($examResponse) {
//         //     return response()->json([
//         //         'status' => true,
//         //         'message' => 'You have already completed this exam.',
//         //     ], 422);
//         // } else {
//             // Create a new record
//             $examResponse = new ExamResponse();
//             $examResponse->exam_id = $validated['exam_id'];
//             $examResponse->student_id = $validated['student_id'];
//             $examResponse->total_marks = $totalMarks;
//             $examResponse->gained_marks = $gainedMarks;
//             $examResponse->passing_marks = $validated['passing_marks'] ?? 0;
//             $examResponse->negative_marks = $request->input('negative_marks', 0);
//             $examResponse->total_correct_answers = $totalCorrectAnswers;
//             $examResponse->total_wrong_answers = $totalWrongAnswers;
//             $examResponse->result_status = $allQuestionsAreMCQ ? 'DONE' : 'PENDING'; // Set result status
//             $examResponse->save();
//         // }

//         // Debugging to confirm what was saved
//         \Log::info('ExamResponse after create or update:', $examResponse->toArray());

//         // Update the got_marks for the exam
//         $exam = Exam::find($validated['exam_id']);
//         $exam->got_marks = $gainedMarks;
//         $exam->save();

//         // Store individual question responses
//         foreach ($questionMarksMap as $questionId => $marksData) {
//             $existingResponse = ExamQuestionResponse::where([
//                 'exam_response_id' => $examResponse->id,
//                 'question_id' => $questionId
//             ])->first();

//             if ($existingResponse) {
//                 // Update the existing record
//                 $existingResponse->response = json_encode($marksData['response']);
//                 $existingResponse->marks = $marksData['marks'];
//                 $existingResponse->negative_marks = $marksData['negative_marks'];
//                 $existingResponse->your_marks = $marksData['your_marks'];
//                 $existingResponse->status = $marksData['status']; // Set status based on grading
//                 $existingResponse->save();
//             } else {
//                 // Create a new record
//                 $newResponse = new ExamQuestionResponse();
//                 $newResponse->exam_response_id = $examResponse->id;
//                 $newResponse->question_id = $questionId;
//                 $newResponse->response = json_encode($marksData['response']);
//                 $newResponse->marks = $marksData['marks'];
//                 $newResponse->negative_marks = $marksData['negative_marks'];
//                 $newResponse->your_marks = $marksData['your_marks'];
//                 $newResponse->status = $marksData['status']; // Set status based on grading
//                 $newResponse->save();
//             }
//         }

//         return response()->json([
//             'status' => true,
//             'message' => 'Exam responses stored successfully.',
//             'exam_response_id' => $examResponse->id,
//             'result_status' => $examResponse->result_status
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => false,
//             'message' => 'An error occurred while storing exam responses.',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }

// public function storeExamResponse(Request $request)
// {
//     try {
//         // Validate the request data
//         $validated = $request->validate([
//             'exam_id' => 'required|exists:exams,id',
//             'student_id' => 'required|exists:students,id',
//             'responses' => 'required|array',
//             'responses.*.question_id' => 'required|exists:questions,id',
//             'responses.*.response' => 'nullable',
//             'responses.*.marks' => 'nullable|numeric',
//             'responses.*.negative_marks' => 'nullable|numeric',
//             'passing_marks' => 'nullable|numeric'
//         ]);

//         $timezone = 'Asia/Kolkata';

//         // Find the exam
//         $exam = Exam::findOrFail($validated['exam_id']);

//         // Parse start and end time into Carbon instances
//         $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $exam->start_time, $timezone);
//         $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $exam->end_time, $timezone);

//         // Get current time and date in the specified timezone
//         $currentTime = Carbon::now($timezone);
//         $currentDate = $currentTime->toDateString();
//         $examDate = $startTime->toDateString();

//         // Validate exam date and time
//         if ($currentDate !== $examDate || $currentTime->lt($startTime) || $currentTime->gt($endTime)) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'Exam submission is only allowed on the exam date and within the designated time period.',
//             ], 403);
//         }

//         // Initialize counters
//         $totalMarks = 0;
//         $gainedMarks = 0;
//         $totalCorrectAnswers = 0;
//         $totalWrongAnswers = 0;
//         $answeredQuestionIds = [];

//         // Fetch exam questions with their correct answers
//         $examQuestions = ExamQuestion::where('exam_id', $validated['exam_id'])
//             ->with('question')
//             ->get();

//         $correctAnswersMap = $examQuestions->keyBy('question_id')->map(function ($examQuestion) {
//             return $examQuestion->question->correct_answers;
//         });

//         // Initialize an array to keep track of question responses and marks
//         $questionMarksMap = [];

//         foreach ($validated['responses'] as $response) {
//             $marks = $response['marks'] ?? 0;
//             $negativeMarks = $response['negative_marks'] ?? 0;
//             $questionId = $response['question_id'];
//             $responseText = $response['response'] ?? '';

//             // Initialize question data if not already set
//             if (!isset($questionMarksMap[$questionId])) {
//                 $questionMarksMap[$questionId] = [
//                     'marks' => 0,
//                     'negative_marks' => 0,
//                     'response' => $responseText,
//                     'your_marks' => 0,
//                     'status' => 'not_attempted'
//                 ];
//             }

//             // Aggregate marks
//             $questionMarksMap[$questionId]['marks'] += $marks;
//             $questionMarksMap[$questionId]['negative_marks'] += $negativeMarks;

//             // Track answered question IDs
//             $answeredQuestionIds[$questionId] = true;

//             // Determine the question type and correctness
//             $question = $examQuestions->firstWhere('question_id', $questionId);
//             if ($question) {
//                 $correctAnswers = $correctAnswersMap[$questionId] ?? [];

//                 if ($question->question->question_type === 'MCQ') {
//                     $isCorrect = is_array($correctAnswers) && is_array($responseText)
//                         ? !array_diff($correctAnswers, $responseText) && !array_diff($responseText, $correctAnswers)
//                         : $responseText == $correctAnswers;

//                     if ($isCorrect) {
//                         $gainedMarks += $marks;
//                         $totalCorrectAnswers++;
//                         $questionMarksMap[$questionId]['your_marks'] = $marks;
//                         $questionMarksMap[$questionId]['status'] = 'correct';
//                     } else {
//                         $gainedMarks -= $negativeMarks;
//                         $totalWrongAnswers++;
//                         $questionMarksMap[$questionId]['your_marks'] = -$negativeMarks;
//                         $questionMarksMap[$questionId]['status'] = 'incorrect';
//                     }
//                 } else {
//                     // For non-MCQ questions
//                     $questionMarksMap[$questionId]['status'] = 'pending';
//                 }
//             }
//         }

//         // Compute total marks
//         $totalMarks = $examQuestions->sum('marks');
//         $totalQuestions = count($answeredQuestionIds);

//         // Check if an exam response already exists
//         $examResponse = ExamResponse::updateOrCreate(
//             [
//                 'exam_id' => $validated['exam_id'],
//                 'student_id' => $validated['student_id']
//             ],
//             [
//                 'total_marks' => $totalMarks,
//                 'gained_marks' => $gainedMarks,
//                 'passing_marks' => $validated['passing_marks'] ?? 0,
//                 'negative_marks' => $request->input('negative_marks', 0),
//                 'total_correct_answers' => $totalCorrectAnswers,
//                 'total_wrong_answers' => $totalWrongAnswers,
//                 'result_status' => $totalQuestions == count($examQuestions->pluck('question_id')->unique()) ? 'DONE' : 'PENDING'
//             ]
//         );

//         // Update exam got_marks
//         $exam->update(['got_marks' => $gainedMarks]);

//         // Store or update individual question responses
//         foreach ($questionMarksMap as $questionId => $marksData) {
//             ExamQuestionResponse::updateOrCreate(
//                 [
//                     'exam_response_id' => $examResponse->id,
//                     'question_id' => $questionId
//                 ],
//                 [
//                     'response' => json_encode($marksData['response']),
//                     'marks' => $marksData['marks'],
//                     'negative_marks' => $marksData['negative_marks'],
//                     'your_marks' => $marksData['your_marks'],
//                     'status' => $marksData['status']
//                 ]
//             );
//         }

//         return response()->json([
//             'status' => true,
//             'message' => 'Exam responses stored successfully.',
//             'exam_response_id' => $examResponse->id,
//             'result_status' => $examResponse->result_status
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => false,
//             'message' => 'An error occurred while storing exam responses.',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }


// public function storeExamResponse(Request $request)
// {
//     try {
//         // Validate the request data
//         $validated = $request->validate([
//             'exam_id' => 'required|exists:exams,id',
//             'student_id' => 'required|exists:students,id',
//             'responses' => 'required|array',
//             'responses.*.question_id' => 'required|exists:questions,id',
//             'responses.*.response' => 'nullable',
//             'responses.*.marks' => 'nullable|numeric',
//             'responses.*.negative_marks' => 'nullable|numeric',
//             'passing_marks' => 'nullable|numeric'
//         ]);

//         $timezone = 'Asia/Kolkata';

//         // Find the exam
//         $exam = Exam::find($validated['exam_id']);

//         // Parse the start and end time into Carbon instances with the specified timezone
//         $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $exam->start_time, $timezone);
//         $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $exam->end_time, $timezone);

//         // Get the current time and date in the specified timezone
//         $currentTime = Carbon::now($timezone);
//         $currentDate = $currentTime->toDateString(); // Get date in "Y-m-d" format
//         $examDate = $startTime->toDateString();      // Get exam date in "Y-m-d" format

//         // Check if the current date matches the exam date
//         if ($currentDate !== $examDate) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'Exam submission is only allowed on the exam date',
//             ], 403);
//         }

//         // Check if the current time is within the allowed time frame
//         if ($currentTime->lt($startTime) || $currentTime->gt($endTime)) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'Exam submission is not allowed outside the designated time period',
//             ], 403);
//         }

//         // Initialize counters
//         $totalMarks = 0;
//         $gainedMarks = 0;
//         $totalCorrectAnswers = 0;
//         $totalWrongAnswers = 0;
//         $totalQuestions = 0;

//         // Fetch the questions for the exam
//         $examQuestions = ExamQuestion::where('exam_id', $validated['exam_id'])
//             ->with('question')
//             ->get();

//         // Create a map of correct answers for quick lookup
//         $correctAnswersMap = $examQuestions->mapWithKeys(function ($examQuestion) {
//             return [$examQuestion->question_id => $examQuestion->question->correct_answers];
//         });

//         // Initialize an array to keep track of question responses and marks
//         $questionMarksMap = [];

//         // Track unique question IDs
//         $answeredQuestionIds = [];
        
//         // Track if all questions are MCQs
//         $allQuestionsAreMCQ = true;

//         foreach ($validated['responses'] as $response) {
//             $marks = $response['marks'] ?? 0;
//             $negativeMarks = $response['negative_marks'] ?? 0;
//             $questionId = $response['question_id'];
//             $responseText = $response['response'] ?? '';

//             // Aggregate marks for each question
//             if (!isset($questionMarksMap[$questionId])) {
//                 $questionMarksMap[$questionId] = [
//                     'marks' => 0,
//                     'negative_marks' => 0,
//                     'response' => $responseText,
//                     'your_marks' => 0
//                 ];
//             }
//             $questionMarksMap[$questionId]['marks'] += $marks;
//             $questionMarksMap[$questionId]['negative_marks'] += $negativeMarks;

//             // Track answered question IDs
//             $answeredQuestionIds[$questionId] = true;

//             // Determine if the response is correct based on question type
//             $question = $examQuestions->firstWhere('question_id', $questionId);
//             $correctAnswers = $correctAnswersMap[$questionId] ?? []; 

//             if ($question) { 
//                 switch ($question->question->question_type) {
//                     case 'MCQ':
//                         // For MCQ, compare if the selected options match correct answers
//                         if (is_array($correctAnswers) && is_array($responseText)) {
//                             $isCorrect = !array_diff($correctAnswers, $responseText) && !array_diff($responseText, $correctAnswers);
//                         } else {
//                             $isCorrect = $responseText == $correctAnswers;
//                         }

//                         if ($isCorrect) {
//                             $gainedMarks += $marks;
//                             $totalCorrectAnswers++;
//                             $questionMarksMap[$questionId]['your_marks'] = $marks;
//                         } else {
//                             $gainedMarks -= $negativeMarks;
//                             $totalWrongAnswers++;
//                             $questionMarksMap[$questionId]['your_marks'] = -$negativeMarks;
//                         }
//                         break;

//                     case 'Short Answer':
//                     case 'Fill in the Blanks':
//                         // For Short Answer and Fill in the Blanks, keep the response for manual grading
//                         $allQuestionsAreMCQ = false;
//                         break;

//                     default:
//                         $allQuestionsAreMCQ = false;
//                 }
//             }
//         }

//         // Compute total marks by summing the marks of all questions for the exam
//         $totalMarks = $examQuestions->sum('marks');

//         // Count the total number of unique questions answered
//         $totalQuestions = count($answeredQuestionIds);

//         // Check if an exam response already exists
//         $examResponse = ExamResponse::where('exam_id', $validated['exam_id'])
//             ->where('student_id', $validated['student_id'])
//             ->first();

//         if ($examResponse) {
//             return response()->json([
//                 'status' => true,
//                 'message' => 'You have already completed this exam.',
//             ], 422);
//         } else {
//             // Create a new record
//             $examResponse = new ExamResponse();
//             $examResponse->exam_id = $validated['exam_id'];
//             $examResponse->student_id = $validated['student_id'];
//             $examResponse->total_marks = $totalMarks;
//             $examResponse->gained_marks = $gainedMarks;
//             $examResponse->passing_marks = $validated['passing_marks'] ?? 0;
//             $examResponse->negative_marks = $request->input('negative_marks', 0);
//             $examResponse->total_correct_answers = $totalCorrectAnswers;
//             $examResponse->total_wrong_answers = $totalWrongAnswers;
//             $examResponse->save();
//         }

//         // Debugging to confirm what was saved
//         \Log::info('ExamResponse after create or update:', $examResponse->toArray());

//         // Update the got_marks for the exam
//         $exam = Exam::find($validated['exam_id']); 
//         $exam->got_marks = $gainedMarks;
//         $exam->save();

//         // Store individual question responses
//         foreach ($questionMarksMap as $questionId => $marksData) {
//             $existingResponse = ExamQuestionResponse::where([
//                 'exam_response_id' => $examResponse->id,
//                 'question_id' => $questionId
//             ])->first();

//             if ($existingResponse) {
//                 // Update the existing record
//                 $existingResponse->response = json_encode($marksData['response']);
//                 $existingResponse->marks = $marksData['marks'];
//                 $existingResponse->negative_marks = $marksData['negative_marks'];
//                 $existingResponse->your_marks = $marksData['your_marks']; 
//                 $existingResponse->status = in_array(
//                     $examQuestions->firstWhere('question_id', $questionId)->question->question_type,
//                     ['Short Answer', 'Fill in the Blanks']
//                 ) ? 'pending' : 'correct';   
//                 $existingResponse->save(); 
//             } else {
//                 // Create a new record
//                 $newResponse = new ExamQuestionResponse();
//                 $newResponse->exam_response_id = $examResponse->id;
//                 $newResponse->question_id = $questionId;
//                 $newResponse->response = json_encode($marksData['response']);
//                 $newResponse->marks = $marksData['marks'];
//                 $newResponse->negative_marks = $marksData['negative_marks'];
//                 $newResponse->your_marks = $marksData['your_marks'];
//                 $newResponse->status = in_array(
//                     $examQuestions->firstWhere('question_id', $questionId)->question->question_type,
//                     ['Short Answer', 'Fill in the Blanks']
//                 ) ? 'pending' : 'correct';
//                 $newResponse->save();
//             }
//         }

//         // Return the stored exam response data
//         return response()->json([
//             'status' => true,
//             'message' => 'Response stored successfully',
//             'data' => [
//                 'exam_response' => $examResponse,
//                 'question_marks' => $questionMarksMap,
//                 'total_questions' => $totalQuestions
//             ]
//         ], 201);
//     } catch (\Illuminate\Validation\ValidationException $e) {
//         return response()->json([
//             'status' => false,
//             'message' => 'Validation failed',
//             'errors' => $e->errors()
//         ], 422);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => false,
//             'message' => 'An error occurred',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }

// public function storeExamResponse(Request $request)
// {
//     try {
//         // Validate the request data
//         $validated = $request->validate([
//             'exam_id' => 'required|exists:exams,id',
//             'student_id' => 'required|exists:students,id',
//             'responses' => 'required|array',
//             'responses.*.question_id' => 'required|exists:questions,id',
//             'responses.*.response' => 'nullable',
//             'responses.*.marks' => 'nullable|numeric',
//             'responses.*.negative_marks' => 'nullable|numeric',
//             'passing_marks' => 'nullable|numeric'
//         ]);

//         $timezone = 'Asia/Kolkata';
//         $currentTime = Carbon::now($timezone);

//         // Find the exam and parse times
//         $exam = Exam::findOrFail($validated['exam_id']);
//         $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $exam->start_time, $timezone);
//         $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $exam->end_time, $timezone);

//         // Validate exam date and time
//         if ($currentTime->lt($startTime) || $currentTime->gt($endTime) || $currentTime->toDateString() !== $startTime->toDateString()) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'Exam submission is only allowed on the exam date and within the designated time period.',
//             ], 403);
//         }

//         $examResponse = ExamResponse::where('exam_id', $validated['exam_id'])
//             ->where('student_id', $validated['student_id'])
//             ->first();

//         // if ($examResponse) {
//         //     return response()->json([
//         //         'status' => true,
//         //         'message' => 'You have already completed this exam.',
//         //     ], 422);
//         // } 

//         // Fetch all questions for the exam with their correct answers
//         $examQuestions = ExamQuestion::where('exam_id', $validated['exam_id'])
//             ->with('question')
//             ->get()
//             ->keyBy('question_id');

//         $correctAnswersMap = $examQuestions->mapWithKeys(function ($examQuestion) {
//             return [$examQuestion->question_id => $examQuestion->question->correct_answers];
//         });

//         $responses = $validated['responses'];

//         // Initialize variables
//         $totalMarks = $examQuestions->sum('marks');
//         $gainedMarks = 0;
//         $totalCorrectAnswers = 0;
//         $totalWrongAnswers = 0;
//         $answeredQuestionIds = [];
//         $questionMarksMap = [];

//         foreach ($responses as $response) {
//             $questionId = $response['question_id'];
//             $responseText = $response['response'] ?? '';
//             $marks = $response['marks'] ?? 0;
//             $negativeMarks = $response['negative_marks'] ?? 0;

//             // Prepare question data
//             if (!isset($questionMarksMap[$questionId])) {
//                 $questionMarksMap[$questionId] = [
//                     'marks' => $marks,
//                     'negative_marks' => $negativeMarks,
//                     'response' => $responseText,
//                     'your_marks' => 0,
//                     'status' => 'not_attempted'
//                 ];
//             }

//             $answeredQuestionIds[$questionId] = true;

//             // Check correctness
//             $question = $examQuestions->get($questionId);
//             if ($question) {
//                 $correctAnswers = $correctAnswersMap->get($questionId, []);
//                 $questionType = $question->question->question_type;

//                 if ($questionType === 'MCQ') {
//                     $isCorrect = is_array($correctAnswers) && is_array($responseText)
//                         ? !array_diff($correctAnswers, $responseText) && !array_diff($responseText, $correctAnswers)
//                         : $responseText == $correctAnswers;

//                     if ($isCorrect) {
//                         $gainedMarks += $marks;
//                         $totalCorrectAnswers++;
//                         $questionMarksMap[$questionId]['your_marks'] = $marks;
//                         $questionMarksMap[$questionId]['status'] = 'correct';
//                     } else {
//                         $gainedMarks -= $negativeMarks;
//                         $totalWrongAnswers++;
//                         $questionMarksMap[$questionId]['your_marks'] = -$negativeMarks;
//                         $questionMarksMap[$questionId]['status'] = 'incorrect';
//                     }
//                 } else {
//                     $questionMarksMap[$questionId]['status'] = 'pending';
//                 }
//             }
//         }

//         // Check if an exam response already exists and update/create as necessary
//         $examResponse = ExamResponse::updateOrCreate(
//             [
//                 'exam_id' => $validated['exam_id'],
//                 'student_id' => $validated['student_id']
//             ],
//             [
//                 'total_marks' => $totalMarks,
//                 'gained_marks' => $gainedMarks,
//                 'passing_marks' => $validated['passing_marks'] ?? 0,
//                 'negative_marks' => $request->input('negative_marks', 0),
//                 'total_correct_answers' => $totalCorrectAnswers,
//                 'total_wrong_answers' => $totalWrongAnswers,
//                 'result_status' => count($answeredQuestionIds) === $examQuestions->count() ? 'DONE' : 'PENDING'
//             ] 
//         );

//         // Store or update individual question responses
//         foreach ($questionMarksMap as $questionId => $marksData) {
//             ExamQuestionResponse::updateOrCreate(
//                 [
//                     'exam_response_id' => $examResponse->id,
//                     'question_id' => $questionId
//                 ],
//                 [
//                     'response' => json_encode($marksData['response']),
//                     'marks' => $marksData['marks'],
//                     'negative_marks' => $marksData['negative_marks'],
//                     'your_marks' => $marksData['your_marks'],
//                     'status' => $marksData['status']
//                 ]
//             );
//         }

//         return response()->json([
//             'status' => true,
//             'message' => 'Exam responses stored successfully.',
//             'exam_response_id' => $examResponse->id,
//             'result_status' => $examResponse->result_status
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => false,
//             'message' => 'An error occurred while storing exam responses.',
//             'error' => $e->getMessage()
//         ], 500);
//     } 
// }

// public function storeExamResponse(Request $request)
// {
//     try {
//         // Validate the request data
//         $validated = $request->validate([
//             'exam_id' => 'required|exists:exams,id',
//             'student_id' => 'required|exists:students,id',
//             'responses' => 'required|array',
//             'responses.*.question_id' => 'required|exists:questions,id',
//             'responses.*.response' => 'nullable',
//             'responses.*.marks' => 'nullable|numeric',
//             'responses.*.negative_marks' => 'nullable|numeric',
//             'passing_marks' => 'nullable|numeric'
//         ]);

//         $timezone = 'Asia/Kolkata';
//         $currentTime = Carbon::now($timezone);

//         // Find the exam and parse times
//         $exam = Exam::findOrFail($validated['exam_id']);
//         $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $exam->start_time, $timezone);
//         $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $exam->end_time, $timezone);

//         // Validate exam date and time
//         if ($currentTime->lt($startTime) || $currentTime->gt($endTime) || $currentTime->toDateString() !== $startTime->toDateString()) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'Exam submission is only allowed on the exam date and within the designated time period.',
//             ], 403);
//         }

//         $examResponse = ExamResponse::where('exam_id', $validated['exam_id'])
//             ->where('student_id', $validated['student_id'])
//             ->first();

//         // if ($examResponse) {
//         //     return response()->json([
//         //         'status' => true,
//         //         'message' => 'You have already completed this exam.',
//         //     ], 422);
//         // } 

//         // Fetch all questions for the exam with their correct answers
//         $examQuestions = ExamQuestion::where('exam_id', $validated['exam_id'])
//             ->with('question')
//             ->get()
//             ->keyBy('question_id');

//         $correctAnswersMap = $examQuestions->mapWithKeys(function ($examQuestion) {
//             return [$examQuestion->question_id => $examQuestion->question->correct_answers];
//         });

//         $responses = $validated['responses'];

//         // Initialize variables
//         $totalMarks = $examQuestions->sum('marks');
//         $gainedMarks = 0;
//         $totalCorrectAnswers = 0;
//         $totalWrongAnswers = 0;
//         $answeredQuestionIds = [];
//         $questionMarksMap = [];

//         // Initialize question marks map with default values
//         foreach ($examQuestions as $questionId => $examQuestion) {
//             $questionMarksMap[$questionId] = [
//                 'marks' => 0,
//                 'negative_marks' => 0,
//                 'response' => null,
//                 'your_marks' => 0,
//                 'status' => 'not_attempted'
//             ];
//         }

//         foreach ($responses as $response) {
//             $questionId = $response['question_id'];
//             $responseText = $response['response'] ?? '';
//             $marks = $response['marks'] ?? 0;
//             $negativeMarks = $response['negative_marks'] ?? 0;

//             // Prepare question data
//             if (isset($questionMarksMap[$questionId])) {
//                 $questionMarksMap[$questionId]['response'] = $responseText;
//                 $questionMarksMap[$questionId]['marks'] = $marks;
//                 $questionMarksMap[$questionId]['negative_marks'] = $negativeMarks;
//                 $questionMarksMap[$questionId]['status'] = 'pending'; // Default to pending
//             }

//             $answeredQuestionIds[$questionId] = true;

//             // Check correctness
//             $question = $examQuestions->get($questionId);
//             if ($question) {
//                 $correctAnswers = $correctAnswersMap->get($questionId, []);
//                 $questionType = $question->question->question_type;

//                 if ($questionType === 'MCQ') {
//                     if (is_null($responseText) || $responseText === '') {
//         // If the student didn't answer, mark it as not attempted
//         $questionMarksMap[$questionId]['status'] = 'not_attempted';
//     } else {
//         $isCorrect = is_array($correctAnswers) && is_array($responseText)
//             ? !array_diff($correctAnswers, $responseText) && !array_diff($responseText, $correctAnswers)
//             : $responseText == $correctAnswers;

//         if ($isCorrect) {
//             $gainedMarks += $marks;
//             $totalCorrectAnswers++;
//             $questionMarksMap[$questionId]['your_marks'] = $marks;
//             $questionMarksMap[$questionId]['status'] = 'correct';
//         } else {
//             $gainedMarks -= $negativeMarks;
//             $totalWrongAnswers++;
//             $questionMarksMap[$questionId]['your_marks'] = -$negativeMarks;
//             $questionMarksMap[$questionId]['status'] = 'incorrect';
//         }
//     }
//                 } else {
//                     $questionMarksMap[$questionId]['status'] = 'pending';
//                 }
//             }
//         }

//         // Check if an exam response already exists and update/create as necessary
//         $examResponse = ExamResponse::updateOrCreate(
//             [
//                 'exam_id' => $validated['exam_id'],
//                 'student_id' => $validated['student_id']
//             ],
//             [
//                 'total_marks' => $totalMarks,
//                 'gained_marks' => $gainedMarks,
//                 'passing_marks' => $validated['passing_marks'] ?? 0,
//                 'negative_marks' => $request->input('negative_marks', 0),
//                 'total_correct_answers' => $totalCorrectAnswers,
//                 'total_wrong_answers' => $totalWrongAnswers,
//                 'result_status' => count($answeredQuestionIds) === $examQuestions->count() ? 'DONE' : 'PENDING'
//             ] 
//         );

//         // Store or update individual question responses
//         foreach ($questionMarksMap as $questionId => $marksData) {
//             ExamQuestionResponse::updateOrCreate(
//                 [
//                     'exam_response_id' => $examResponse->id,
//                     'question_id' => $questionId
//                 ],
//                 [
//                     'response' => json_encode($marksData['response']),
//                     'marks' => $marksData['marks'],
//                     'negative_marks' => $marksData['negative_marks'],
//                     'your_marks' => $marksData['your_marks'],
//                     'status' => $marksData['status'] ?? 'not_attempted'
//                 ]
//             );
//         }

//         return response()->json([
//             'status' => true,
//             'message' => 'Exam responses stored successfully.',
//             'exam_response_id' => $examResponse->id,
//             'result_status' => $examResponse->result_status
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => false,
//             'message' => 'An error occurred while storing exam responses.',
//             'error' => $e->getMessage()
//         ], 500);
//     } 
// }

// public function storeExamResponse(Request $request)
// {
//     try {
//         // Validate the request data
//         $validated = $request->validate([
//             'exam_id' => 'required|exists:exams,id',
//             'student_id' => 'required|exists:students,id',
//             'responses' => 'required|array',
//             'responses.*.question_id' => 'required|exists:questions,id',
//             'responses.*.response' => 'nullable|array', // Response should be an array for MCQs
//             'responses.*.marks' => 'nullable|numeric',
//             'responses.*.negative_marks' => 'nullable|numeric',
//             'passing_marks' => 'nullable|numeric'
//         ]);

//         $timezone = 'Asia/Kolkata';
//         $currentTime = Carbon::now($timezone);
//          $examResponse = ExamResponse::where('exam_id', $validated['exam_id'])
//             ->where('student_id', $validated['student_id'])
//             ->first();

//         if ($examResponse) {
//             return response()->json([
//                 'status' => true,
//                 'message' => 'You have already completed this exam.',
//             ], 422);
//         } 


//         // Find the exam and parse times
//         $exam = Exam::findOrFail($validated['exam_id']);
//         $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $exam->start_time, $timezone);
//         $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $exam->end_time, $timezone);

//         // Validate exam date and time
//         if ($currentTime->lt($startTime) || $currentTime->gt($endTime) || $currentTime->toDateString() !== $startTime->toDateString()) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'Exam submission is only allowed on the exam date and within the designated time period.',
//             ], 403);
//         }

//         $examResponse = ExamResponse::where('exam_id', $validated['exam_id'])
//             ->where('student_id', $validated['student_id'])
//             ->first();

//         // Fetch all questions for the exam with their correct answers
//         $examQuestions = ExamQuestion::where('exam_id', $validated['exam_id'])
//             ->with('question')
//             ->get()
//             ->keyBy('question_id');

//         $correctAnswersMap = $examQuestions->mapWithKeys(function ($examQuestion) {
//             return [$examQuestion->question_id => $examQuestion->question->correct_answers];
//         });

//         $responses = $validated['responses'];

//         // Initialize variables
//         $totalMarks = $examQuestions->sum('marks');
//         $gainedMarks = 0;
//         $totalCorrectAnswers = 0;
//         $totalWrongAnswers = 0;
//         $answeredQuestionIds = [];
//         $questionMarksMap = [];

//         // Initialize question marks map with default values
//         foreach ($examQuestions as $questionId => $examQuestion) {
//             $questionMarksMap[$questionId] = [
//                 'marks' => 0,
//                 'negative_marks' => 0,
//                 'response' => null,
//                 'your_marks' => 0,
//                 'status' => 'not_attempted'
//             ];
//         } 

//         foreach ($responses as $response) {
//             $questionId = $response['question_id'];
//             $responseText = $response['response'] ?? [];
//             $marks = $response['marks'] ?? 0;
//             $negativeMarks = $response['negative_marks'] ?? 0;

//             // Sort the response array for comparison
//             if (is_array($responseText)) {
//                 sort($responseText);
//             }

//             // Prepare question data
//             if (isset($questionMarksMap[$questionId])) {
//                 $questionMarksMap[$questionId]['response'] = $responseText;
//                 $questionMarksMap[$questionId]['marks'] = $marks;
//                 $questionMarksMap[$questionId]['negative_marks'] = $negativeMarks;
//                 $questionMarksMap[$questionId]['status'] = 'pending'; // Default to pending
//             }

//             $answeredQuestionIds[$questionId] = true;

//             // Check correctness
//             $question = $examQuestions->get($questionId);
//             if ($question) {
//                 $correctAnswers = $correctAnswersMap->get($questionId, []);
//                 $questionType = $question->question->question_type;

//                 if ($questionType === 'MCQ') {
//                     if (empty($responseText)) {
//                         // If the student didn't answer, mark it as not attempted
//                         $questionMarksMap[$questionId]['status'] = 'not_attempted';
//                     } else {
//                         // Sort correct answers for comparison
//                         if (is_array($correctAnswers)) {
//                             sort($correctAnswers);
//                         }

//                         // Compare sorted response and correct answers
//                         $isCorrect = !array_diff($correctAnswers, $responseText) && !array_diff($responseText, $correctAnswers);

//                         if ($isCorrect) {
//                             $gainedMarks += $marks;
//                             $totalCorrectAnswers++;
//                             $questionMarksMap[$questionId]['your_marks'] = $marks;
//                             $questionMarksMap[$questionId]['status'] = 'correct';
//                         } else {
//                             $gainedMarks -= $negativeMarks;
//                             $totalWrongAnswers++;
//                             $questionMarksMap[$questionId]['your_marks'] = -$negativeMarks;
//                             $questionMarksMap[$questionId]['status'] = 'incorrect';
//                         }
//                     }
//                 } else {
//                     $questionMarksMap[$questionId]['status'] = 'pending';
//                 }
//             }
//         }

//         // Check if an exam response already exists and update/create as necessary
//         $examResponse = ExamResponse::updateOrCreate(
//             [
//                 'exam_id' => $validated['exam_id'],
//                 'student_id' => $validated['student_id']
//             ],
//             [
//                 'total_marks' => $totalMarks,
//                 'gained_marks' => $gainedMarks,
//                 'passing_marks' => $validated['passing_marks'] ?? 0,
//                 'negative_marks' => $request->input('negative_marks', 0),
//                 'total_correct_answers' => $totalCorrectAnswers,
//                 'total_wrong_answers' => $totalWrongAnswers,
//                 'result_status' => count($answeredQuestionIds) === $examQuestions->count() ? 'DONE' : 'PENDING'
//             ]
//         );

//         // Store or update individual question responses
//         foreach ($questionMarksMap as $questionId => $marksData) {
//             ExamQuestionResponse::updateOrCreate(
//                 [
//                     'exam_response_id' => $examResponse->id,
//                     'question_id' => $questionId
//                 ],
//                 [
//                     'response' => json_encode($marksData['response']),
//                     'marks' => $marksData['marks'],
//                     'negative_marks' => $marksData['negative_marks'],
//                     'your_marks' => $marksData['your_marks'],
//                     'status' => $marksData['status'] ?? 'not_attempted'
//                 ]
//             );
//         }

//         return response()->json([
//             'status' => true,
//             'message' => 'Exam responses stored successfully.',
//             'exam_response_id' => $examResponse->id,
//             'result_status' => $examResponse->result_status
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => false,
//             'message' => 'An error occurred while storing exam responses.',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }



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
        $totalNegativeMarks = 0;

        // Fetch the existing exam response
        $examResponse = ExamResponse::where('exam_id', $validated['exam_id'])
            ->where('student_id', $validated['student_id'])
            ->firstOrFail();

        // Fetch the exam questions
        $examQuestions = ExamQuestion::where('exam_id', $validated['exam_id'])
            ->with('question')
            ->get()
            ->keyBy('question_id');

        // Fetch pending status question responses
        $pendingResponses = ExamQuestionResponse::where('exam_response_id', $examResponse->id)
            ->where('status', 'pending')
            ->get();

         

        // Update question responses based on manual grades
        foreach ($validated['manual_grades'] as $grade) {
            $questionId = $grade['question_id'];
            $manualMarks = $grade['marks'];

            // Fetch the question and its current response
            $question = $examQuestions->get($questionId);
            $response = $pendingResponses->firstWhere('question_id', $questionId);

            if ($response && in_array($question->question->question_type, ['Short Answer', 'Fill in the Blanks'])) {
                // Update the response with the manual marks
                $response->your_marks = $manualMarks;
                $response->status = 'graded'; // Mark as graded
                $response->save();

                // Update total marks and counters
                $totalMarks += $manualMarks;
                $totalCorrectAnswers += $manualMarks > 0 ? 1 : 0;
                $totalWrongAnswers += $manualMarks <= 0 ? 1 : 0;
                $totalNegativeMarks += $manualMarks < 0 ? abs($manualMarks) : 0;
            }
        }

        // Recalculate the total gained marks, correct answers, and wrong answers
        $examResponse->gained_marks += $totalMarks;
        $examResponse->total_correct_answers += $totalCorrectAnswers;
        $examResponse->total_wrong_answers += $totalWrongAnswers;
        $examResponse->negative_marks += $totalNegativeMarks;
        $examResponse->result_status = 'DONE';
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
 



// public function getResponsesByBatchAndStudent(Request $request) 
// {
//     // Validate the incoming request data 
//     $validated = $request->validate([
//         'batch_id' => 'required|exists:exams,batch_id',
//         'student_id' => 'required|exists:students,id',
//         'exam_id' => 'nullable|exists:exams,id' 
//     ]); 

//     try {
//         // Retrieve all exams associated with the batch
//         $query = Exam::where('batch_id', $validated['batch_id']);

//         // If an exam_id is provided, filter by it
//         if (isset($validated['exam_id'])) {
//             $query->where('id', $validated['exam_id']);
//         }

//         $exams = $query->get();

//         //   $student = Student::find($validated['student_id']); 
//         //  $student->image = $student->image ? url('/Student/' . $student->image) : null;

//         // Initialize an array to hold exam responses
//         $responses = [];

//         foreach ($exams as $exam) {
//             // Fetch the response for the specific student and exam
//             $examResponse = ExamResponse::where('exam_id', $exam->id)
//                 ->where('student_id', $validated['student_id'])
//                 ->first();

//             if ($examResponse) {
//                 // Retrieve all sections associated with the exam
//                 $sections = Section::where('exam_id', $exam->id)->get();

//                 $sectionData = [];

//                 foreach ($sections as $section) {
//                     // Retrieve all questions for each section
//                     $questions = ExamQuestion::where('exam_id', $exam->id)
//                         ->where('section_id', $section->id)
//                         ->with('question') // Assuming you have a relationship set up in the model
//                         ->get(); 

//                     $questionResponses = [];
//                     $sectionTotalMarks = 0;
//                     $sectionObtainedMarks = 0;
//                     $sectionCorrectAnswers = 0; // Total number of correct answers for the section
//                     $sectionWrongAnswers = 0; // Total number of wrong answers for the section

//                     foreach ($questions as $examQuestion) {
//                         // Retrieve the student's response for each question
//                         $studentResponse = ExamQuestionResponse::where('exam_response_id', $examResponse->id)
//                             ->where('question_id', $examQuestion->question_id)
//                             ->first();
//                             // $studentResponse->status='graded';

//                         // Calculate total marks for the section
//                         $sectionTotalMarks += $examQuestion->marks;

//                         // Calculate obtained marks for the section
//                         $obtainedMarks = 0;
//                         $isCorrect = false;

//                         if ($studentResponse) {
//                             $obtainedMarks = $studentResponse->your_marks ?? 0; // Use the 'your_marks' field
//                             $sectionObtainedMarks += $obtainedMarks; // Add obtained marks to the total

//                             // Check if the answer is correct or wrong using the 'status' field
//                             if ($studentResponse->status === 'correct') {
//                                 $sectionCorrectAnswers++;
//                                 $isCorrect = true; 
//                             } else {
//                                 $sectionWrongAnswers++;
//                             }
//                         }  
//                         $questionOptions = $examQuestion->question->options ? $examQuestion->question->options : null;

//                         $questionResponses[] = [
//                             'question_id' => $examQuestion->question_id,
//                             'question_text' => $examQuestion->question->question_text, 
//                             'question_type' => $examQuestion->question->question_type, 
//                             'question_img' => $examQuestion->question->image ? url(Storage::url($examQuestion->question->image)) : null,
//                             'question_options' => $questionOptions, 
//                             'max_marks' => $examQuestion->marks,
//                             'correct_answer' => $examQuestion->question->correct_answers, // Assuming a correct_answers field
//                         //   'student_response' => $studentResponse->response 
//                         //         ? array_map('strval', json_decode($studentResponse->response)) 
//                         //         : [],
//                          'student_response' => $studentResponse->response
//                              ? array_map('strval', (array) json_decode($studentResponse->response, true))
//                            : [],
//                             'gained_marks' => $obtainedMarks,
//                             'negative_marks' => $studentResponse->negative_marks ?? null,
//                             // 'status' => $isCorrect ? 'correct' : 'wrong',

//                               'status' => $studentResponse->status ?? 'incorrect',
                            
//                         ];
//                     }

//                     $sectionData[] = [
//                         // 'section_id' => $section->id,
//                         'section_name' => $section->name,
//                         'total_marks' => $sectionTotalMarks,
//                         'obtained_marks' => $sectionObtainedMarks,
//                         // 'total_correct_answers' => $sectionCorrectAnswers,
//                         // 'total_wrong_answers' => $sectionWrongAnswers,
//                         'questions' => $questionResponses
//                     ];
//                 }

//                 // Append exam response and section-wise question responses to the array
//                 $responses[] = [
//                     'exam_id' => $exam->id,
//                     //  'img' => $student->image,
//                     'exam' => $exam,
//                     'exam_response' => $examResponse,
//                     'sections' => $sectionData
//                 ];
//             }
//         }

//         return response()->json([
//             'status' => true,
//             'message' => 'Responses retrieved successfully',
//             'data' => $responses
//         ], 200);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => false,
//             'message' => 'An error occurred while retrieving responses',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }
  
public function getResponsesByBatchAndStudent(Request $request) 
{
    // Validate the incoming request data 
    $validated = $request->validate([
        'batch_id' => 'required|exists:exams,batch_id',
        'student_id' => 'required|exists:students,id',
        'exam_id' => 'nullable|exists:exams,id' 
    ]); 

    try {
        // Retrieve all exams associated with the batch
        $query = Exam::where('batch_id', $validated['batch_id']);

        // If an exam_id is provided, filter by it
        if ($validated['exam_id']) {
            $query->where('id', $validated['exam_id']);
        }

        $exams = $query->get();

        if ($exams->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No exams found for the provided batch and exam ID',
                'data' => []
            ], 404);
        }

        // Initialize an array to hold exam responses
        $responses = [];

        foreach ($exams as $exam) {
            // Fetch the response for the specific student and exam
            $examResponse = ExamResponse::where('exam_id', $exam->id)
                ->where('student_id', $validated['student_id'])
                ->first();

            if (!$examResponse) {
                continue; // Skip to the next exam if no response is found
            }

            // Retrieve all sections associated with the exam
            $sections = Section::where('exam_id', $exam->id)->get();

            if ($sections->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No sections found for exam ID ' . $exam->id,
                    'data' => []
                ], 404);
            }

            $sectionData = [];

            foreach ($sections as $section) {
                // Retrieve all questions for each section
                $questions = ExamQuestion::where('exam_id', $exam->id)
                    ->where('section_id', $section->id)
                    ->with('question')
                    ->get();

                if ($questions->isEmpty()) {
                    continue; // Skip to the next section if no questions are found
                }

                $questionResponses = [];
                $sectionTotalMarks = 0;
                $sectionObtainedMarks = 0;

                foreach ($questions as $examQuestion) {
                    // Retrieve the student's response for each question
                    $studentResponse = ExamQuestionResponse::where('exam_response_id', $examResponse->id)
                        ->where('question_id', $examQuestion->question_id)
                        ->first();

                    // Calculate total marks for the section
                    $sectionTotalMarks += $examQuestion->marks;

                    // Calculate obtained marks for the section
                    $obtainedMarks = $studentResponse ? ($studentResponse->your_marks ?? 0) : 0;
                    $sectionObtainedMarks += $obtainedMarks;

                    $questionOptions = $examQuestion->question->options ? $examQuestion->question->options : null;

                    $questionResponses[] = [
                        'question_id' => $examQuestion->question_id,
                        'question_text' => $examQuestion->question->question_text, 
                        'question_type' => $examQuestion->question->question_type, 
                        'question_img' => $examQuestion->question->image ? url(Storage::url($examQuestion->question->image)) : null,
                        'question_options' => $questionOptions, 
                        'max_marks' => $examQuestion->marks,
                        'correct_answer' => $examQuestion->question->correct_answers,
                        'student_response' => $studentResponse && $studentResponse->response
                            ? array_map('strval', (array) json_decode($studentResponse->response, true))
                            : [],
                        'gained_marks' => $obtainedMarks,
                        'negative_marks' => $studentResponse ? $studentResponse->negative_marks : null,
                        'status' => $studentResponse ? $studentResponse->status : 'not attempted',
                    ];
                }

                $sectionData[] = [
                    'section_name' => $section->name,
                    'total_marks' => $sectionTotalMarks,
                    'obtained_marks' => $sectionObtainedMarks,
                    'questions' => $questionResponses
                ];
            }

            $responses[] = [
                'exam_id' => $exam->id,
                'exam' => $exam,
                'exam_response' => $examResponse,
                'sections' => $sectionData
            ];
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



public function getStudentExamResult(Request $request)
{
    try {
        // Validate the request data
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'exam_id' => 'required|exists:exams,id'
        ]);

        $studentId = $validated['student_id'];
        $examId = $validated['exam_id'];

        // Fetch all courses for the student
        $courses = DB::table('courses_enrollements')
            ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
            ->where('courses_enrollements.student_id', $studentId)
            ->select('courses.id as course_id', 'courses.name as course_name', 'courses_enrollements.enrollment_date')
            ->get();

        // Check if the exam is related to any of the student's enrolled courses
        $examExists = Exam::where('id', $examId)
                          ->whereIn('batch_id', $courses->pluck('course_id'))
                          ->exists();

        if (!$examExists) { 
            return response()->json([
                'status' => false,
                'message' => 'The specified exam is not related to the student\'s enrolled courses.'
            ], 404);
        }

        // Fetch the exam response for the student for the specified exam ID
        $examResponse = ExamResponse::select(
                'exam_responses.id',
                'exam_responses.exam_id',
                'exam_responses.student_id',
                'exam_responses.total_marks',
                'exam_responses.gained_marks',
                'exam_responses.passing_marks',
                'exam_responses.negative_marks',
                'exam_responses.total_correct_answers',
                'exam_responses.total_wrong_answers',
                'exam_responses.created_at',
                'exam_responses.updated_at',
                'exams.name as exam_name',
                'exams.start_time',
                'exams.end_time',
                'courses.name as course_name'
            )
            ->join('exams', 'exam_responses.exam_id', '=', 'exams.id')
            ->join('courses', 'exams.batch_id', '=', 'courses.id')
            ->where('exam_responses.exam_id', $examId)
            ->where('exam_responses.student_id', $studentId)
            ->first();

        // If no response is found, return an error
        if (!$examResponse) {
            return response()->json([
                'status' => false,
                'message' => 'No results found for the specified exam and student.'
            ], 404);
        }

        // Fetch sections and questions data for the exam
        $sections = Section::where('exam_id', $examResponse->exam_id)
            ->with(['examQuestions.question'])
            ->get();

        // Prepare sections data with questions, correct answers, student's answers, and gained marks
        $sectionsData = $sections->map(function ($section) use ($studentId, $examId) {
            return [
                'section_id' => $section->id,
                'section_name' => $section->name,
                'questions' => $section->examQuestions->map(function ($examQuestion) use ($studentId, $examId) {
                    $question = $examQuestion->question;

                    // Check if the image path exists
                    $question_img = $question->image ? url(Storage::url($question->image)) : null;

                    // Fetch the student's response for this question
                    $studentResponse = ExamQuestionResponse::where('exam_id', $examId)
                        ->where('question_id', $examQuestion->question_id)
                        ->where('student_id', $studentId)
                        ->first();

                    return [
                        'question_id' => $examQuestion->question_id, 
                        'question_text' => $question->question_text,
                        'question_img' => $question_img,
                        'question_type' => $question->question_type,
                        'options' => $question->options,
                        'correct_answers' => $question->correct_answers,
                        'student_answer' => $studentResponse ? $studentResponse->answer : null,
                        'gained_marks' => $studentResponse ? $studentResponse->gained_marks : 0,
                        'marks' => $examQuestion->marks,
                        'negative_marks' => $examQuestion->negative_marks,
                    ];
                })
            ];
        });

        // Merge exam response with sections and questions data
        $resultData = [
            'exam_details' => $examResponse,
            'sections' => $sectionsData
        ];

        return response()->json([
            'status' => true,
            'message' => 'Student result fetched successfully',
            'data' => $resultData
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

public function getStudentAllResult(Request $request)
{
    try {
        // Validate the request data
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id'
        ]);

        $studentId = $validated['student_id'];

        // Fetch all courses for the student
        $courses = DB::table('courses_enrollements')
            ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
            ->where('courses_enrollements.student_id', $studentId)
            ->select('courses.id as course_id', 'courses.name as course_name', 'courses_enrollements.enrollment_date')
            ->get();

        // Collect all exam IDs from these courses
        $examIds = Exam::whereIn('batch_id', $courses->pluck('course_id'))->pluck('id');

        // Fetch exam responses for the student in the specified courses
        $examResponses = ExamResponse::select(
                'exam_responses.id',
                'exam_responses.exam_id',
                'exam_responses.student_id',
                'exam_responses.total_marks',  
                'exam_responses.gained_marks',
                'exam_responses.passing_marks',
                'exam_responses.negative_marks',
                'exam_responses.total_correct_answers',
                'exam_responses.total_wrong_answers',
                'exam_responses.created_at',
                'exam_responses.updated_at',
                'exams.name as exam_name',
                'exams.start_time',
                'exams.end_time',
                'courses.name as course_name',
                "courses.id as course_id"
            )
            ->join('exams', 'exam_responses.exam_id', '=', 'exams.id')
            ->join('courses', 'exams.batch_id', '=', 'courses.id')
            ->whereIn('exam_responses.exam_id', $examIds)
            ->where('exam_responses.student_id', $studentId)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Student results fetched successfully',
            'data' => $examResponses
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


public function getAllStudentsResults(Request $request)
{
    try { 
        // Validate the request data
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id', // Required course ID
            'exam_id' => 'required|exists:exams,id' // Required exam ID
        ]);

        // Base query to fetch all exams filtered by course_id and exam_id
        $examIds = Exam::where('batch_id', $validated['course_id'])
            ->where('id', $validated['exam_id'])
            ->pluck('id');

        // Fetch exam responses for all students
        $examResponses = ExamResponse::select(
             'exam_responses.id',  
             'students.id as student_id',
             DB::raw("IF(students.image IS NOT NULL AND students.image != '', CONCAT('" . url('/Student/') . "/', students.image), null) as student_image"),

                'students.name as student_name', 
                'students.email as student_email', 
                'students.phone as student_phone',
                'exam_responses.exam_id', 
                'exam_responses.student_id', 
                'exam_responses.total_marks', 
                'exam_responses.gained_marks', 
                'exam_responses.passing_marks', 
                'exam_responses.negative_marks', 
                'exam_responses.total_correct_answers', 
                'exam_responses.total_wrong_answers', 
                'exam_responses.result_status', 
                'exam_responses.created_at', 
                'exam_responses.updated_at', 
                'exams.name as exam_name',
               
            )
            ->join('exams', 'exam_responses.exam_id', '=', 'exams.id')
            ->join('students', 'exam_responses.student_id', '=', 'students.id')
            ->whereIn('exam_responses.exam_id', $examIds)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'All student results fetched successfully',
            'data' => $examResponses
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

public function getStudentResults(Request $request)
{
    try { 
        // Validate the request data
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id', // Required course ID
            'exam_id' => 'required|exists:exams,id', // Required exam ID
            'student_id' => 'required|exists:students,id' // Required student ID
        ]);

        // Base query to fetch all exams filtered by course_id and exam_id
        $examIds = Exam::where('batch_id', $validated['course_id'])
            ->where('id', $validated['exam_id'])
            ->pluck('id');

        // Fetch exam responses for the specific student
        $examResponses = ExamResponse::select(
             'exam_responses.id',  
             'students.id as student_id',
             DB::raw("IF(students.image IS NOT NULL AND students.image != '', CONCAT('" . url('/Student/') . "/', students.image), null) as student_image"),
             'students.name as student_name', 
             'students.email as student_email', 
             'students.phone as student_phone',
             'exam_responses.exam_id', 
             'exam_responses.student_id', 
             'exam_responses.total_marks', 
             'exam_responses.gained_marks', 
             'exam_responses.passing_marks', 
             'exam_responses.negative_marks', 
             'exam_responses.total_correct_answers', 
             'exam_responses.total_wrong_answers', 
             'exam_responses.result_status', 
             'exam_responses.created_at', 
             'exam_responses.updated_at', 
             'exams.name as exam_name'
            )
            ->join('exams', 'exam_responses.exam_id', '=', 'exams.id')
            ->join('students', 'exam_responses.student_id', '=', 'students.id')
            ->whereIn('exam_responses.exam_id', $examIds)
            ->where('exam_responses.student_id', $validated['student_id']) // Filter by student_id
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Student results fetched successfully',
            'data' => $examResponses
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

        $timezone = 'Asia/Kolkata';

        // Find the exam
        $exam = Exam::find($validated['exam_id']);

        // Parse the start and end time into Carbon instances with the specified timezone
        $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $exam->start_time, $timezone);
        $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $exam->end_time, $timezone);

        // Get the current time and date in the specified timezone
        $currentTime = Carbon::now($timezone);
        $currentDate = $currentTime->toDateString(); // Get date in "Y-m-d" format
        $examDate = $startTime->toDateString();      // Get exam date in "Y-m-d" format

        // Check if the current date matches the exam date
        if ($currentDate !== $examDate) {
            return response()->json([
                'status' => false,
                'message' => 'Exam submission is only allowed on the exam date',
            ], 403);
        }

        // Check if the current time is within the allowed time frame
        if ($currentTime->lt($startTime) || $currentTime->gt($endTime)) {
            return response()->json([
                'status' => false,
                'message' => 'Exam submission is not allowed outside the designated time period',
            ], 403);
        }

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

        // Create a map of correct answers for quick lookup
        $correctAnswersMap = $examQuestions->mapWithKeys(function ($examQuestion) {
            return [$examQuestion->question_id => $examQuestion->question->correct_answers];
        });

        // Initialize an array to keep track of question responses and marks
        $questionMarksMap = [];

        // Track unique question IDs
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
                    'your_marks' => 0,
                    'status' => 'not attempted' // Default status
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
                            $questionMarksMap[$questionId]['your_marks'] = $marks;
                            $questionMarksMap[$questionId]['status'] = 'correct';
                        } else {
                            $gainedMarks -= $negativeMarks;
                            $totalWrongAnswers++;
                            $questionMarksMap[$questionId]['your_marks'] = -$negativeMarks;
                            $questionMarksMap[$questionId]['status'] = 'incorrect';
                        }
                        break;

                    case 'Short Answer':
                    case 'Fill in the Blanks':
                        // For Short Answer and Fill in the Blanks, keep the response for manual grading
                        $questionMarksMap[$questionId]['status'] = 'pending';
                        break;

                    default:
                        $questionMarksMap[$questionId]['status'] = 'not attempted';
                }
            }
        }

        // Compute total marks by summing the marks of all questions for the exam
        $totalMarks = $examQuestions->sum('marks');

        // Count the total number of unique questions answered
        $totalQuestions = count($answeredQuestionIds);

        // Check if an exam response already exists
        $examResponse = ExamResponse::where('exam_id', $validated['exam_id'])
            ->where('student_id', $validated['student_id'])
            ->first();

        if ($examResponse) {
            return response()->json([
                'status' => true,
                'message' => 'You have already completed this exam.',
            ], 422);
        } else {
            // Create a new record
            $examResponse = new ExamResponse();
            $examResponse->exam_id = $validated['exam_id'];
            $examResponse->student_id = $validated['student_id'];
            $examResponse->total_marks = $totalMarks;
            $examResponse->gained_marks = $gainedMarks;
            $examResponse->passing_marks = $validated['passing_marks'] ?? 0;
            $examResponse->negative_marks = $request->input('negative_marks', 0);
            $examResponse->total_correct_answers = $totalCorrectAnswers;
            $examResponse->total_wrong_answers = $totalWrongAnswers;
            $examResponse->save();
        }

        // Debugging to confirm what was saved
        \Log::info('ExamResponse after create or update:', $examResponse->toArray());

        // Update the got_marks for the exam
        $exam = Exam::find($validated['exam_id']);
        $exam->got_marks = $gainedMarks;
        $exam->save();

        // Store individual question responses
        foreach ($questionMarksMap as $questionId => $marksData) {
            $existingResponse = ExamQuestionResponse::where([
                'exam_response_id' => $examResponse->id,
                'question_id' => $questionId
            ])->first();

            if ($existingResponse) {
                // Update the existing record
                $existingResponse->response = json_encode($marksData['response']);
                $existingResponse->marks = $marksData['marks'];
                $existingResponse->negative_marks = $marksData['negative_marks'];
                $existingResponse->your_marks = $marksData['your_marks'];
                $existingResponse->status = $marksData['status'];
                $existingResponse->save();
            } else {
                // Create a new record
                $newResponse = new ExamQuestionResponse();
                $newResponse->exam_response_id = $examResponse->id;
                $newResponse->question_id = $questionId;
                $newResponse->response = json_encode($marksData['response']);
                $newResponse->marks = $marksData['marks'];
                $newResponse->negative_marks = $marksData['negative_marks'];
                $newResponse->your_marks = $marksData['your_marks'];
                $newResponse->status = $marksData['status'];
                $newResponse->save();
            }
        }

        // Return the stored exam response data
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Response stored successfully',
            'data' => [
                'exam_response' => $examResponse,
                'question_marks' => $questionMarksMap,
                'total_questions' => $totalQuestions
            ]
        ], 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => false,
            'code' => 422,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'An error occurred',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
