<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Subject extends Controller
{
     // Create a new subject
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $subject = new Subject();
        $subject->name = $request->name;
        $subject->save();

        return response()->json(['message' => 'Subject created successfully!', 'subject' => $subject], 201);
    }

    // Update an existing subject
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json(['message' => 'Subject not found!'], 404);
        }

        $subject->name = $request->name;
        $subject->save();

        return response()->json(['message' => 'Subject updated successfully!', 'subject' => $subject], 200);
    }

    // Delete a subject
    public function delete($id)
    {
        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json(['message' => 'Subject not found!'], 404);
        }

        $subject->delete();

        return response()->json(['message' => 'Subject deleted successfully!'], 200);
    }
}
