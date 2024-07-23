<?php

namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Models\Subject;
use Illuminate\Http\Request; 
 

class subjectController extends Controller
{
     // Create a new subject
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $subject = new Subject();
            $subject->name = $request->name;
            $subject->save();

            return response()->json(['message' => 'Subject created successfully!', 'subject' => $subject], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to create subject!', 'error' => $e->getMessage()], 500);
        }
    }

    // Update an existing subject
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $subject = Subject::find($id);

            if (!$subject) {
                return response()->json(['message' => 'Subject not found!'], 404);
            }

            $subject->name = $request->name;
            $subject->save();

            return response()->json(['message' => 'Subject updated successfully!', 'subject' => $subject], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to update subject!', 'error' => $e->getMessage()], 500);
        }
    }

    // Delete a subject
    public function delete($id)
    {
        try {
            $subject = Subject::find($id);

            if (!$subject) {
                return response()->json(['message' => 'Subject not found!'], 404);
            }

            $subject->delete();

            return response()->json(['message' => 'Subject deleted successfully!'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to delete subject!', 'error' => $e->getMessage()], 500);
        }
    }

       public function index()
    {
        try {
            $subjects = Subject::all();
            return response()->json(['subjects' => $subjects], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to retrieve subjects!', 'error' => $e->getMessage()], 500);
        }
    }

      public function show($id)
    {
        try {
            $subject = Subject::find($id);

            if (!$subject) {
                return response()->json(['message' => 'Subject not found!'], 404);
            }

            return response()->json(['subject' => $subject], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to retrieve subject!', 'error' => $e->getMessage()], 500);
        }
    }
}
