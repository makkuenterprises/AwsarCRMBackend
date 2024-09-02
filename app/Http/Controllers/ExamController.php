<?php
namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Section;
use App\Models\Question;
use App\Models\ExamQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;


class ExamController extends Controller 
{ 
    // public function __construct()
    // {
    //     $this->middleware('api');
    // }
    
// public function createExam(Request $request)
// {
//     try { 
//         // Validate the request
//         $request->validate([
//             'name' => 'required|string',
//             'start_time' => 'required|date_format:Y-m-d H:i:s',
//             'end_time' => 'required|date_format:Y-m-d H:i:s',
//             'batch_id' => 'required|exists:courses,id',
//             'passing_marks' => 'required|numeric',
//             'sections' => 'required|array',
//             'sections.*.name' => 'required|string',
//             'sections.*.questions' => 'required|array',
//             'sections.*.questions.*.id' => 'required|exists:questions,id',
//             'sections.*.questions.*.marks' => 'required|numeric',
//             'sections.*.questions.*.negative_marks' => 'nullable|numeric',
//             'sections.*.questions.*.got_marks' => 'nullable|numeric', // Include got_marks in validation
//         ]);

//         // Create the exam
//         $exam = Exam::create($request->only('name', 'start_time', 'end_time', 'batch_id', 'passing_marks'));

//         // Process each section
//         foreach ($request->sections as $sectionData) {
//             // Create the section
//             $section = Section::create([
//                 'exam_id' => $exam->id,
//                 'name' => $sectionData['name'],
//             ]);

//             // Process each question in the section
//             foreach ($sectionData['questions'] as $questionData) {
//                 ExamQuestion::create([
//                     'exam_id' => $exam->id,
//                     'question_id' => $questionData['id'],
//                     'section_id' => $section->id,
//                     'marks' => $questionData['marks'],
//                     'negative_marks' => $questionData['negative_marks'] ?? null, // Handle optional negative_marks
//                     'got_marks' => $questionData['got_marks'] ?? 0, // Set got_marks, default to 0 if not provided
//                 ]);
//             }
//         } 

//         // Return success response
//         return response()->json([ 'status' => true,
//             'code' => 200, 'message' => 'Exam created successfully'], 201);
        
//     } catch (\Illuminate\Validation\ValidationException $e) {
//         // Handle validation errors
//         return response()->json([
//             'status' => false,
//             'code' => 422,
//             'message' => 'Validation failed',
//             'errors' => $e->errors()
//         ], 422);
        
//     } catch (\Exception $e) {
//         // Handle other errors
//         return response()->json([
//             'status' => false,
//             'code' => 500,
//             'message' => 'An error occurred while creating the exam',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }

public function createExam(Request $request)
{
    DB::beginTransaction();

    try {
        // Validate the request
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
            'sections.*.questions.*.negative_marks' => 'nullable|numeric',
            'sections.*.questions.*.got_marks' => 'nullable|numeric',
        ]);

        // Initialize total marks
        $totalMarks = 0;
           // Set timezone to Asia/Kolkata
        $timezone = 'Asia/Kolkata';

        // Create the exam
        $exam = Exam::create([
            'name' => $request->name,
            'start_time' => Carbon::createFromFormat('Y-m-d H:i:s', $request->start_time, $timezone),
            'end_time' => Carbon::createFromFormat('Y-m-d H:i:s', $request->end_time, $timezone),
            'batch_id' => $request->batch_id,
            'passing_marks' => $request->passing_marks,
            'total_marks' => 0, // Placeholder for total marks
        ]);

        // Process each section
        foreach ($request->sections as $sectionData) {
            // Initialize section total marks
            $sectionTotalMarks = 0;

            // Create the section
            $section = Section::create([
                'exam_id' => $exam->id,
                'name' => $sectionData['name'],
            ]);

            // Process each question in the section
            foreach ($sectionData['questions'] as $questionData) {
                ExamQuestion::create([
                    'exam_id' => $exam->id,
                    'question_id' => $questionData['id'],
                    'section_id' => $section->id,
                    'marks' => $questionData['marks'],
                    'negative_marks' => $questionData['negative_marks'] ?? null,
                    'got_marks' => $questionData['got_marks'] ?? 0,
                ]);

                // Add question marks to section total
                $sectionTotalMarks += $questionData['marks'];
            }

            // Add section total marks to overall total
            $totalMarks += $sectionTotalMarks;
        }
// dd($totalMarks);
        // Update the exam with the total marks
        // $exam->update(['total_marks' => $totalMarks]);
        $exam->total_marks= $totalMarks;
        $exam->save();

        DB::commit();

