<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\CoursesEnrollement;
use Illuminate\Support\Carbon;
use App\Models\Course;
use Illuminate\Support\Facades\Validator;





class AttendanceController extends Controller 
{

// all Course Lists===================================================================

    public function alllist(){
        $courses = Course::where('status', 'active')->orderByDesc('id')->get();
        $coursesList = $courses->map(function ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            // 'fee' => $user->fee,
            'startDate' => $user->startDate,
            'endDate' => $user->endDate,
            'modeType' => $user->modeType,
            'summary' => $user->summary,
            'Course_id' => $user->Course_id,
            'image' => $user->image ? url('/Courses/' . $user->image) : null, // Assuming $user->imagePath contains the relative path
           
        ];
    });
        //  return response()->json(['status'=>true,'code'=>200,'data'=>$courses]);
        return response()->json([
        'status' => true,
        'code' => 200,
        'data' => $coursesList
    ]);

    } 
    
// Get Student By Course Id =================================================================================== 


public function getStudents(Request $request, $id) 
{
    // Validate the request to ensure 'current_date' is present and in the correct format
    $validated = $request->validate([
        'current_date' => 'required|date_format:d-m-y',
    ]);

    // Find the course by ID
    $course = Course::find($id);
    if (!$course) {
        return response()->json(['status' => false, 'code' => 404, 'message' => 'Course not found'], 404);
    }

    try {
        // Fetch students enrolled in the specified course
        $students = DB::table('students')
            ->join('courses_enrollements', 'courses_enrollements.student_id', '=', 'students.id')
            ->where('courses_enrollements.course_id', $id)
            ->select('students.*', 'courses_enrollements.course_id')
            ->get();

        // Check if no students are enrolled in the course
        if ($students->isEmpty()) {
            return response()->json(['status' => false, 'code' => 404, 'message' => 'No students enrolled in the specified course'], 404);
        }

        // Initialize data array to store students' details
        $data = [];

        // Get the current date from the validated request
        $currentDate = Carbon::createFromFormat('d-m-y', $validated['current_date'])->format('Y-m-d');
        $currentMonth = Carbon::createFromFormat('d-m-y', $validated['current_date'])->format('m');
        $currentYear = Carbon::createFromFormat('d-m-y', $validated['current_date'])->format('Y');

        // Iterate through each student to fetch attendance details
        foreach ($students as $student) {
            // Fetch attendance records for the student in the current course
            $attendances = DB::table('attendances')
                ->where('student_id', $student->id)
                ->where('course_id', $id)
                ->get();

            // Count the number of absent days overall
            $totalAbsentDays = $attendances->where('status', 'absent')->count();

            // Count the number of absent days for the current month
            $absentDaysCurrentMonth = $attendances->filter(function ($attendance) use ($currentMonth, $currentYear) {
                $attendanceDate = Carbon::createFromFormat('Y-m-d', $attendance->date);
                return $attendance->status === 'absent' && $attendanceDate->format('m') == $currentMonth && $attendanceDate->format('Y') == $currentYear;
            })->count();

            // Get today's attendance status
            $todayAttendance = $attendances->firstWhere('date', $currentDate);
            $todayStatus = $todayAttendance ? $todayAttendance->status : 'no record';

            // Prepare student data including number of absent days and today's status
            $data[] = [
                'id' => $student->id,
                'name' => $student->name,
                'course_id' => $student->course_id,
                'email' => $student->email,
                'phone' => $student->phone,
                'fname' => $student->fname,
                'fphone' => $student->fphone,
                'total_absent_days' => $totalAbsentDays,
                'absent_days_current_month' => $absentDaysCurrentMonth,
                'today_status' => $todayStatus,
            ];
        }

        // Return success response with students' data
        return response()->json(['status' => true, 'data' => $data], 200);
    } catch (\Exception $e) {
        // Return error response if there's an exception
        Log::error('Failed to fetch students: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' => 'Failed to fetch students', 'error' => $e->getMessage()], 500);
    }
}


