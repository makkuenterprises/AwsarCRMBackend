<?php

namespace App\Http\Controllers;

use App\Models\Questions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;


class QuestionController extends Controller
{
    public function index()
    {
          $questions = Questions::orderBy('created_at', 'desc')->get();
             $questions->transform(function ($question) {
        if ($question->image) {
            $question->image = url(Storage::url($question->image));
        }
        return $question;
    });
        return response()->json(['status' => 'success', 'data' => $questions]);
    }
    
public function index2(Request $request)
{
    $stream = $request->input('stream');

    $query = Questions::orderBy('created_at', 'desc');

    if ($stream) {
        $query->where('stream', $stream);
    }

    $questions = $query->get();

    $questions->transform(function ($question) {
        if ($question->image) {
            $question->image = url(Storage::url($question->image));
        }
        return $question;
    });

    return response()->json(['status' => 'success', 'data' => $questions]);
}
   public function store(Request $request)  
{
    try {
    //    dd($request->all());
    $validator = Validator::make($request->all(), [
            'question_text' => 'required|string',
            'question_type' => 'required|in:MCQ,Short Answer,Fill in the Blanks',
            'options' => 'nullable|array',
            'correct_answers' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'stream' =>  'required', // Validate batch_id
        ]);

        if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

        $question = new Questions();
        $question->question_text = $request->input('question_text');
        $question->question_type = $request->input('question_type');
        $question->options = $request->input('options');
        $question->correct_answers = $request->input('correct_answers');
         $question->stream = $request->input('stream'); 

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('questions', 'public');
            $question->image = $imagePath;
        }

        $question->save();

        return response()->json(['status' => 'success', 'data' => $question], 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation Error',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred while processing your request.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    public function show($id)
    {
        $question = Questions::find($id);
         if ($question->image) {
        $question->image = url(Storage::url($question->image));
    }
        if (!$question) {
            return response()->json(['status' => 'error', 'message' => 'Question not found'], 404);
        }
        return response()->json(['status' => 'success', 'data' => $question]);
    }



public function update(Request $request, $id)
{
    $question = Questions::find($id); 
    if (!$question) {
        return response()->json(['status' => 'error', 'message' => 'Question not found'], 404);
    }

    // Define validation rules
    $validator = Validator::make($request->all(), [
        'question_text' => 'required|string',
        'question_type' => 'required|in:MCQ,Short Answer,Fill in the Blanks',
        'options' => 'nullable|array',
        'correct_answers' => 'nullable|array',
        'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    // Check if validation failed
    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    // Update fields only if they are present in the request
    if ($request->has('question_text')) {
        $question->question_text = $request->input('question_text');
    }
    if ($request->has('question_type')) {
        $question->question_type = $request->input('question_type');
    }
    if ($request->has('options')) {
        $question->options = $request->input('options');
    }
    if ($request->has('correct_answers')) {
        $question->correct_answers = $request->input('correct_answers');
    }

    // Handle image upload
    if ($request->hasFile('image')) {
        // Delete old image if exists
        if ($question->image) {
            \Storage::disk('public')->delete($question->image);
        }
        // Store new image and update the path
        $imagePath = $request->file('image')->store('questions', 'public');
        $question->image = $imagePath;
    }

    // Save the updated question
    $question->save();

    // Return success response
    return response()->json(['status' => 'success', 'data' => $question]);
}



    public function destroy($id)
    {
        $question = Questions::find($id);
        if (!$question) {
            return response()->json(['status' => 'error', 'message' => 'Question not found'], 404);
        }

        // Delete image if exists
        if ($question->image) {
            \Storage::disk('public')->delete($question->image);
        }

        $question->delete();

        return response()->json(['status' => 'success', 'message' => 'Question deleted successfully']);
    }
}
