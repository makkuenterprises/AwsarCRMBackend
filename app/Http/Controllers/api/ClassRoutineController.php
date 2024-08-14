<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ClassRoutine;
use App\Models\Teacher;
use Carbon\Carbon;
use App\Models\Course;
use Illuminate\Http\Request;

class ClassRoutineController extends Controller
{



public function store(Request $request)
{
    try {
        $validatedData = $request->validate([
            'subject' => 'required|string',
            'batch_id' => 'required|exists:courses,id',
            'day_of_week' => 'required|in:mon,tue,wed,thu,fri,sat',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
        ]);

         if (!preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $validatedData['start_time']) ||
            !preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $validatedData['end_time'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid time format. Please use 24-hour format (HH:MM:SS).',
            ], 400);
        }


        // Overlapping time validation
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

        // Create the class routine
        $classRoutine = ClassRoutine::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Class routine created successfully',
            'data' => $classRoutine->toArray(),
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create class routine',
            'errors' => [
                'exception' => ['Failed to create class routine.'],
            ]
        ], 500);
    }
}


public function createTimeSlot(Request $request)
{
    //  dd($request->all());
    try {
        // Validate the request data
        $validatedData = $request->validate([
            'batch_id' => 'required|exists:courses,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'days' => 'required|array',
            'days.*' => 'required|in:mon,tue,wed,thu,fri,sat',
        ]);

        // Prepare to store time slots
        $createdTimeSlots = [];

        // Loop through each provided day
        foreach ($validatedData['days'] as $dayOfWeek) {
            // Check for overlapping time slots
            $existingTimeSlot = ClassRoutine::where('day_of_week', $dayOfWeek)
                ->where('batch_id', $validatedData['batch_id'])
                ->where(function ($query) use ($validatedData) {
                    $query->where(function ($q) use ($validatedData) {
                        $q->where('start_time', '<=', $validatedData['start_time'])
                            ->where('end_time', '>', $validatedData['start_time']);
                    })->orWhere(function ($q) use ($validatedData) {
                        $q->where('start_time', '<', $validatedData['end_time'])
                            ->where('end_time', '>=', $validatedData['end_time']);
                    });
                })
                ->exists();

            if ($existingTimeSlot) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'A time slot with overlapping time already exists for ' . ucfirst($dayOfWeek) . ' and batch.',
                ], 400);
            }

            // Create time slot
            $timeSlot = ClassRoutine::create([
                'batch_id' => $validatedData['batch_id'],
                'day_of_week' => $dayOfWeek,
                'start_time' => $validatedData['start_time'],
                'end_time' => $validatedData['end_time'],
                'subject' => null, // Initially, no subject is assigned
            ]);

            $createdTimeSlots[] = $timeSlot;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Time slots created successfully',
            'data' => $createdTimeSlots,
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $e->errors(), // Return validation errors
        ], 422); // HTTP status code for Unprocessable Entity
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create time slots',
            'errors' => [
                'exception' => [$e->getMessage()],
            ],
        ], 500);
    }
}
 public function updateTimeSlot(Request $request)
{
    try {
        // Validate the request data
        $validatedData = $request->validate([
            'batch_id' => 'required|exists:courses,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'days' => 'required|array',
            'days.*' => 'required|in:mon,tue,wed,thu,fri,sat',
            'time_slot_id' => 'nullable|exists:class_routines,id', // Optional for update
        ]);

        // Prepare to store time slots
        $createdTimeSlots = [];

        // Loop through each provided day
        foreach ($validatedData['days'] as $dayOfWeek) {
            if ($validatedData['time_slot_id']) {
                // Update existing time slot if time_slot_id is provided
                $timeSlot = ClassRoutine::findOrFail($validatedData['time_slot_id']);
                
                // Update time slot details
                $timeSlot->batch_id = $validatedData['batch_id'];
                $timeSlot->day_of_week = $dayOfWeek;
                $timeSlot->start_time = $validatedData['start_time'];
                $timeSlot->end_time = $validatedData['end_time'];
                $timeSlot->save();
            } else {
                // Check for overlapping time slots
                $existingTimeSlot = ClassRoutine::where('day_of_week', $dayOfWeek)
                    ->where('batch_id', $validatedData['batch_id'])
                    ->where(function ($query) use ($validatedData) {
                        $query->where(function ($q) use ($validatedData) {
                            $q->where('start_time', '<=', $validatedData['start_time'])
                                ->where('end_time', '>', $validatedData['start_time']);
                        })->orWhere(function ($q) use ($validatedData) {
                            $q->where('start_time', '<', $validatedData['end_time'])
                                ->where('end_time', '>=', $validatedData['end_time']);
                        });
                    })
                    ->exists();

                if ($existingTimeSlot) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'A time slot with overlapping time already exists for ' . ucfirst($dayOfWeek) . ' and batch.',
                    ], 400);
                }

                // Create time slot
                $timeSlot = ClassRoutine::create([
                    'batch_id' => $validatedData['batch_id'],
                    'day_of_week' => $dayOfWeek,
                    'start_time' => $validatedData['start_time'],
                    'end_time' => $validatedData['end_time'],
                    'subject' => null, // Initially, no subject is assigned
                ]);
            }

            $createdTimeSlots[] = $timeSlot;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Time slots ' . ($validatedData['time_slot_id'] ? 'updated' : 'created') . ' successfully',
            'data' => $createdTimeSlots,
        ], $validatedData['time_slot_id'] ? 200 : 201); // Use 200 for update, 201 for create

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $e->errors(), // Return validation errors
        ], 422); // HTTP status code for Unprocessable Entity
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to ' . ($validatedData['time_slot_id'] ? 'update' : 'create') . ' time slots',
            'errors' => [
                'exception' => [$e->getMessage()],
            ],
        ], 500);
    }
}
public function deleteTimeSlotsByBatchId($batch_id)
{
    try {
        // Find all time slots with the given batch_id
        $timeSlots = ClassRoutine::where('batch_id', $batch_id)->get();

        // If no time slots found, return a not found response
        if ($timeSlots->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No time slots found for batch ID ' . $batch_id,
            ], 404);
        }

        // Delete each time slot
        foreach ($timeSlots as $timeSlot) {
            $timeSlot->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'All time slots deleted successfully for batch ID ' . $batch_id,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to delete time slots for batch ID ' . $batch_id,
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function assignSubject(Request $request)
{

    try {

         $validatedData = $request->validate([
        'time_slot_id' => 'required|exists:class_routines,id',
        'subject' => 'required|string',
    ]);
        $timeSlot = ClassRoutine::find($validatedData['time_slot_id']);
        
        if ($timeSlot->subject) {
            return response()->json([
                'status' => 'error',
                'message' => 'This time slot already has a subject assigned.',
            ], 400);
        }

        $timeSlot->subject = $validatedData['subject'];
        $timeSlot->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Subject assigned to time slot successfully',
            'data' => $timeSlot,
        ], 200);

    }catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $e->errors(), // Return validation errors
        ], 422); // HTTP status code for Unprocessable Entity
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create time slots',
            'errors' => [
                'exception' => [$e->getMessage()],
            ],
        ], 500);
    }
}

