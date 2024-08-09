<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CoursesEnrollement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\Student;
use App\Models\Course;
use App\Models\Admin;
use App\Models\StaffModel;
use App\Models\Teacher;
use Illuminate\Support\Str;
use App\Models\PaymentHistory;

use App\Models\Invoice;
use App\Notifications\CourseEnrollmentNotification;
use App\Notifications\CourseEnrollmentNotificationForAdmin;

use DB;
 
class CourseEnrollementController extends Controller
{ 

   public function enrollCourse(Request $request)
{
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'student_id' => 'required|exists:students,id',
        'course_id' => 'required|exists:courses,id',
        'payment_type' => ['required', 'string', 'min:1', 'max:250'],
        'payment_status' => ['required', 'string', 'min:1', 'max:250'],
        'paid_amount' => ['required', 'numeric', 'min:0'],
        'due_date' => 'nullable|date_format:d/m/y'
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
        DB::beginTransaction(); // Start the transaction

        // Find the student and course
        $student = Student::find($request->student_id);
        if (!$student) {
            DB::rollBack(); // Rollback the transaction
            return response()->json(['status' => false, 'code' => 404, 'message' => 'Student not found'], 404);
        }

        $course = Course::find($request->course_id);
        if (!$course) {
            DB::rollBack(); // Rollback the transaction
            return response()->json(['status' => false, 'code' => 404, 'message' => 'Course not found'], 404);
        }

        // Check if the student is already enrolled in the course
        $enrollCourse = CoursesEnrollement::where('student_id', $request->input('student_id'))
                                          ->where('course_id', $request->input('course_id'))
                                          ->first();
        if ($enrollCourse) {
            DB::rollBack(); // Rollback the transaction
            return response()->json(['status' => false, 'code' => 400, 'message' => 'Student is already enrolled in the course.'], 400);
        }
         
           if ($request->input('paid_amount') > $course->fee) {
        return response()->json([
            'status' => false,
            'code' => 400,
            'message' => 'Paid amount should be less than or equal to the course fee.',
        ], 400);
    }

        // Enroll the student in the course
        $enrollcourse = new CoursesEnrollement();
        $enrollcourse->student_id = $request->input('student_id');
        $enrollcourse->course_id = $request->input('course_id');
        $enrollcourse->enrollment_date = Carbon::now()->toDateString(); 
        $enrollcourse->payment_type = $request->input('payment_type'); 
        $enrollcourse->payment_status = $request->input('payment_status');
        $enrollcourse->paid_amount = $request->input('paid_amount');
        $enrollcourse->due_date = $request->input('due_date');
        
        $timestamp = time(); // Get the current Unix timestamp
        sleep(1);
        $randomString = Str::random(4);
        $randomInteger = random_int(0,9999);
        $enrollmentno = $timestamp . $randomInteger . $randomString;
        $enrollcourse->enrollment_no = $enrollmentno;
        $enrollcourse->save();

        // Generate transaction ID and save payment history
        $transactionId = 'TXN' . $timestamp . $randomInteger . Str::upper(Str::random(6));
        $paymentDate = Carbon::now('Asia/Kolkata'); // Get the current date and time in Asia/Kolkata timezone

        $paymentHistory = new PaymentHistory();
        $paymentHistory->enrollment_id = $enrollcourse->id;
        $paymentHistory->transaction_id = $transactionId;
        $paymentHistory->payment_type = $request->input('payment_type');
        $paymentHistory->payment_status = $request->input('payment_status'); 
        $paymentHistory->paid_amount = $request->input('paid_amount');
        $paymentHistory->payment_date = $paymentDate;
        $paymentHistory->save();

        // Update student's payment status and course info
        $student->payment_status = $request->input('payment_status'); 
        $student->course_id = $request->input('course_id'); 
        $student->paymentType = $request->input('payment_type');
        $student->save();
        $admins = Admin::all();
        $staffMembers = StaffModel::all();
         // Fetch and include the attached teachers
      
        $course = Course::with('teachers')->find($request->input('course_id'));

        if (!$course) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => 'Course not found'
            ], 404);
        }

        $teachers = $course->teachers;
        foreach ($teachers as $teacher) {
            $teacher->notify(new CourseEnrollmentNotificationForAdmin($course->name, $enrollcourse->enrollment_no, $enrollcourse->created_at, $student->name ));
        }  

          foreach ($admins as $admin) {
            $admin->notify(new CourseEnrollmentNotificationForAdmin($course->name, $enrollcourse->enrollment_no, $enrollcourse->created_at, $student->name ));
        }  

        // Send notifications to staff members
        foreach ($staffMembers as $staff) {
            $staff->notify(new CourseEnrollmentNotificationForAdmin($course->name, $enrollcourse->enrollment_no, $enrollcourse->created_at, $student->name));
        } 

        $student->notify(new CourseEnrollmentNotification($course->name, $enrollcourse->enrollment_no, $enrollcourse->created_at, $student->name));
      
        // Create an invoice
        $invoice = new Invoice();
        $invoice->enrollment_id = $enrollcourse->id; 
        $invoice->student_id = $student->id;
        $invoice->course_id = $course->id;
        $invoice->invoice_no = 'INV' . $timestamp . $randomInteger . Str::upper(Str::random(6));
        $invoice->student_name = $student->name;
        $invoice->course_name = $course->name;
        $invoice->total_amount = $course->fee;
        $invoice->paid_amount = $request->input('paid_amount');
        $invoice->remaining_amount = $course->fee - $request->input('paid_amount');
        $invoice->invoice_date = Carbon::now()->toDateString();
        $invoice->save(); 
       
        DB::commit(); // Commit the transaction
        return response()->json(['status' => true, 'code' => 200, 'message' => 'Student enrolled in the course successfully'], 200);
    } catch (\Exception $e) {
        DB::rollBack(); // Rollback the transaction
        return response()->json(['status' => false, 'code' => 500, 'message' => 'Failed to enroll student in the course', 'error' => $e->getMessage()], 500);
    }
}
  