// // All Student Attendance ===================================================================================================
// public function getAllStudentBatchDetails(Request $request)
// {
//     // Validate request data
//     $request->validate([
//         'course_id' => 'required|exists:courses,id',
//     ], [
//         'course_id.required' => 'The course ID field is required.',
//         'course_id.exists' => 'The selected course ID is invalid.',
//     ]);

//     // Retrieve validated data from the request
//     $courseId = $request->input('course_id');

//     try {
//         // Fetch all students enrolled in the course
//         $students = DB::table('students')
//             ->join('courses_enrollements', 'students.id', '=', 'courses_enrollements.student_id')
//             ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
//             ->where('courses.id', $courseId)
//             ->select('students.*', 'courses_enrollements.*', 'courses.name as course_name')
//             ->get();

//         // Initialize array to store results
//         $allStudentsDetails = [];

//         foreach ($students as $student) {
//             // Fetch attendance records for each student and course
//             $attendances = DB::table('attendances')
//                 ->where('student_id', $student->id)
//                 ->where('course_id', $courseId)
//                 ->get();

//             // Count total days absent
//             $daysAbsent = $attendances->where('status', 'absent')->count();

//             // Count days absent for the current month
//             $currentMonth = date('m');
//             $currentYear = date('Y');
//             $daysAbsentCurrentMonth = $attendances->filter(function ($attendance) use ($currentMonth, $currentYear) {
//                 $attendanceDate = \DateTime::createFromFormat('Y-m-d', $attendance->date);
//                 return $attendance->status === 'absent' && $attendanceDate->format('m') == $currentMonth && $attendanceDate->format('Y') == $currentYear;
//             })->count();

//             // Build student details array
//             $studentDetails = [
//                 'student_id' => $student->id,
//                 'student_name' => $student->name,
//                 'phone' => $student->phone,
//                 'course_name' => $student->course_name,
//                 'days_absent' => $daysAbsent,
//                 'days_absent_current_month' => $daysAbsentCurrentMonth,
//             ];

//             // Push student details to the result array
//             $allStudentsDetails[] = $studentDetails;
//         }

//         // Return success response with all student batch details and attendance
//         return response()->json([
//             'code' => 200,
//             'success' => true,
//             'data' => $allStudentsDetails,
//         ]);
//     } catch (\Exception $e) {
//         // Return error response if there's an exception
//         Log::error('Failed to fetch all students batch details: ' . $e->getMessage());
//         return response()->json(['success' => false, 'message' => 'Failed to fetch all students batch details', 'error' => $e->getMessage()], 500);
//     }
// }


// Get Attendance By Date=================================================================================

