<?php
 
namespace App\Http\Controllers;

use App\Models\Questions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;


class QuestionController extends Controller
{

    // public function __construct()
    // {
    //     $this->middleware('api');
    // }
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
    try {
        // Validate the incoming request with 'stream' as a required parameter
        $validated = $request->validate([
            'stream' => 'required|string|max:255',
        ]);
        
        // Retrieve the validated stream parameter
        $stream = $validated['stream'];

        // Query the questions table
        $query = Questions::orderBy('created_at', 'desc');

        $query->where('stream', $stream);

        $questions = $query->get();

        // Transform the questions to include the full image URL
        $questions->transform(function ($question) {
            if ($question->image) {
                $question->image = url(Storage::url($question->image));
            }
            return $question;
        });

        return response()->json(['status' => 'success', 'data' => $questions]);

    } catch (ValidationException $e) {
        // Return a validation error response
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    }
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
            'stream' =>  'required', 
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
         if ($question->image) {
        $question->image = url(Storage::url($question->image));
    } else {
        $question->image = null; // Ensure image is set to null if not present
    }

        return response()->json(['status' => 'success', 'data' => $question], 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'data' => $question,
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
    } else {
        $question->image = null; // Ensure image is set to null if not present
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
        //  'image' => 'nullable|string',
         'stream' =>  'required', 
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

     if ($request->has('stream')) {
        $question->stream = $request->input('stream');
    }
   
  // Handle image upload or URL
if ($request->hasFile('image')) {
    // Delete old image if it exists and is not a URL
    if ($question->image && !filter_var($question->image, FILTER_VALIDATE_URL)) {
        \Storage::disk('public')->delete($question->image);
    }
    // Store new image and update the path
    $imagePath = $request->file('image')->store('questions', 'public');
    $question->image = $imagePath;
} elseif ($request->input('image') && filter_var($request->input('image'), FILTER_VALIDATE_URL)) {
   
} elseif ($request->input('image') === null) {
    // If no new image file is uploaded and image input is null, delete the old image if it exists
    if ($question->image && !filter_var($question->image, FILTER_VALIDATE_URL)) {
        \Storage::disk('public')->delete($question->image);
    }
    $question->image = null;
}

// Save the question
$question->save();


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
