<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\CoursesEnrollement;
use App\Models\Course;





class AttendanceController extends Controller
{
  public function getStudents($id) 
{
    // Find the course by ID
    $course = Course::find($id);
    if (!$course) {
        return response()->json(['status' => false, 'code' => 404, 'message' => 'Course not found'], 404);
    }

    try {
        // Fetch students enrolled in the specified course along with their attendance data
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

        // Get the current month and year
        $currentMonth = date('m');
        $currentYear = date('Y');

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
                $attendanceDate = \DateTime::createFromFormat('Y-m-d', $attendance->date);
                return $attendance->status === 'absent' && $attendanceDate->format('m') == $currentMonth && $attendanceDate->format('Y') == $currentYear;
            })->count();

            // Prepare student data including number of absent days
            $data[] = [
                'id' => $student->id,
                'name' => $student->name,
                'course-id' => $student->course_id,
                'email' => $student->email,
                'phone' => $student->phone,
                'fname' => $student->fname,
                'fphone' => $student->fphone,
                'total_absent_days' => $totalAbsentDays,
                'absent_days_current_month' => $absentDaysCurrentMonth,
            ];
        }

        // Return success response with students' details including absent days
        return response()->json(['status' => 'success', 'code' => 200, 'data' => $data]);
    } catch (\Exception $e) {
        // Return error response if there's an exception
        Log::error('Failed to fetch students or attendance: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' => 'Failed to fetch students or attendance', 'error' => $e->getMessage()], 500);
    }
}


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


    public function alllist(){
                $courses = Course::where('status', 'active')->orderByDesc('id')->get();
         $coursesList = $courses->map(function ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'fee' => $user->fee,
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
    $courseId = $request->query('course_id');

     $course = Course::find($courseId);
        if (!$course) {
            DB::rollBack(); // Rollback the transaction
            return response()->json(['status' => false, 'code' => 404, 'message' => 'Course not found'], 404);
        }

    try {
        // Retrieve attendance records for the specified date and course
        $attendances = Attendance::where('date', $date)
            ->where('course_id', $courseId) // Assuming 'student' is the relationship method in Attendance model
            ->get();

        // Return success response with attendance data grouped by date
        return response()->json(['success' => true, 'data' => $attendances]);
    } catch (\Exception $e) {
        // Return error response if there's an exception
        Log::error('Failed to fetch attendance: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Failed to fetch attendance', 'error' => $e->getMessage()], 500);
    }
}

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


public function getAllStudentBatchDetails(Request $request)
{
    // Validate request data
    $request->validate([
        'course_id' => 'required|exists:courses,id',
    ], [
        'course_id.required' => 'The course ID field is required.',
        'course_id.exists' => 'The selected course ID is invalid.',
    ]);

    // Retrieve validated data from the request
    $courseId = $request->input('course_id');

    try {
        // Fetch all students enrolled in the course
        $students = DB::table('students')
            ->join('courses_enrollements', 'students.id', '=', 'courses_enrollements.student_id')
            ->join('courses', 'courses_enrollements.course_id', '=', 'courses.id')
            ->where('courses.id', $courseId)
            ->select('students.*', 'courses_enrollements.*', 'courses.name as course_name')
            ->get();

        // Initialize array to store results
        $allStudentsDetails = [];

        foreach ($students as $student) {
            // Fetch attendance records for each student and course
            $attendances = DB::table('attendances')
                ->where('student_id', $student->id)
                ->where('course_id', $courseId)
                ->get();

            // Count total days absent
            $daysAbsent = $attendances->where('status', 'absent')->count();

            // Count days absent for the current month
            $currentMonth = date('m');
            $currentYear = date('Y');
            $daysAbsentCurrentMonth = $attendances->filter(function ($attendance) use ($currentMonth, $currentYear) {
                $attendanceDate = \DateTime::createFromFormat('Y-m-d', $attendance->date);
                return $attendance->status === 'absent' && $attendanceDate->format('m') == $currentMonth && $attendanceDate->format('Y') == $currentYear;
            })->count();

            // Build student details array
            $studentDetails = [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'phone' => $student->phone,
                'course_name' => $student->course_name,
                'days_absent' => $daysAbsent,
                'days_absent_current_month' => $daysAbsentCurrentMonth,
            ];

            // Push student details to the result array
            $allStudentsDetails[] = $studentDetails;
        }

        // Return success response with all student batch details and attendance
        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $allStudentsDetails,
        ]);
    } catch (\Exception $e) {
        // Return error response if there's an exception
        Log::error('Failed to fetch all students batch details: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Failed to fetch all students batch details', 'error' => $e->getMessage()], 500);
    }
}



}