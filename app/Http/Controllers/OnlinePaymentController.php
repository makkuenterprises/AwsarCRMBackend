<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; 

class OnlinePaymentController extends Controller
{

// public function payment(Request $request, $id)
// {

//      $validator = Validator::make($request->all(), [
//         'student_id' => 'required|exists:students,id',
//         'course_id' => 'required|exists:courses,id',
//         'payment_type' => ['required', 'string', 'min:1', 'max:250'],
//         'payment_status' => ['required', 'string', 'min:1', 'max:250'],
//         'paid_amount' => ['required', 'numeric', 'min:0'],
//         'due_date' => 'nullable|date_format:d/m/y'
//     ]); 

//      // Check if validation fails
//     if ($validator->fails()) { 
//         return response()->json([
//             'status' => false,
//             'code' => 400,
//             'errors' => $validator->errors()
//         ], 400);
//     }
    

//     try {

//         DB::beginTransaction();

  
//     // Fetch the Razorpay credentials from the database
//     $gateway = PaymentGateway::first();
//     if (!$gateway) {
//         return response()->json(['status' => false, 'message' => 'Payment gateway configuration not found.'], 404);
//     }

//     // Initialize Razorpay API with the fetched credentials
//     $api = new Api($gateway->api_key, $gateway->api_secret);


//      // Find the student and course
//         $student = Student::find($request->student_id);
//         if (!$student) {
//             DB::rollBack(); // Rollback the transaction
//             return response()->json(['status' => false, 'code' => 404, 'message' => 'Student not found'], 404);
//         }

//         $course = Course::find($request->course_id);
//         if (!$course) {
//             DB::rollBack(); // Rollback the transaction
//             return response()->json(['status' => false, 'code' => 404, 'message' => 'Course not found'], 404);
//         }

//         // Check if the student is already enrolled in the course
//         $enrollCourse = CoursesEnrollement::where('student_id', $request->input('student_id'))
//                                           ->where('course_id', $request->input('course_id'))
//                                           ->first();
//         if ($enrollCourse) {
//             DB::rollBack(); // Rollback the transaction
//             return response()->json(['status' => false, 'code' => 400, 'message' => 'Student is already enrolled in the course.'], 400);
//         }
         
//            if ($request->input('paid_amount') > $course->fee) {
//         return response()->json([
//             'status' => false,
//             'code' => 400,
//             'message' => 'Paid amount should be less than or equal to the course fee.',
//         ], 400);
//        }

//         // Enroll the student in the course
//         $enrollcourse = new CoursesEnrollement();
//         $enrollcourse->student_id = $request->input('student_id');
//         $enrollcourse->course_id = $request->input('course_id');
//         $enrollcourse->enrollment_date = Carbon::now('Asia/Kolkata')->toDateString(); 
//         $enrollcourse->payment_type = $request->input('payment_type'); 
//         $enrollcourse->payment_status = $request->input('payment_status');
//         $enrollcourse->paid_amount = $request->input('paid_amount');
//         $enrollcourse->due_date = $request->input('due_date');
        
//         $timestamp = time(); // Get the current Unix timestamp
//         sleep(1);
//         $randomString = Str::random(4);
//         $randomInteger = random_int(0,9999);
//         $enrollmentno = $timestamp . $randomInteger . $randomString;
//         $enrollcourse->enrollment_no = $enrollmentno;
//         $enrollcourse->save();


//         $payment = $api->payment->fetch($input['razorpay_payment_id']);
//         if (count($input) && !empty($input['razorpay_payment_id'])) {
//             $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(['amount' => $payment['amount']]);

//             // Your payment processing logic here
//             if($response==='success'){

//         $paymentHistory = new PaymentHistory();
//         $paymentHistory->enrollment_id = $enrollcourse->id;
//         $paymentHistory->transaction_id = $transactionId;
//         $paymentHistory->payment_type = $request->input('payment_type');
//         $paymentHistory->payment_status = $request->input('payment_status'); 
//         $paymentHistory->paid_amount = $request->input('paid_amount');
//         $paymentHistory->payment_date = $paymentDate;
//         $paymentHistory->save();

//         // Update student's payment status and course info
//         $student->payment_status = $request->input('payment_status'); 
//         $student->course_id = $request->input('course_id'); 
//         $student->paymentType = $request->input('payment_type');
//         $student->save();
//         $admins = Admin::all();
//         $staffMembers = StaffModel::all();
//          // Fetch and include the attached teachers
      
//         $course = Course::with('teachers')->find($request->input('course_id'));

//         if (!$course) {
//             return response()->json([
//                 'status' => false,
//                 'code' => 404,
//                 'message' => 'Course not found'
//             ], 404);
//         }

//         $teachers = $course->teachers;
//         foreach ($teachers as $teacher) {
//             $teacher->notify(new CourseEnrollmentNotificationForAdmin($course->name, $enrollcourse->enrollment_no, $enrollcourse->created_at, $student->name ));
//         }  

//           foreach ($admins as $admin) {
//             $admin->notify(new CourseEnrollmentNotificationForAdmin($course->name, $enrollcourse->enrollment_no, $enrollcourse->created_at, $student->name ));
//         }  

//         // Send notifications to staff members
//         foreach ($staffMembers as $staff) {
//             $staff->notify(new CourseEnrollmentNotificationForAdmin($course->name, $enrollcourse->enrollment_no, $enrollcourse->created_at, $student->name));
//         } 

//         $student->notify(new CourseEnrollmentNotification($course->name, $enrollcourse->enrollment_no, $enrollcourse->created_at, $student->name));
      
//         // Create an invoice
//         $invoice = new Invoice();
//         $invoice->enrollment_id = $enrollcourse->id; 
//         $invoice->student_id = $student->id;
//         $invoice->course_id = $course->id;
//         $invoice->invoice_no = 'INV' . $timestamp . $randomInteger . Str::upper(Str::random(6));
//         $invoice->student_name = $student->name;
//         $invoice->course_name = $course->name;
//         $invoice->total_amount = $course->fee;
//         $invoice->paid_amount = $request->input('paid_amount');
//         $invoice->remaining_amount = $course->fee - $request->input('paid_amount');
//         $invoice->invoice_date = Carbon::now()->toDateString();
//         $invoice->save(); 
//             }

//             DB::commit();
//         }
//     } catch (\Exception $e) {
//         DB::rollBack();
//         return response()->json(['status' => false, 'message' => 'Payment failed.', 'error' => $e->getMessage()], 500);
//     }

//     return response()->json(['status' => true, 'message' => 'Payment successful']);
// }