public function getAttendanceByDate(Request $request)
{
    // Validate request query parameters
    $request->validate([
        'date' => [
            'required',
            function ($attribute, $value, $fail) {
                $d = \DateTime::createFromFormat('d/m/Y', $value);
                if (!$d || $d->format('d/m/Y') !== $value) {
                    $fail('The ' . $attribute . ' does not match the format dd/mm/yyyy.');
                }
            }
        ],
        'course_id' => 'required|exists:courses,id', // Validate course_id
    ]);

    // Retrieve validated data from the query string
    $date = \DateTime::createFromFormat('d/m/Y', $request->input('date'))->format('Y-m-d');
    $courseId = $request->input('course_id');

    // Find the course by ID
    $course = Course::find($courseId);
    if (!$course) {
        return response()->json(['status' => false, 'code' => 404, 'message' => 'Course not found'], 404);
    }

    try {
        // Get the current date, month, and year
        $currentDate = date('Y-m-d');
        $currentMonth = date('m');
        $currentYear = date('Y');

        // Retrieve attendance records for the specified date and course
        $attendances = DB::table('attendances')
            ->where('attendances.date', $date)
            ->where('attendances.course_id', $courseId)
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->select('attendances.*', 'students.name', 'students.email', 'students.phone', 'students.fname', 'students.fphone')
            ->get();

        // Check if no attendance records are found
        if ($attendances->isEmpty()) {
            return response()->json(['status' => false, 'code' => 404, 'message' => 'No attendance records found for the specified date and course'], 404);
        } 

        // Initialize data array to store students' details and attendance data
        $data = [];

        // Iterate through each attendance record to fetch additional details
        foreach ($attendances as $attendance) {
            // Fetch all attendance records for the student in the current course
            $studentAttendances = DB::table('attendances')
                ->where('student_id', $attendance->student_id)
                ->where('course_id', $courseId)
                ->get();

            // Count the number of absent days overall
            $totalAbsentDays = $studentAttendances->where('status', 'absent')->count();

            // Count the number of absent days for the current month
            $absentDaysCurrentMonth = $studentAttendances->filter(function ($attendance) use ($currentMonth, $currentYear) {
                $attendanceDate = \DateTime::createFromFormat('Y-m-d', $attendance->date);
                return $attendance->status === 'absent' && $attendanceDate->format('m') == $currentMonth && $attendanceDate->format('Y') == $currentYear;
            })->count();

            // Get today's attendance status
            $todayAttendance = $studentAttendances->firstWhere('date', $currentDate);
            $todayStatus = $todayAttendance ? $todayAttendance->status : 'no record';

            // Prepare student data including number of absent days and today's status
            $data[] = [
                'id' => $attendance->student_id,
                'name' => $attendance->name,
                'course_id' => $attendance->course_id,
                'email' => $attendance->email,
                'phone' => $attendance->phone,
                'fname' => $attendance->fname,
                'fphone' => $attendance->fphone,
                'total_absent_days' => $totalAbsentDays,
                'absent_days_current_month' => $absentDaysCurrentMonth,
                'today_status' => $todayStatus,
            ];
        }

        // Return success response with attendance data
        return response()->json(['success' => true, 'data' => $data]);
    } catch (\Exception $e) {
        // Return error response if there's an exception
        Log::error('Failed to fetch attendance: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Failed to fetch attendance', 'error' => $e->getMessage()], 500);
    }
}



// Create Attendance =======================================================================================

public function create(Request $request)
{
      // Custom error messages
    $messages = [
        'date.required' => 'The date field is required.',
        'course_id.required' => 'The course ID field is required.',
        'course_id.exists' => 'The selected course ID is invalid.',
        'attendance.required' => 'The attendance field is required.',
        'attendance.array' => 'The attendance must be an array.',
        'attendance.*.student_id.required' => 'The student ID field is required.',
        'attendance.*.student_id.exists' => 'The selected student ID is invalid.',
        'attendance.*.status.required' => 'The status field is required.',
        'attendance.*.status.in' => 'The status must be either present or absent.',
    ];

    // Custom validation logic
    try {
        $request->validate([
            'date' => [
                'required',
                function ($attribute, $value, $fail) {
                    $d = \DateTime::createFromFormat('d/m/Y', $value);
                    if (!$d || $d->format('d/m/Y') !== $value) {
                        $fail('The ' . $attribute . ' does not match the format dd/mm/yyyy.');
                    }
                }
            ],
            'course_id' => 'required|exists:courses,id',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:present,absent',
        ], $messages);
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Return validation error response
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    }

    // Retrieve validated data from the request
    $date = \DateTime::createFromFormat('d/m/Y', $request->input('date'))->format('Y-m-d');
    $courseId = $request->input('course_id'); // Retrieve course_id
    $attendanceData = $request->input('attendance');

    try {
        // Start a database transaction
        DB::beginTransaction();

        // Save attendance records
        foreach ($attendanceData as $data) {
            Attendance::create([
                'student_id' => $data['student_id'],
                'date' => $date,
                'status' => $data['status'],
                'course_id' => $courseId, // Include course_id
            ]);
        }

        // Commit the transaction
        DB::commit();

        // Return success response
        return response()->json(['success' => true, 'message' => 'Attendance submitted successfully']);
    } catch (\Exception $e) {
        // Rollback the transaction in case of an error
        DB::rollback();

        // Return error response
        Log::error('Failed to submit attendance: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Failed to submit attendance', 'error' => $e->getMessage()], 500);
    }
}

