<?php
namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Section;
use App\Models\Question;
use App\Models\ExamQuestion;
use Illuminate\Http\Request;
class ExamController extends Controller 
{ 
    
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

        // Create the exam
        $exam = Exam::create([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
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

        // Update the exam with the total marks
        $exam->update(['total_marks' => $totalMarks]);

        // Return success response
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Exam created successfully',
            'data' => $exam
        ], 201);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Handle validation errors
        return response()->json([
            'status' => false,
            'code' => 422,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
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
        $exams = Exam::where('batch_id', $batchId)->get();

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
                    return [
                        'question_id' => $examQuestion->question_id,
                        'question_text' => $examQuestion->question->question_text,
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

}