   public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'payment_type' => ['required', 'string', 'min:1', 'max:250'],
            'payment_status' => ['required', 'string', 'min:1', 'max:250'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'due_date' => 'nullable|date_format:d/m/y'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
           

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
            DB::rollBack();
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => 'Paid amount should be less than or equal to the course fee.',
            ], 400);
        }

            // Fetch Razorpay credentials
            $gateway = PaymentGateway::first();
            if (!$gateway) {
                return response()->json(['status' => false, 'message' => 'Payment gateway configuration not found.'], 404);
            }
            $api = new Api($gateway->api_key, $gateway->api_secret);

            // Create order
            $order = $api->order->create([
                'amount' => $request->paid_amount * 100, // Amount in paise
                'currency' => 'INR',
                'payment_capture' => 1 // Auto capture payment
            ]);

            return response()->json(['status' => true, 'payment' => $order]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Payment creation failed.', 'error' => $e->getMessage()], 500);
        }
    }

public function confirmPayment(Request $request)
{
    $validator = Validator::make($request->all(), [
        'student_id' => 'required|exists:students,id',
        'course_id' => 'required|exists:courses,id',
        'payment_type' => ['required', 'string', 'min:1', 'max:250'],
        'payment_status' => ['required', 'string', 'min:1', 'max:250'],
        'paid_amount' => ['required', 'numeric', 'min:0'],
        'due_date' => 'nullable|date_format:d/m/y',
        'payment_id' =>'required',
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'code' => 400,
            'errors' => $validator->errors()
        ], 400);
    }

    DB::beginTransaction();

    try {
        

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
            DB::rollBack();
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

        // Fetch the Razorpay credentials from the database
        $gateway = PaymentGateway::first();
        if (!$gateway) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Payment gateway configuration not found.'], 404); 
        }

        // Capture the payment
        $payment = $api->payment->fetch($request->input('payment_id'));
        $response = $payment->capture(['amount' => $payment['amount']]);

        if ($response['status'] === 'captured') {
            // Payment was successful, store the payment history
            $paymentHistory = new PaymentHistory();
            $paymentHistory->enrollment_id = $enrollcourse->id;
            $paymentHistory->transaction_id = $response['id']; // Use Razorpay payment ID as transaction ID
            $paymentHistory->payment_type = $request->input('payment_type');
            $paymentHistory->payment_status = $request->input('payment_status');
            $paymentHistory->paid_amount = $request->input('paid_amount');
            $paymentHistory->payment_date = Carbon::now()->toDateString();
            $paymentHistory->save();

            // Update student's payment status and course info
            $student->payment_status = $request->input('payment_status');
            $student->course_id = $request->input('course_id');
            $student->paymentType = $request->input('payment_type');
            $student->save();

            $admins = Admin::all();
            $staffMembers = StaffModel::all();
            $course = Course::with('teachers')->find($request->input('course_id'));

            if (!$course) {
                DB::rollBack();
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
            $invoice->transaction_id = $response['id'];
            $invoice->remaining_amount = $course->fee - $request->input('paid_amount');
            $invoice->invoice_date = Carbon::now()->toDateString();
            $invoice->save();

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Payment successful']);
        } else {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Payment capture failed.'], 500);
        }
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['status' => false, 'message' => 'Payment processing failed.', 'error' => $e->getMessage()], 500);
    }
}