public function assignSubjectUpdate(Request $request)
{
    try {
        // Validate the request data
        $validatedData = $request->validate([
            'time_slot_id' => 'required|exists:class_routines,id',
            'subject' => 'required|string',
        ]);

        // Find the time slot by ID
        $timeSlot = ClassRoutine::findOrFail($validatedData['time_slot_id']);
        
        // Update the subject field regardless of its current value
        $timeSlot->subject = $validatedData['subject'];
        $timeSlot->save();

        // Return a success response with updated time slot details
        return response()->json([
            'status' => 'success',
            'message' => 'Subject assigned to time slot successfully',
            'data' => $timeSlot,
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Return validation errors if validation fails
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        // Return an error response for any other exceptions
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to assign subject to time slot',
            'errors' => [
                'exception' => [$e->getMessage()],
            ],
        ], 500);
    }
}

// public function showClassRoutine($batch_id)
// {
//     try {
//         // Fetch all class routines for the specified batch_id, ordered by start_time
//         $classRoutines = ClassRoutine::where('batch_id', $batch_id)
//                                      ->orderBy('start_time')
//                                      ->get();

//         // Prepare the data in the desired format
//         $routineData = [];

//         // Loop through each class routine
//         foreach ($classRoutines as $routine) {
//             $dayOfWeek = ucfirst($routine->day_of_week);

//             // Initialize the day if not set
//             if (!isset($routineData[$dayOfWeek])) {
//                 $routineData[$dayOfWeek] = [];
//             }

//             // Format the time range
//             $timeRange = $routine->start_time . ' - ' . $routine->end_time;

//             // Add details including id, subject to the routine day
//             $routineData[$dayOfWeek][] = [
//                 'id' => $routine->id,
//                 'time_range' => $timeRange,
//                 'subject' => $routine->subject,
//             ];
//         }

//         // Return the formatted routine data
//         return response()->json([
//             'status' => 'success',
//             'data' => $routineData,
//         ], 200);

