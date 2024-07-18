<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ClassRoutine;
use Illuminate\Http\Request;

class ClassRoutineController extends Controller
{
   
public function index()
{
// Fetch all class routines
$classRoutines = ClassRoutine::get();

// Group the class routines by 'day_of_week' and 'batch_id'
$groupedData = $classRoutines->groupBy(function ($routine) {
    return $routine->day_of_week . '_' . $routine->batch_id;
})->values()->all();

        return response()->json([
            'status' => 'success',
            'message' => 'Class routines retrieved successfully',
            'data' => $groupedData
        ], 200);
    }

// public function store(Request $request)
// {
//     // Validate request data
//     $validatedData = $request->validate([
//         'subject' => 'required|string',
//         'batch_id' => 'nullable|exists:courses,id',
//         'day_of_week' => 'required|in:mon,tue,wed,thu,fri,sat',
//         'start_time' => 'required|date_format:H:i',
//         'end_time' => 'required|date_format:H:i|after:start_time',
//     ]);

//     // Check if there's already a routine with the same day, time, and batch
//     $existingRoutine = ClassRoutine::where('day_of_week', $validatedData['day_of_week'])
//                                     ->where('start_time', '<', $validatedData['end_time'])
//                                     ->where('end_time', '>', $validatedData['start_time'])
//                                     ->where('batch_id', $validatedData['batch_id'])
//                                     ->exists();

//     if ($existingRoutine) { 
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Another class routine already exists for the same day, time, and batch.',
//         ], 400);
//     }

//     // Create the class routine if validation passes
//     $classRoutine = ClassRoutine::create($validatedData);

//     return response()->json([
//         'status' => 'success',
//         'message' => 'Class routine created successfully',
//         'data' => $classRoutine->toArray(), // Convert model to array for response
//     ], 201);
// }



// public function show($batch_id)
// { 
//     try {
//         // Retrieve all class routines for the given batch ID
//         $classRoutines = ClassRoutine::where('batch_id', $batch_id)
//                                      ->get(); 

//         // Check if any routines were found
//         if ($classRoutines->isEmpty()) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'No class routines found for the batch',
//             ], 404);
//         }

//         // Return a JSON response with the routines
//         return response()->json([
//             'status' => 'success',
//             'message' => 'Class routines retrieved successfully',
//             'data' => $classRoutines,
//         ], 200);

//     } catch (\Exception $e) {
//         // Return a JSON response with an error message
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Failed to retrieve class routines',
//         ], 500);
//     }
// }
public function store(Request $request)
{
    // Validate request data
    $validatedData = $request->validate([
        'subject' => 'required|string',
        'batch_id' => 'required|exists:courses,id',
        'day_of_week' => 'required|in:mon,tue,wed,thu,fri,sat',
        'start_time' => 'required|date_format:H:i', // Ensure time is in 24-hour format
        'end_time' => 'required|date_format:H:i|after:start_time', // Ensure end time is after start time
    ]);

    try { 
        // Check if start_time and end_time are in 24-hour format
        if (!preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $validatedData['start_time']) ||
            !preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $validatedData['end_time'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid time format. Please use 24-hour format (HH:MM).',
            ], 400);
        }

        // Check if there's already a routine with overlapping time for the same day, batch, and subject
        $existingRoutine = ClassRoutine::where('day_of_week', $validatedData['day_of_week'])
                                        ->where('batch_id', $validatedData['batch_id'])
                                        ->where('subject', $validatedData['subject'])
                                        ->where(function ($query) use ($validatedData) {
                                            $query->where(function ($q) use ($validatedData) {
                                                $q->where(function ($qq) use ($validatedData) {
                                                    $qq->where('start_time', '<=', $validatedData['start_time'])
                                                       ->where('end_time', '>', $validatedData['start_time']);
                                                })->orWhere(function ($qq) use ($validatedData) {
                                                    $qq->where('start_time', '<', $validatedData['end_time'])
                                                       ->where('end_time', '>=', $validatedData['end_time']);
                                                })->orWhere(function ($qq) use ($validatedData) {
                                                    $qq->where('start_time', '>=', $validatedData['start_time'])
                                                       ->where('end_time', '<=', $validatedData['end_time']);
                                                });
                                            });
                                        })
                                        ->exists();

