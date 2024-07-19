<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = Question::all();
        return response()->json(['status' => 'success', 'data' => $questions]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:MCQ,Short Answer,Fill in the Blanks',
            'options' => 'nullable|array',
            'correct_answers' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $question = new Question();
        $question->question_text = $request->input('question_text');
        $question->question_type = $request->input('question_type');
        $question->options = $request->input('options');
        $question->correct_answers = $request->input('correct_answers');

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('questions', 'public');
            $question->image = $imagePath;
        }

        $question->save();

        return response()->json(['status' => 'success', 'data' => $question], 201);
    }

    public function show($id)
    {
        $question = Question::find($id);
        if (!$question) {
            return response()->json(['status' => 'error', 'message' => 'Question not found'], 404);
        }
        return response()->json(['status' => 'success', 'data' => $question]);
    }

    public function update(Request $request, $id)
    {
        $question = Question::find($id);
        if (!$question) {
            return response()->json(['status' => 'error', 'message' => 'Question not found'], 404);
        }

        $request->validate([
            'question_text' => 'sometimes|required|string',
            'question_type' => 'sometimes|required|in:MCQ,Short Answer,Fill in the Blanks',
            'options' => 'nullable|array',
            'correct_answers' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $question->question_text = $request->input('question_text', $question->question_text);
        $question->question_type = $request->input('question_type', $question->question_type);
        $question->options = $request->input('options', $question->options);
        $question->correct_answers = $request->input('correct_answers', $question->correct_answers);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($question->image) {
                \Storage::disk('public')->delete($question->image);
            }
            $imagePath = $request->file('image')->store('questions', 'public');
            $question->image = $imagePath;
        }

        $question->save();

        return response()->json(['status' => 'success', 'data' => $question]);
    }

    public function destroy($id)
    {
        $question = Question::find($id);
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
