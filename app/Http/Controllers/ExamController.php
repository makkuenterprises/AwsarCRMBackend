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
    try { 
        // Validate the request
        $request->validate([
            'name' => 'required|string',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s',
            'batch_id' => 'required|exists:courses,id', // Changed from courses to batches
            'passing_marks' => 'required|numeric',
            'sections' => 'required|array',
            'sections.*.name' => 'required|string',
            'sections.*.questions' => 'required|array',
            'sections.*.questions.*.id' => 'required|exists:questions,id',
            'sections.*.questions.*.marks' => 'required|numeric',
            'sections.*.questions.*.negative_marks' => 'nullable|numeric', // Make negative_marks optional
        ]);

        // Create the exam
        $exam = Exam::create($request->only('name', 'start_time', 'end_time', 'batch_id', 'passing_marks'));

        // Process each section
        foreach ($request->sections as $sectionData) {
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
                    'negative_marks' => $questionData['negative_marks'] ?? null, // Handle optional negative_marks
                ]);
            }
        }

        // Return success response
        return response()->json([ 'status' => true,
            'code' => 200,'message' => 'Exam created successfully'], 201);
        
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

        // Get all questions associated with the exam
        $examQuestions = ExamQuestion::where('exam_id', $examId)
            ->with('question') // Assuming you have a relationship defined in ExamQuestion model
            ->get();

        // Prepare the result
        $questions = $examQuestions->map(function ($examQuestion) {
            return [
                'question_id' => $examQuestion->question_id,
                'question_text' => $examQuestion->question->question_text,
                'question_type' => $examQuestion->question->question_type,
                'options' => $examQuestion->question->options,
                'correct_answers' => $examQuestion->question->correct_answers,
                'marks' => $examQuestion->marks,
                'negative_marks' => $examQuestion->negative_marks,
            ];
        });

        // Return success response with questions data
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Questions retrieved successfully',
            'data' => $questions
        ], 200);

    } catch (\Exception $e) {
        // Handle any errors
        return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'An error occurred while retrieving questions',
            'error' => $e->getMessage()
        ], 500);
    }
}

}