        // Return success response
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Exam created successfully',
            'data' => $exam
        ], 201);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        // Handle validation errors
        return response()->json([
            'status' => false,
            'code' => 422,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        DB::rollBack();
        // Handle other errors
        return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'An error occurred while creating the exam',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function listExamsForBatch($batchId)
{
    try {
        // Fetch all exams associated with the specific batch
        $exams = Exam::where('batch_id', $batchId)->orderBy('created_at', 'desc')->get();
        // Check if exams are found
        if ($exams->isEmpty()) {
            return response()->json([
                'status' => false, 
                'code' => 404,
                'message' => 'No exams found for the specified batch'
            ], 404);
        }

        // Return success response with exams data
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Exams retrieved successfully',
            'data' => $exams
        ], 200);

    } catch (\Exception $e) {
        // Handle any errors
        return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'An error occurred while retrieving exams',
            'error' => $e->getMessage()
        ], 500);
    }
} 
 

public function listExams()
{ 
    try {
        // Fetch all exams associated with the specific batch
        $exams = Exam::orderBy('created_at', 'desc')->get();
        // Check if exams are found
        if ($exams->isEmpty()) {
            return response()->json([
                'status' => false, 
                'code' => 404,
                'message' => 'No exams found for the specified batch'
            ], 404);
        }

        // Return success response with exams data
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Exams retrieved successfully',
            'data' => $exams
        ], 200);

    } catch (\Exception $e) {
        // Handle any errors
        return response()->json([
            'status' => false, 
            'code' => 500,
            'message' => 'An error occurred while retrieving exams',
            'error' => $e->getMessage()
        ], 500);
    }
} 

public function listQuestionsForExam($examId) 
{
    try {
        // Fetch the exam
        $exam = Exam::findOrFail($examId);

        // Get all sections associated with the exam
        $sections = Section::where('exam_id', $examId)
            ->with(['examQuestions.question']) // Load questions for each section
            ->get(); 

        // Prepare the result
        $data = $sections->map(function ($section) { 
            return [
                'section_id' => $section->id,
                'section_name' => $section->name,
                'questions' => $section->examQuestions->map(function ($examQuestion) {

                    $question = $examQuestion->question;

                // Check if the image path exists
                if ($question->image) {
                    // Generate a URL for the image stored in the 'public' disk
                    $question_img = url(Storage::url($question->image));
                } else {
                    // Set image to null if not present
                    $question_img = null;
                }

                    return [
                        'question_id' => $examQuestion->question_id, 
                        'question_text' => $examQuestion->question->question_text,
                        'question_img' => $question_img,
                        'question_type' => $examQuestion->question->question_type,
                        'options' => $examQuestion->question->options,
                        'correct_answers' => $examQuestion->question->correct_answers,
                        'marks' => $examQuestion->marks,
                        'negative_marks' => $examQuestion->negative_marks,
                    ];
                })
            ];
        });

        // Return success response with sections and questions data
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Sections and questions retrieved successfully',
            'data' => $data
        ], 200);
    } catch (\Exception $e) {
        // Handle any errors
        return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'An error occurred while retrieving sections and questions',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getExamDetails(Request $request)
{
    try {
        // Retrieve batchId and examId from the request
        $batchId = $request->input('batchId');
        $examId = $request->input('examId');

        // Validate the inputs
        if (!$batchId || !$examId) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => 'batchId and examId are required'
            ], 400);
        }

        // Fetch the exam associated with the specific batch and exam ID
        $exam = Exam::where('batch_id', $batchId)
                    ->where('id', $examId)
                    ->first(['id', 'name', 'start_time', 'end_time', 'passing_marks', 'created_at']);

        // Check if the exam is found
        if (!$exam) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => 'No exam found for the specified batch and exam ID'
            ], 404);
        }

        // Calculate the duration
        $startTime = Carbon::parse($exam->start_time);
        $endTime = Carbon::parse($exam->end_time);
        $durationInMinutes = $startTime->diffInMinutes($endTime);

        // Retrieve sections for the exam and calculate marks and question count per section
        $sections = Section::where('exam_id', $exam->id)->get(['id', 'name']);

        $sectionDetails = $sections->map(function ($section) use ($exam) {
            // Count questions in each section
            $questionCount = ExamQuestion::where('exam_id', $exam->id)
                                         ->where('section_id', $section->id)
                                         ->count();

            // Calculate total marks and negative marks for each section
            $totalMarks = ExamQuestion::where('exam_id', $exam->id)
                                      ->where('section_id', $section->id)
                                      ->sum('marks');
            $negativeMarks = ExamQuestion::where('exam_id', $exam->id)
                                         ->where('section_id', $section->id)
                                         ->sum('negative_marks');

            return [
                'name' => $section->name,
                'total_questions' => $questionCount,
                'total_marks' => $totalMarks, 
                'negative_marks' => $negativeMarks
            ];
        });

        // Calculate total marks, negative marks, and total questions for the entire exam
        $totalMarksExam = ExamQuestion::where('exam_id', $exam->id)->sum('marks');
        $negativeMarksExam = ExamQuestion::where('exam_id', $exam->id)->sum('negative_marks');
        $totalQuestionsExam = ExamQuestion::where('exam_id', $exam->id)->count();

        // Format the exam details
        $examDetails = [
            'id' => $exam->id,
            'name' => $exam->name,
            'start_time' => $exam->start_time,
            'end_time' => $exam->end_time,
            'passing_marks' => $exam->passing_marks,
            'created_at' => $exam->created_at,
            'duration' => $durationInMinutes . ' minutes',
            'total_marks' => $totalMarksExam,
            'negative_marks' => $negativeMarksExam,
            'total_questions' => $totalQuestionsExam,
            'sections' => $sectionDetails // Include sections with question counts and marks
        ];

        // Return success response with exam data
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Exam retrieved successfully',
            'data' => $examDetails
        ], 200);

    } catch (\Exception $e) {
        // Handle any errors
        return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'An error occurred while retrieving the exam',
            'error' => $e->getMessage()
        ], 500);
    }
}