        if ($existingRoutine) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'subject' => ['A routine with the same subject, day, and overlapping time already exists.'],
                ]
            ], 400);
        }
// Check if there's already a routine with overlapping time for the same day and batch
        $existingRoutineTime = ClassRoutine::where('day_of_week', $validatedData['day_of_week'])
                                        ->where('batch_id', $validatedData['batch_id'])
                                        ->where(function ($query) use ($validatedData) {
                                            $query->where(function ($q) use ($validatedData) {
                                                $q->where(function ($qq) use ($validatedData) {
                                                    $qq->where('start_time', '<=', $validatedData['start_time'])
                                                       ->where('end_time', '>', $validatedData['start_time']);
                                                })->orWhere(function ($qq) use ($validatedData) {
                                                    $qq->where('start_time', '<', $validatedData['end_time'])
                                                       ->where('end_time', '>=', $validatedData['end_time']);
                                                })->orWhere(function ($qq) use ($validatedData) {
                                                    $qq->where('start_time', '>=', $validatedData['start_time'])
                                                       ->where('end_time', '<=', $validatedData['end_time']);
                                                });
                                            });
                                        })
                                        ->exists();

        if ($existingRoutineTime) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'subject' => ['A routine with the same day, time, and batch already exists.'],
                ]
            ], 400);
        }
        // Create the class routine if validation passes
        $classRoutine = ClassRoutine::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Class routine created successfully',
            'data' => $classRoutine->toArray(), // Convert model to array for response
        ], 201);

    } catch (\Exception $e) {
        // Return a JSON response with an error message
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create class routine',
            'errors' => [
                'exception' => ['Failed to create class routine.'],
            ]
        ], 500);
    }
}
 

 
public function show($batch_id = null)
{
    try {
        // Retrieve all class routines, optionally filtered by batch ID
        $query = ClassRoutine::query();

        if ($batch_id) {
            $query->where('batch_id', $batch_id);
        } 

        $classRoutines = $query->orderBy('start_time')->get();

        // Check if any routines were found
        if ($classRoutines->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No class routines found' . ($batch_id ? ' for the batch' : ''),
            ], 404);
        }

        // Initialize an array to hold the timetable data
        $timetable = [];

        // Group routines by batch ID and then by day of the week
        foreach ($classRoutines as $routine) {
            if (!isset($timetable[$routine->batch_id])) {
                $timetable[$routine->batch_id] = [];
            }

            if (!isset($timetable[$routine->batch_id][$routine->day_of_week])) {
                $timetable[$routine->batch_id][$routine->day_of_week] = [];
            }

            $routineKey = $routine->start_time . '-' . $routine->end_time;

            $timetable[$routine->batch_id][$routine->day_of_week][$routineKey] = [
                'subject' => $routine->subject,
                'start_time' => $routine->start_time,
                'end_time' => $routine->end_time,
            ];
        }

        // Return a JSON response with the formatted timetable
        return response()->json([
            'status' => 'success',
            'message' => 'Class routines retrieved successfully',
            'data' => $timetable,
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
        'batch_id' => 'nullable|exists:courses,id',
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

        // Return a JSON response with a success message and 200 status code (OK)
        return response()->json([
            'status' => 'success',
            'message' => 'Class routine deleted successfully',
        ], 200);

    } catch (\Exception $e) {
        // Return a JSON response with an error message and 404 status code (Not Found)
        return response()->json([
            'status' => 'error',
            'message' => 'Class routine not found or could not be deleted',
        ], 404);
    }
}

 
}
 