public function getPaymentHistory(Request $request)
{
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'student_id' => 'required|exists:students,id',
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
        // Retrieve the payment history for the specific course and student using join
          $paymentHistory = DB::table('payment_histories')
            ->join('courses_enrollements', 'payment_histories.enrollment_id', '=', 'courses_enrollements.id')
            ->where('courses_enrollements.student_id', $request->student_id)
            ->where('courses_enrollements.course_id', $request->course_id)
            ->select('payment_histories.transaction_id', 'payment_histories.payment_type', 'payment_histories.payment_status', 'payment_histories.paid_amount', 'payment_histories.payment_date')
            ->get(); 

        if ($paymentHistory->isEmpty()) {
            return response()->json(['status' => false, 'code' => 404, 'message' => 'No payment history found for the specified course and student'], 404);
        }

            return response()->json([
                'status' => true,
                'code' => 200, 
                'data' => $paymentHistory
            ], 200);
    } catch (\Exception $e) {
        return response()->json(['status' => false, 'code' => 500, 'message' => 'Failed to retrieve payment history', 'error' => $e->getMessage()], 500);
    }
}


public function PaymentHistory(Request $request)
{
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'student_id' => 'required|exists:students,id',
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
        // Retrieve the total fee for the course
        $course = DB::table('courses')
            ->where('id', $request->course_id)
            ->first(['fee']); // Column for total fee

        if (!$course) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => 'Course not found'
            ], 404);
        }

        // Retrieve the enrollment details to get due date
        $enrollment = DB::table('courses_enrollements')
            ->where('student_id', $request->student_id)
            ->where('course_id', $request->course_id)
            ->first(['due_date']); // Column for due date

        if (!$enrollment) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => 'Enrollment not found'
            ], 404);
        }

        // Retrieve the payment history for the specific course and student
        $paymentHistory = DB::table('payment_histories')
            ->join('courses_enrollements', 'payment_histories.enrollment_id', '=', 'courses_enrollements.id')
            ->where('courses_enrollements.student_id', $request->student_id)
            ->where('courses_enrollements.course_id', $request->course_id)
            ->select('payment_histories.transaction_id', 'payment_histories.payment_type', 'payment_histories.payment_status', 'payment_histories.paid_amount', 'payment_histories.payment_date')
            ->get(); 

        if ($paymentHistory->isEmpty()) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => 'No payment history found for the specified course and student'
            ], 404);
        }

        // Calculate total paid amount
        $totalPaidAmount = $paymentHistory->sum('paid_amount');
        // Calculate outstanding balance
        $outstandingAmount = $course->fee - $totalPaidAmount;

        // Return success response with payment history and additional data
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => [
                'payment_history' => $paymentHistory,
                'fee_paid' => $totalPaidAmount,
                'fee_payable' => $course->fee,
                'outstanding' => $outstandingAmount,
                'due_date' => $enrollment->due_date
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'Failed to retrieve payment history',
            'error' => $e->getMessage()
        ], 500);
    }
} 

