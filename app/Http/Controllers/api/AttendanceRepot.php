<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceRepot extends Controller
{
public function generateAttendanceReport(Request $request)
{
    // Validate the input data
    $validatedData = $request->validate([
        'start_date' => 'required|date_format:d/m/Y',
        'end_date' => 'required|date_format:d/m/Y',
        'course_id' => 'required|exists:courses,id',
    ]);

    $courseId = $validatedData['course_id'];

    // Convert dates to Y-m-d format
    $startDate = \DateTime::createFromFormat('d/m/Y', $validatedData['start_date'])->format('Y-m-d');
    $endDate = \DateTime::createFromFormat('d/m/Y', $validatedData['end_date'])->format('Y-m-d');

    // Calculate the total number of days between start_date and end_date
    $totalDays = (new \DateTime($startDate))->diff(new \DateTime($endDate))->days + 1;

    // Build the query
    $query = DB::table('attendances')
        ->where('attendances.course_id', $courseId)
        ->join('students', 'attendances.student_id', '=', 'students.id')
        ->join('courses', 'attendances.course_id', '=', 'courses.id')
        ->select('attendances.*', 'students.name', 'students.email', 'students.phone', 'students.fname', 'students.fphone', 'courses.name as course_name');

    // Apply date range filter
    if ($startDate && $endDate) {
        $query->whereBetween('attendances.date', [$startDate, $endDate]);
    }

    // Execute the query
    $attendances = $query->get();

    if ($attendances->isEmpty()) {
        return response()->json(['status' => false, 'message' => 'No attendance records found'], 404);
    }

    // Process attendance data
    $data = [];
    $students = $attendances->groupBy('student_id');

    foreach ($students as $studentId => $studentAttendances) {
        $student = $studentAttendances->first(); // Get student data
        $presentDays = $studentAttendances->where('status', 'present')->count();
        $absentDays = $totalDays - $presentDays;

        $data[] = [
            'id' => $student->student_id,
            'name' => $student->name,
            'fname' => $student->fname,

            'course_id' => $student->course_id,
            'phone' => $student->phone,
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
        ];
    }

    // Generate the PDF
    $pdf = Pdf::loadView('attendance_report', [
        'students' => $data,
        'course_name' => $student->course_name,
        'startDate' => $startDate,
        'endDate' => $endDate
    ]);

    // Return the PDF as a download
    return $pdf->download('attendance_report.pdf');
}


public function attendanceReport(Request $request)
{
    // Validate the input data
    $validatedData = $request->validate([
        'start_date' => 'required|date_format:d/m/Y',
        'end_date' => 'required|date_format:d/m/Y',
        'course_id' => 'required|exists:courses,id',
    ]);

    $courseId = $validatedData['course_id'];

    // Convert dates to Y-m-d format
    $startDate = \DateTime::createFromFormat('d/m/Y', $validatedData['start_date'])->format('Y-m-d');
    $endDate = \DateTime::createFromFormat('d/m/Y', $validatedData['end_date'])->format('Y-m-d');

    // Calculate the total number of days between start_date and end_date
    $totalDays = (new \DateTime($startDate))->diff(new \DateTime($endDate))->days + 1;

    // Build the query
    $query = DB::table('attendances')
        ->where('attendances.course_id', $courseId)
        ->join('students', 'attendances.student_id', '=', 'students.id')
        ->join('courses', 'attendances.course_id', '=', 'courses.id') 
        ->select('attendances.*', 'students.name', 'students.email', 'students.phone', 'students.fname', 'students.fphone', 'courses.name as course_name');

    // Apply date range filter
    if ($startDate && $endDate) {
        $query->whereBetween('attendances.date', [$startDate, $endDate]);
    }

    // Execute the query
    $attendances = $query->get();

    if ($attendances->isEmpty()) {
        return response()->json(['status' => false, 'message' => 'No attendance records found'], 404);
    }

    // Process attendance data
    $data = [];
    $students = $attendances->groupBy('student_id');

    foreach ($students as $studentId => $studentAttendances) {
        $student = $studentAttendances->first(); 
        $presentDays = $studentAttendances->where('status', 'present')->count();
        $absentDays = $totalDays - $presentDays;

        $data[] = [
            'id' => $student->student_id,
            'name' => $student->name,
            'fname' => $student->fname,
            'course_id' => $student->course_id,
            'phone' => $student->phone,
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
        ];
    }

    // Return JSON response
    return response()->json([
        'status' => true,
        'course_name' => $student->course_name,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'data' => $data
    ]);
}

}
