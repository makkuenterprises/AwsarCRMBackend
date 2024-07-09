<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ClassRoutine;
use Illuminate\Http\Request;

class ClassRoutineController extends Controller
{
    //
    public function index()
    {
        $classRoutines = ClassRoutine::with('subject', 'batch')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Class routines retrieved successfully',
            'data' => $classRoutines
        ], 200);
    }

public function store(Request $request)
{
    // Validate request data
    $validatedData = $request->validate([
        'subject' => 'required|string',
        'batch_id' => 'nullable|exists:courses,id',
        'day_of_week' => 'required|in:mon,tue,wed,thu,fri,sat',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
    ]);

    // Check if there's already a routine with the same day, time, and batch
    $existingRoutine = ClassRoutine::where('day_of_week', $validatedData['day_of_week'])
                                    ->where('start_time', '<', $validatedData['end_time'])
                                    ->where('end_time', '>', $validatedData['start_time'])
                                    ->where('batch_id', $validatedData['batch_id'])
                                    ->exists();

    if ($existingRoutine) {
        return response()->json([
            'status' => 'error',
            'message' => 'Another class routine already exists for the same day, time, and batch.',
        ], 400);
    }

    // Create the class routine if validation passes
    $classRoutine = ClassRoutine::create($validatedData);

    return response()->json([
        'status' => 'success',
        'message' => 'Class routine created successfully',
        'data' => $classRoutine->toArray(), // Convert model to array for response
    ], 201);
}



public function show($batch_id)
{
    try {
        // Retrieve all class routines for the given batch ID
        $classRoutines = ClassRoutine::where('batch_id', $batch_id)
                                     ->get(); 

        // Check if any routines were found
        if ($classRoutines->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No class routines found for the batch',
            ], 404);
        }

        // Return a JSON response with the routines
        return response()->json([
            'status' => 'success',
            'message' => 'Class routines retrieved successfully',
            'data' => $classRoutines,
        ], 200);

    } catch (\Exception $e) {
        // Return a JSON response with an error message
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve class routines',
        ], 500);
    }
}


public function update(Request $request, $id)
{
    // Validate request data
    $validatedData = $request->validate([
        'subject' => 'required|string',
        'batch_id' => 'nullable|exists:batches,id',
        'day_of_week' => 'required|in:mon,tue,wed,thu,fri,sat',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
    ]);

    // Find the class routine by ID
    $classRoutine = ClassRoutine::findOrFail($id);

    // Check if the update changes the day, time, batch, or subject
    if ($classRoutine->day_of_week != $validatedData['day_of_week'] ||
        $classRoutine->start_time != $validatedData['start_time'] ||
        $classRoutine->end_time != $validatedData['end_time'] ||
        $classRoutine->batch_id != $validatedData['batch_id'] ||
        $classRoutine->subject_id != $validatedData['subject']) {

        // Check if there's already a routine with the same day, time, and batch
        $existingRoutine = ClassRoutine::where('day_of_week', $validatedData['day_of_week'])
                                        ->where('start_time', '<', $validatedData['end_time'])
                                        ->where('end_time', '>', $validatedData['start_time'])
                                        ->where('batch_id', $validatedData['batch_id'])
                                        ->where('id', '!=', $id) // Exclude current routine from check
                                        ->exists();

        if ($existingRoutine) {
            return response()->json([
                'status' => 'error',
                'message' => 'Another class routine already exists for the same day, time, and batch.',
            ], 400);
        }
    }

    // Update the class routine if validation passes
    $classRoutine->update($validatedData);

    return response()->json([
        'status' => 'success',
        'message' => 'Class routine updated successfully',
        'data' => $classRoutine->toArray(), // Convert model to array for response
    ], 200); 
}


public function destroy($id)
{
    try {
        // Find the class routine by ID or throw a 404 error if not found
        $classRoutine = ClassRoutine::findOrFail($id);

        // Delete the class routine
        $classRoutine->delete();

        // Return a JSON response with a success message and 204 status code (No Content)
        return response()->json([
            'status' => 'success',
            'message' => 'Class routine deleted successfully',
        ], 204);

    } catch (\Exception $e) {
        // Return a JSON response with an error message and 404 status code (Not Found)
        return response()->json([
            'status' => 'error',
            'message' => 'Class routine not found or could not be deleted',
        ], 404);
    }
}

}