//     } catch (\Exception $e) {
//         // Return a JSON response with an error message if an exception occurs
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Failed to fetch class routines for batch ' . $batch_id,
//             'error' => $e->getMessage(),
//         ], 500);
//     }
// }
public function showClassRoutine($batch_id)
{
    try { 
         $staff = Auth::guard('staff')->user();
         $student = Auth::guard('student')->user();
         $teacher = Auth::guard('teacher')->user();
        $admin = Auth::guard('admin')->user();
         if ($student || $admin || $staff || $teacher) {
        // Fetch all class routines for the specified batch_id, ordered by start_time
        $classRoutines = ClassRoutine::where('batch_id', $batch_id)
                                     ->orderBy('start_time')
                                     ->get();

        // Prepare the data in the desired format
        $routineData = [];

        // Loop through each class routine
        foreach ($classRoutines as $routine) {
            $dayOfWeek = ucfirst($routine->day_of_week);

            // Initialize the day if not set
            if (!isset($routineData[$dayOfWeek])) {
                $routineData[$dayOfWeek] = [];
            }

            // Format the time range
            $timeRange = $routine->start_time . ' - ' . $routine->end_time;

            // Add details including id, subject to the routine day
            $routineData[$dayOfWeek][] = [
                'id' => $routine->id,
                'time_range' => $timeRange,
                'subject' => $routine->subject,
            ];
        }

        // If no routines are found, ensure that 'data' is an empty object
        if (empty($routineData)) {
            $routineData = new \stdClass(); // Create an empty object
        }

        // Return the formatted routine data
        return response()->json([
            'status' => 'success',
            'data' => $routineData,
        ], 200);}else{
             return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 401);
        }
        

    } catch (\Exception $e) {
        // Return a JSON response with an error message if an exception occurs
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch class routines for batch ' . $batch_id,
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function showClassTimeRoutine($batch_id)
{
    try {
        // Fetch all class routines for the specified batch_id, ordered by start_time
        $classRoutines = ClassRoutine::where('batch_id', $batch_id)
                                     ->orderBy('start_time')
                                     ->get();

        // Prepare the data in the desired format
        $timeRanges = [];

        // Loop through each class routine
        foreach ($classRoutines as $routine) {
            // Format the time range
            $timeRange = $routine->start_time . ' - ' . $routine->end_time;

            // Add time range to the list if not already present
            if (!in_array($timeRange, $timeRanges)) {
                $timeRanges[] = $timeRange;
            }
        }

        // Return the formatted routine data
        return response()->json([
            'status' => 'success',
            'data' => $timeRanges,
        ], 200);

    } catch (\Exception $e) {
        // Return a JSON response with an error message if an exception occurs
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch class routines for batch ' . $batch_id,
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function showClassTimeRoutinee($batch_id)
{
    try {
        // Fetch all class routines for the specified batch_id, ordered by start_time
        $classRoutines = ClassRoutine::where('batch_id', $batch_id)
                                     ->orderBy('start_time')
                                     ->get();

        // Prepare the data in the desired format
        $routines = [];

        // Loop through each class routine
        foreach ($classRoutines as $routine) {
            // Format the time range
            $timeRange = $routine->start_time . ' - ' . $routine->end_time;

            // Add time range and id to the list if not already present
            $routines[] = [
                'id' => $routine->id,
                'time_range' => $timeRange,
            ];
        }

        // Return the formatted routine data
        return response()->json([
            'status' => 'success',
            'data' => $routines,
        ], 200);

    } catch (\Exception $e) {
        // Return a JSON response with an error message if an exception occurs
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch class routines for batch ' . $batch_id,
            'error' => $e->getMessage(),
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


public function getTodayClasses($teacherId)
    {
        try {
            // Get today's day of the week in short format (e.g., mon, tue)
            $todayDay = strtolower(Carbon::now()->format('D'));

            // Get the teacher with their courses
            $teacher = Teacher::with('courses')->findOrFail($teacherId);

            // Get the course IDs for the teacher
            $courseIds = $teacher->courses->pluck('id');

            // Fetch the class routines for today for the teacher's courses with course names
            $classRoutines = ClassRoutine::whereIn('batch_id', $courseIds)
            ->where('day_of_week', $todayDay)
            ->join('courses', 'class_routines.batch_id', '=', 'courses.id')
            ->select('class_routines.*', 'courses.name as course_name')
            ->get();

            // Return the class routines
            return response()->json([
                'status' => 'success',
                'message' => 'Class routines retrieved successfully',
                'data' => $classRoutines
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve class routines',
                'errors' => [
                    'exception' => [$e->getMessage()]
                ]
            ], 500);
        }
    }
 
}
 