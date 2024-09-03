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
        'specific_date' => 'nullable|date_format:d/m/Y',
        'course_id' => 'required|exists:courses,id',
    ]);

    $courseId = $validatedData['course_id'];

    // Convert dates to Y-m-d format
    $startDate = isset($validatedData['start_date']) ? \DateTime::createFromFormat('d/m/Y', $validatedData['start_date'])->format('Y-m-d') : null;
    $endDate = isset($validatedData['end_date']) ? \DateTime::createFromFormat('d/m/Y', $validatedData['end_date'])->format('Y-m-d') : null;
    $specificDate = isset($validatedData['specific_date']) ? \DateTime::createFromFormat('d/m/Y', $validatedData['specific_date'])->format('Y-m-d') : null;

    // Build the query
    $query = DB::table('attendances')
        ->where('attendances.course_id', $courseId)
        ->join('students', 'attendances.student_id', '=', 'students.id')
        ->select('attendances.*', 'students.name', 'students.email', 'students.phone', 'students.fname', 'students.fphone');

    if ($specificDate) {
        $query->whereDate('attendances.date', $specificDate);
    } elseif ($startDate && $endDate) {
        $query->whereBetween('attendances.date', [$startDate, $endDate]);
    }

    // Execute the query
    $attendances = $query->get();

    if ($attendances->isEmpty()) {
        return response()->json(['status' => false, 'message' => 'No attendance records found'], 404);
    }

    // Process attendance data
    $data = [];

    foreach ($attendances as $attendance) {
        // Fetch all attendance records for the student
        $studentAttendances = DB::table('attendances')
            ->where('student_id', $attendance->student_id)
            ->where('course_id', $courseId)
            ->get();

        $totalAbsentDays = $studentAttendances->where('status', 'absent')->count();
        $absentDaysCurrentMonth = $studentAttendances->filter(function ($att) {
            return \DateTime::createFromFormat('Y-m-d', $att->date)->format('m') == date('m') && $att->status === 'absent';
        })->count();

        $todayAttendance = $studentAttendances->firstWhere('date', date('Y-m-d'));
        $todayStatus = $todayAttendance ? $todayAttendance->status : 'no record';

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

    // Generate the PDF
    $pdf = Pdf::loadView('attendance_report', ['students' => $data]);

    // Return the PDF as a download
    return $pdf->download('attendance_report.pdf');
}
}
