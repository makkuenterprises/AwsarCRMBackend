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
        'batch_id' => 'nullable|exists:courses,id',
        'day_of_week' => 'required|in:mon,tue,wed,thu,fri,sat',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
    ]);

    try {
        // Check if there's already a routine with the same day, time, batch, and subject
        // Exclude the current routine ID if editing an existing routine
        $existingRoutine = ClassRoutine::where('day_of_week', $validatedData['day_of_week'])
                                        ->where('batch_id', $validatedData['batch_id'])
                                        ->where('subject', $validatedData['subject'])
                                        ->where(function ($query) use ($validatedData) {
                                            $query->where(function ($q) use ($validatedData) {
                                                $q->whereBetween('start_time', [$validatedData['start_time'], $validatedData['end_time']])
                                                  ->orWhereBetween('end_time', [$validatedData['start_time'], $validatedData['end_time']]);
                                            })
                                            ->orWhere(function ($q) use ($validatedData) {
                                                $q->where('start_time', '<', $validatedData['start_time'])
                                                  ->where('end_time', '>', $validatedData['end_time']);
                                            });
                                        })
                                        ->exists();

        if ($existingRoutine) {
            return response()->json([
                'status' => 'error',
                'message' => 'Another class routine already exists for the same day, time, subject, and batch.',
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
        ], 500);
    }
}

 
public function show($batch_id)
{
    try {
        // Retrieve all class routines for the given batch ID
        $classRoutines = ClassRoutine::where('batch_id', $batch_id)
                                     ->orderBy('start_time')
                                     ->get();

        // Check if any routines were found
        if ($classRoutines->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No class routines found for the batch',
            ], 404);
        }

        // Initialize an array to hold the timetable data
        $timetable = [];

        // Days of the week in order
        $daysOfWeek = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

        // Initialize timetable with empty arrays for each time slot
        foreach ($daysOfWeek as $day) {
            $timetable[$day] = [
                '9:00 AM' => '',
                '10:00 AM' => '',
                '11:00 AM' => '',
                '12:00 PM' => 'Lunch Break',
                '1:00 PM' => '',
                '2:00 PM' => '',
                '3:00 PM' => '',
            ];
        }

        // Populate timetable with class routines
        foreach ($classRoutines as $routine) {
            $timetable[$routine->day_of_week][$routine->start_time] = $routine->subject;
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
 