// all student list for attendance============================================================================

public function getStudentsEnrolledInCourse(Request $request, $courseId)
{
    // Validate the course ID
    $validator = Validator::make(['course_id' => $courseId], [
        'course_id' => 'required|exists:courses,id',
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'code' => 400,
            'errors' => $validator->errors()
        ], 400);
    }

    try {
        // Get the list of students enrolled in the specific course
        $students = DB::table('courses_enrollements')
            ->join('students', 'courses_enrollements.student_id', '=', 'students.id')
            ->where('courses_enrollements.course_id', $courseId)
            ->select('students.id', 'students.name')
            ->get();

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $students
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'Failed to retrieve students enrolled in the course',
            'error' => $e->getMessage()
        ], 500);
    }
}


// course lists for specific student =================================================
public function getCoursesByStudent($studentId)
{
    // Find the student by ID
    $student = Student::find($studentId);
    if (!$student) {
        return response()->json(['status' => false, 'code' => 404, 'message' => 'Student not found'], 404);
    }

    try {
        // Fetch courses the student is enrolled in
        $courses = DB::table('courses_enrollements')
            ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
            ->where('courses_enrollements.student_id', $studentId)
            ->select('courses.*', 'courses_enrollements.enrollment_date')
            ->get();

        // Check if the student is not enrolled in any course
        if ($courses->isEmpty()) {
            return response()->json(['status' => false, 'code' => 404, 'message' => 'No courses found for the specified student'], 404);
        }

        // Initialize data array to store course details
        $data = [];

        // Iterate through each course to prepare the data array
        foreach ($courses as $course) {
            $data[] = [
                'course_id' => $course->id,
                'course_name' => $course->name,
                'course_description' => $course->summary,
                'enrollment_date' => $course->enrollment_date,
            ];
        }

        // Return success response with course data
        return response()->json(['status' => true, 'data' => $data]);
    } catch (\Exception $e) {
        // Return error response if there's an exception
        Log::error('Failed to fetch courses: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' => 'Failed to fetch courses', 'error' => $e->getMessage()], 500);
    }
}

// specific date attendance for specific batch and student ==================================

public function getAttendanceByDateStudent(Request $request)
{
    // Validate request body parameters
    $request->validate([
        'date' => [
            'required',
            function ($attribute, $value, $fail) {
                $d = \DateTime::createFromFormat('d/m/Y', $value);
                if (!$d || $d->format('d/m/Y') !== $value) {
                    $fail('The ' . $attribute . ' does not match the format dd/mm/yyyy.');
                }
            }
        ],
        'course_id' => 'required|exists:courses,id', // Validate course_id
        'student_id' => 'required|exists:students,id' // Validate student_id
    ]);

    // Retrieve validated data from the request body
    $date = \DateTime::createFromFormat('d/m/Y', $request->input('date'))->format('Y-m-d');
    $courseId = $request->input('course_id');
    $studentId = $request->input('student_id');

    try {
        // Retrieve attendance record with course name and student name
        $attendance = Attendance::where('attendances.date', $date)
            ->where('attendances.course_id', $courseId)
            ->where('attendances.student_id', $studentId)
            ->join('courses', 'attendances.course_id', '=', 'courses.id')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->select('attendances.*', 'courses.name as course_name', 'students.name as student_name')
            ->first();

        // Check if attendance record exists
        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'Attendance record not found'], 404);
        }

        // Return success response with attendance data
        return response()->json(['success' => true, 'data' => $attendance]);
    } catch (\Exception $e) {
        // Return error response if there's an exception
        Log::error('Failed to fetch attendance: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Failed to fetch attendance', 'error' => $e->getMessage()], 500);
    }
}


