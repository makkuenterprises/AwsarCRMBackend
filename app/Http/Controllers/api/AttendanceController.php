<?php

namespace App\Http\Controllers\api;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\CoursesEnrollement;

class AttendanceController extends Controller
{
    public function getStudents($id)
    {
        $course = CoursesEnrollement::where('course_id',$id)->get();

        $students = DB::table('students')
            ->join('courses_enrollements', 'courses_enrollements.student_id', '=', 'students.id')
            ->where('courses_enrollements.course_id', $id)
            ->select('students.*', 'courses_enrollements.course_id')
            ->get();

            foreach ($students as $student) {
            $data[] = [
            'id' => $student->id,
            'name' => $student->name,
            'course-id' => $student->course_id,
            ];
        }
        return response()->json(['data' => $data]);
    }

    public function submitAttendance(Request $request)
    {
        // Validate request data
        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:present,absent',
        ]);

        // Retrieve attendance data from the request
        $date = $request->input('date');
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
}