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
        // $course = CoursesEnrollement::where('course_id',$id)->get();
        $course = Course::find($id);
        if (!$course) {
            DB::rollBack(); // Rollback the transaction
            return response()->json(['status' => false, 'code' => 404, 'message' => 'Course not found'], 404);
        }
    

        $students = DB::table('students')
            ->join('courses_enrollements', 'courses_enrollements.student_id', '=', 'students.id')
            ->where('courses_enrollements.course_id', $id)
            ->select('students.*', 'courses_enrollements.course_id')
            ->get();
            $data = []; // Initialize the $data array
            foreach ($students as $student) {
            $data[] = [
            'id' => $student->id,
            'name' => $student->name,
            'course-id' => $student->course_id,
            ];
        }
        return response()->json(['status'=>'success','code'=>200,'data' => $data]);
    }

public function create(Request $request)
{
    // Validate request data 
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
        'attendance' => 'required|array',
        'attendance.*.student_id' => 'required|exists:students,id',
        'attendance.*.status' => 'required|in:present,absent',
    ]);

    // Retrieve attendance data from the request
  $date = \DateTime::createFromFormat('d/m/Y', $request->input('date'))->format('Y-m-d');
    $courseId = $request->input('course_id'); // Retrieve course_id
    $attendanceData = $request->input('attendance');

     $course = Course::find($courseId);
        if (!$course) {
            DB::rollBack(); // Rollback the transaction
            return response()->json(['status' => false, 'code' => 404, 'message' => 'Course not found'], 404);
        }
    

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
            $data = CoursesEnrollement::get();
             return response()->json(['data' => $data]);
    }


 public function getAttendanceByDate(Request $request)
{
    // Validate request query parameters
    $request->validate([
        'date' => 'required|date',
        'course_id' => 'required|exists:courses,id', // Validate course_id
    ]);

    // Retrieve validated data from the query string
    $date = $request->query('date');
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



}