<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;

class OnlinePaymentController extends Controller
{

public function payment(Request $request, $id)
{

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

        DB::beginTransaction();

  
    // Fetch the Razorpay credentials from the database
    $gateway = PaymentGateway::first();
    if (!$gateway) {
        return response()->json(['status' => false, 'message' => 'Payment gateway configuration not found.'], 404);
    }

    // Initialize Razorpay API with the fetched credentials
    $api = new Api($gateway->api_key, $gateway->api_secret);


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


        $payment = $api->payment->fetch($input['razorpay_payment_id']);
        if (count($input) && !empty($input['razorpay_payment_id'])) {
            $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(['amount' => $payment['amount']]);

            // Your payment processing logic here
            if($response==='success'){

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
            }

            DB::commit();
        }
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['status' => false, 'message' => 'Payment failed.', 'error' => $e->getMessage()], 500);
    }

    return response()->json(['status' => true, 'message' => 'Payment successful']);
}

}