// for specific Student and Batch========================================================================================




public function getStudentBatchDetails(Request $request)
{
    // Validate request data
    $request->validate([
        'student_id' => 'required|exists:students,id',
        'course_id' => 'required|exists:courses,id',
    ], [
        'student_id.required' => 'The student ID field is required.',
        'student_id.exists' => 'The selected student ID is invalid.',
        'course_id.required' => 'The course ID field is required.',
        'course_id.exists' => 'The selected course ID is invalid.',
    ]);

    // Retrieve validated data from the request
    $studentId = $request->input('student_id');
    $courseId = $request->input('course_id');

    try {
        // Fetch the student details for the specific batch using join
       // Fetch the student details for the specific batch using join
$studentBatchDetails = DB::table('students')
    ->join('courses_enrollements', 'students.id', '=', 'courses_enrollements.student_id')
    ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
    ->where('students.id', $studentId)
    ->where('courses.id', $courseId)
    ->select('students.*', 'students.name as student_name', 'courses_enrollements.*', 'courses.*')
    ->first();



        if (!$studentBatchDetails) {
            return response()->json(['success' => false, 'message' => 'Student not found in the specified batch'], 404);
        }

        // Fetch the attendance records for the student and course
        $attendances = DB::table('attendances')
            ->where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->get();

        // Count the number of days the student was absent
        $daysAbsent = $attendances->where('status', 'absent')->count();

        // Get the current month
        $currentMonth = date('m');
        $currentYear = date('Y');

        // Count the number of days the student was absent for the current month
        $daysAbsentCurrentMonth = $attendances->filter(function ($attendance) use ($currentMonth, $currentYear) {
            $attendanceDate = \DateTime::createFromFormat('Y-m-d', $attendance->date);
            return $attendance->status === 'absent' && $attendanceDate->format('m') == $currentMonth && $attendanceDate->format('Y') == $currentYear;
        })->count();

        // Return success response with student batch details and days absent
         // Return success response with specific student batch details and days absent
        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => [
                'student' => [
                    'student_name' => $studentBatchDetails->student_name,
                    'phone' => $studentBatchDetails->phone,
                    'course_name' => $studentBatchDetails->name,
                    'father_name' => $studentBatchDetails->fname,
                    'father_phone' => $studentBatchDetails->fphone,
                ],
                'days_absent' => $daysAbsent,
                'days_absent_current_month' => $daysAbsentCurrentMonth
            ]
        ]);
    } catch (\Exception $e) {
        // Return error response if there's an exception
        Log::error('Failed to fetch student batch details: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Failed to fetch student batch details', 'error' => $e->getMessage()], 500);
    }
}


// in between Date attendance details================================================
public function getAttendanceBetweenDates(Request $request)
{
    // Validate the request inputs
    $validator = Validator::make($request->all(), [
        'start_date' => 'required|date_format:d/m/Y',
        'end_date' => 'required|date_format:d/m/Y|after_or_equal:start_date',
        'course_id' => 'required|exists:courses,id',
        'student_id' => 'required|exists:students,id',  // Assuming student_id should exist in students table
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        return response()->json([
            'status' => false, 
            'code' => 400,
            'errors' => $validator->errors()
        ], 400);
    }

    try {
        // Convert dates to 'Y-m-d' format
        $startDate = \DateTime::createFromFormat('d/m/Y', $request->input('start_date'))->format('Y-m-d');
        $endDate = \DateTime::createFromFormat('d/m/Y', $request->input('end_date'))->format('Y-m-d');

        $studentId = $request->input('student_id');
        $courseId = $request->input('course_id');

        // Get the attendance records between the specified dates for the given student
        $attendanceRecords = DB::table('attendances')
            ->where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $attendanceRecords
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'Failed to retrieve attendance records',
            'error' => $e->getMessage()
        ], 500);
    }
}




}