public function restPaymentRazorpay(Request $request)
{
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'student_id' => 'required|exists:students,id',
        'course_id' => 'required|exists:courses,id',
        'paid_amount' => ['required', 'numeric', 'min:0'],
        'payment_type' => ['required', 'string', 'min:1', 'max:250'],
        'payment_status' => ['required', 'string', 'min:1', 'max:250'],
        'payment_id' => 'required|string', // Razorpay payment ID
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

        // Find the existing enrollment record
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

        // Fetch the Razorpay credentials from the database
        $gateway = PaymentGateway::first();
        if (!$gateway) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Payment gateway configuration not found.'], 404); 
        }

        // Initialize Razorpay API with the fetched credentials
        $api = new Api($gateway->api_key, $gateway->api_secret);

        // Capture the payment
        $payment = $api->payment->fetch($request->payment_id);
        $response = $payment->capture(['amount' => $payment['amount']]);

        if ($response['status'] !== 'captured') {
            DB::rollBack(); // Rollback the transaction
            return response()->json(['status' => false, 'code' => 400, 'message' => 'Failed to capture payment.'], 400);
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
        $transactionId = $response['id']; // Use Razorpay payment ID as transaction ID
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
        $invoice->transaction_id = $transactionId;

        $invoice->remaining_amount = $course->fee - $totalPaidAmount;
        $invoice->invoice_date = Carbon::now()->toDateString();
        $invoice->save(); 

        // Send notifications to relevant users
        $teacherId = TeacherCourse::where('course_id', $request->course_id)->value('teacher_id');
        if ($teacherId) {
            Notification::create([
                'user_id' => $teacherId,
                'message' => 'A new payment has been made for your course.',
            ]);
        }

        // Admin and staff notification logic should be implemented here

        Notification::create([
            'user_id' => $student->user_id,
            'message' => 'Your payment was successful.',
        ]);

        DB::commit(); // Commit the transaction

        return response()->json(['status' => true, 'code' => 200, 'message' => 'Payment processed successfully.'], 200);
    } catch (\Exception $e) {
        DB::rollBack(); // Rollback the transaction
        return response()->json(['status' => false, 'code' => 500, 'message' => 'Failed to process payment', 'error' => $e->getMessage()], 500);
    }
}


}
