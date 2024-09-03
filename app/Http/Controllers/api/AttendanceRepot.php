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
        'start_date' => 'nullable|date_format:d/m/Y',
        'end_date' => 'nullable|date_format:d/m/Y',
        'course_id' => 'required|exists:courses,id',
    ]);

    $courseId = $validatedData['course_id'];

    // Convert dates to Y-m-d format
    $startDate = isset($validatedData['start_date']) ? \DateTime::createFromFormat('d/m/Y', $validatedData['start_date'])->format('Y-m-d') : null;
    $endDate = isset($validatedData['end_date']) ? \DateTime::createFromFormat('d/m/Y', $validatedData['end_date'])->format('Y-m-d') : null;

    // Build the query
    $query = DB::table('attendances')
        ->where('attendances.course_id', $courseId)
        ->join('students', 'attendances.student_id', '=', 'students.id')
        ->select('attendances.*', 'students.name', 'students.email', 'students.phone', 'students.fname', 'students.fphone');

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
        $totalDays = $studentAttendances->count();
        $presentDays = $studentAttendances->where('status', 'present')->count();
        $absentDays = $totalDays - $presentDays;

        $data[] = [
            'id' => $student->student_id,
            'name' => $student->name,
            'course_id' => $student->course_id,
            'email' => $student->email,
            'phone' => $student->phone,
            'fname' => $student->fname,
            'fphone' => $student->fphone,
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
        ];
    }

    // Generate the PDF
    $pdf = Pdf::loadView('attendance_report', ['students' => $data,
    'startDate' => $startDate,
    'endDate' => $endDate]);

    // Return the PDF as a download
    return $pdf->download('attendance_report.pdf');
}
public function attendanceReport(Request $request)
{
    // Validate the input data
    $validatedData = $request->validate([
        'start_date' => 'nullable|date_format:d/m/Y',
        'end_date' => 'nullable|date_format:d/m/Y',
        'course_id' => 'required|exists:courses,id',
    ]);

    $courseId = $validatedData['course_id'];

    // Convert dates to Y-m-d format
    $startDate = isset($validatedData['start_date']) ? \DateTime::createFromFormat('d/m/Y', $validatedData['start_date'])->format('Y-m-d') : null;
    $endDate = isset($validatedData['end_date']) ? \DateTime::createFromFormat('d/m/Y', $validatedData['end_date'])->format('Y-m-d') : null;

    // Build the query
    $query = DB::table('attendances')
        ->where('attendances.course_id', $courseId)
        ->join('students', 'attendances.student_id', '=', 'students.id')
        ->join('courses', 'attendances.course_id', '=', 'courses.id') // Join with the courses table
        ->select('attendances.*', 'students.name', 'students.email', 'students.phone', 'students.fname', 'students.fphone', 'courses.course_name'); // Select course_name

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
        $totalDays = $studentAttendances->count();
        $presentDays = $studentAttendances->where('status', 'present')->count();
        $absentDays = $totalDays - $presentDays;

        $data[] = [
            'id' => $student->student_id,
            'name' => $student->name,
            'course_id' => $student->course_id,
            'course_name' => $student->name, // Include course_name
            'email' => $student->email,
            'phone' => $student->phone,
            'fname' => $student->fname,
            'fphone' => $student->fphone,
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
        ];
    }

    // Return JSON response
    return response()->json([
        'status' => true,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'data' => $data
    ]);
}

}