public function getExamsForStudent(Request $request)
{
    try {
        // Retrieve the student ID from the request
        $studentId = $request->input('student_id');

        // Validate the student ID
        if (!$studentId) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => 'Student ID is required'
            ], 400);
        }

        // Fetch the courses the student is enrolled in
        $courses = DB::table('courses_enrollements')
            ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
            ->where('courses_enrollements.student_id', $studentId)
            ->select('courses.id as course_id')
            ->get();

        // Extract course IDs
        $courseIds = $courses->pluck('course_id')->toArray();

        // Check if any courses are found
        if (empty($courseIds)) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => 'No courses found for the specified student'
            ], 404);
        }

        // Fetch exams associated with the found courses
        $exams = DB::table('exams')
            ->whereIn('batch_id', $courseIds)
            ->get(['id', 'name', 'start_time', 'end_time', 'passing_marks', 'batch_id', 'created_at']);

        // Check if exams are found
        if ($exams->isEmpty()) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => 'No exams found for the courses the student is enrolled in'
            ], 404);
        }

        // Prepare the exam details
        $examDetails = $exams->map(function ($exam) {
            // Calculate the duration in minutes
            $startTime = Carbon::parse($exam->start_time);
            $endTime = Carbon::parse($exam->end_time);
            $durationInMinutes = $startTime->diffInMinutes($endTime);

            // Retrieve sections for the exam and calculate marks and question count per section
            $sections = Section::where('exam_id', $exam->id)->get(['id', 'name']);

            $sectionDetails = $sections->map(function ($section) use ($exam) {
                // Count questions in each section
                $questionCount = ExamQuestion::where('exam_id', $exam->id)
                                             ->where('section_id', $section->id)
                                             ->count();

                // Calculate total marks and negative marks for each section
                $totalMarks = ExamQuestion::where('exam_id', $exam->id)
                                          ->where('section_id', $section->id)
                                          ->sum('marks');
                $negativeMarks = ExamQuestion::where('exam_id', $exam->id)
                                             ->where('section_id', $section->id)
                                             ->sum('negative_marks');

                return [
                    'name' => $section->name,
                    'total_questions' => $questionCount,
                    'total_marks' => $totalMarks,
                    'negative_marks' => $negativeMarks
                ];
            });

            // Calculate total marks, negative marks, and total questions for the entire exam
            $totalMarksExam = ExamQuestion::where('exam_id', $exam->id)->sum('marks');
            $negativeMarksExam = ExamQuestion::where('exam_id', $exam->id)->sum('negative_marks');
            $totalQuestionsExam = ExamQuestion::where('exam_id', $exam->id)->count();

            // Format the exam details
            return [
                'exam_id' => $exam->id,
                'name' => $exam->name,
                'start_time' => $exam->start_time,
                'end_time' => $exam->end_time,
                'passing_marks' => $exam->passing_marks,
                'created_at' => $exam->created_at,
                'batch_id' => $exam->batch_id, // Include batch ID
                'duration' => $durationInMinutes . ' minutes',
                'total_marks' => $totalMarksExam,
                'negative_marks' => $negativeMarksExam,
                'total_questions' => $totalQuestionsExam,
                'sections' => $sectionDetails // Include sections with question counts and marks
            ];
        });

        // Return success response with exam data
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Exams retrieved successfully',
            'data' => $examDetails
        ], 200);

    } catch (\Exception $e) {
        // Handle any errors
        return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'An error occurred while retrieving exams',
            'error' => $e->getMessage()
        ], 500);
    }
}




}