public function restPayment(Request $request)
{
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'student_id' => 'required|exists:students,id',
        'course_id' => 'required|exists:courses,id',
        'paid_amount' => ['required', 'numeric', 'min:0'],
        'payment_type' => ['required', 'string', 'min:1', 'max:250'],
        'payment_status' => ['required', 'string', 'min:1', 'max:250'],
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
        DB::beginTransaction(); // Start the transaction

        // Find the existing enrollment record with student details using join
       $enrollment = CoursesEnrollement::where('student_id', $request->student_id)
            ->where('course_id', $request->course_id)
            ->first();




        if (!$enrollment) {
            DB::rollBack(); // Rollback the transaction
            return response()->json(['status' => false, 'code' => 404, 'message' => 'Enrollment not found'], 404);
        }

        // Find the student and course
        $student = Student::find($request->student_id);
        if (!$student) {
            DB::rollBack(); // Rollback the transaction
            return response()->json(['status' => false, 'code' => 404, 'message' => 'Student not found'], 404);
        }

        $course = Course::find($request->course_id);
        if (!$course) {
            DB::rollBack(); // Rollback the transaction
            return response()->json(['status' => false, 'code' => 404, 'message' => 'Course not found'], 404);
        }
        // Check if the course and student are retrieved
        if (!$course || !$student) {
            DB::rollBack(); // Rollback the transaction
            return response()->json(['status' => false, 'code' => 404, 'message' => 'Course or Student not found'], 404);
        }

        // Retrieve the course fee
        $course = Course::find($request->course_id);
        if (!$course) {
            DB::rollBack(); // Rollback the transaction
            return response()->json(['status' => false, 'code' => 404, 'message' => 'Course not found'], 404);
        }

        // Calculate remaining amount
        $totalPaidAmount = $enrollment->paid_amount + $request->paid_amount;
        if ($totalPaidAmount > $course->fee) {
            DB::rollBack(); // Rollback the transaction
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => 'Paid amount exceeds the course fee.',
            ], 400);
        }

        // Update enrollment with new payment details
        $enrollment->paid_amount = $totalPaidAmount;
        $enrollment->save(); // Save changes to the enrollment

        // Generate transaction ID and save payment history
        $timestamp = time();
        $transactionId = 'TXN' . $timestamp . Str::upper(Str::random(6));
        $paymentDate = Carbon::now('Asia/Kolkata');

        $paymentHistory = new PaymentHistory();
        $paymentHistory->enrollment_id = $enrollment->id;
        $paymentHistory->transaction_id = $transactionId;
        $paymentHistory->payment_type = $request->payment_type;
        $paymentHistory->payment_status = $request->payment_status;
        $paymentHistory->paid_amount = $request->paid_amount;
        $paymentHistory->payment_date = $paymentDate;
        $paymentHistory->save();

        // Create Invoice
        $invoice = new Invoice();
        $invoice->enrollment_id = $enrollment->id; 
        $invoice->student_id = $request->student_id;
        $invoice->course_id = $request->course_id;
        $invoice->invoice_no = 'INV' . $timestamp . Str::upper(Str::random(6)); // Generating a unique invoice number
        $invoice->student_name = $student->name; // Ensure you have a `student` relationship in CoursesEnrollement
        $invoice->course_name = $course->name;
        $invoice->total_amount = $course->fee;
        $invoice->paid_amount = $request->paid_amount;
        $invoice->remaining_amount = $course->fee - $totalPaidAmount;
        $invoice->invoice_date = Carbon::now()->toDateString();
        $invoice->save(); 

        DB::commit(); // Commit the transaction
        return response()->json(['status' => true, 'code' => 200, 'message' => 'Rest payment processed successfully'], 200);
    } catch (\Exception $e) {
        DB::rollBack(); // Rollback the transaction
        return response()->json(['status' => false, 'code' => 500, 'message' => 'Failed to process rest payment', 'error' => $e->getMessage()], 500);
    }
}


}
