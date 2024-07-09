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
    $validatedData = $request->validate([
        'subject' => 'required|string',
        'batch_id' => 'nullable|exists:courses,id',
        'day_of_week' => 'required|in:mon,tue,wed,thu,fri,sat',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
    ]);
 
    $classRoutine = ClassRoutine::create($validatedData);

    // Optionally load subject and batch relationships if needed
    // $classRoutine->load('subject', 'batch');

    return response()->json([
        'status' => 'success',
        'message' => 'Class routine created successfully',
        'data' => $classRoutine->toArray(), // Convert model to array for response
    ], 201);
}
 


    public function show($id)
    {
        $classRoutine = ClassRoutine::with('subject', 'batch')->findOrFail($id);

        return response()->json($classRoutine);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'batch_id' => 'nullable|exists:batches,id',
            'day_of_week' => 'required|in:mon,tue,wed,thu,fri,sat',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $classRoutine = ClassRoutine::findOrFail($id);
        $classRoutine->update($validatedData);

        return response()->json($classRoutine);
    }

    public function destroy($id)
    {
        $classRoutine = ClassRoutine::findOrFail($id);
        $classRoutine->delete();

        return response()->json(null, 204);
    }
}
