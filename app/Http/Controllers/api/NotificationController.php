<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Course;
use App\Models\Notice;
use App\Models\StaffModel;
use App\Models\Student;
use App\Notifications\NoticeNotification;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('api');
    // }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:1|max:255',
            'description' => ['nullable', 'string', 'min:1', 'max:250'],
            'sendTo' => ['required', 'string', 'min:1', 'max:250'],
            'batch' => 'nullable|array',
            'batch.*' => 'string|exists:courses,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $notification = new Notice();
            $notification->title = $request->input('title');
            $notification->description = $request->input('description');
            $notification->sendTo = $request->input('sendTo');
            $notification->batch = json_encode($request->input('batch')); // Store as JSON
            $notification->save();

            // Fetch courses associated with the specified batches
            if ($request->has('batch')) {
                $batchNames = $request->input('batch');
                $courses = Course::whereIn('name', $batchNames)->get();
            } else {
                $courses = Course::all(); // Fetch all courses if no batches specified (for admins and staff)
            }

            $data = [
                'type' => 'notice',
                'notice_id' => $notification->id, // Example of additional data
            ];

            // Send notifications to students
            if ($request->has('batch')) {
                $courseNames = $request->input('batch');

                // Fetch students enrolled in the specified batches
                $students = DB::table('students')
                    ->join('courses_enrollements', 'students.id', '=', 'courses_enrollements.student_id')
                    ->join('courses', 'courses.id', '=', 'courses_enrollements.course_id')
                    ->whereIn('courses.name', $courseNames)
                    ->select('students.id', 'students.name', 'students.email')
                    ->distinct()
                    ->get();

                foreach ($students as $student) {
                    $studentModel = Student::find($student->id);
                    if ($studentModel) {
                        $studentModel->notify(new NoticeNotification($notification));
                    }

                    if ($studentModel->one_signal_id) {
                        $message = 'A new notice has been posted: "' . $notification->title . '". ';

                        $this->sendOneSignalNotification(
                            $studentModel->one_signal_id,
                            'New Notice Posted',
                            $message,
                            $data
                        );
                    }
                }

            }
            // Send notifications to teachers
            $teachers = collect();
            foreach ($courses as $course) {
                $teachers = $teachers->merge($course->teachers()->get());
            }
            $teachers = $teachers->unique('id');
            foreach ($teachers as $teacher) {
                $teacher->notify(new NoticeNotification($notification));

                if ($teacher->one_signal_id) {
                    $message = 'A new notice has been posted: "' . $notification->title . '". ';
                    $this->sendOneSignalNotificationGuru(
                        $teacher->one_signal_id,
                        'New Notice Posted',
                        $message,
                        $data
                    );

                }

            }

            // Send notifications to admins
            $admins = Admin::all();
            foreach ($admins as $admin) {
                $admin->notify(new NoticeNotification($notification));
            }

            // Send notifications to staff members
            $staffMembers = StaffModel::all();
            foreach ($staffMembers as $staff) {
                $staff->notify(new NoticeNotification($notification));
            }

            // Encode batch data to JSON for consistent response
            $notification->batch = json_decode($notification->batch, true); // Decode JSON to array

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'Notice created successfully',
                'notification' => $notification,
            ], 200);
        } catch (Exception $e) {
            $data = ['error' => $e->getMessage()];
            return response()->json([
                'status' => false,
                'code' => 500,
                'message' => 'An error occurred while creating notice',
                'data' => $data,
            ], 500);
        }
    }

    protected function sendOneSignalNotification($oneSignalId, $title, $message, $data)
    {

        $content = [
            "en" => $message,
        ];

        $fields = [
            'app_id' => '3b902819-bb6c-4b89-a5c1-fe44ed11cb8a',
            // 'app_id' => config('services.onesignal.app_id'),
            'include_player_ids' => [$oneSignalId],
            'headings' => ["en" => $title],
            'contents' => $content,
            'data' => $data,

        ];

        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . 'YzdjM2FiOTctMGVjZC00ODMyLWJlNDQtY2E2NmNiOTFmNzQy',
            // 'Authorization: Basic ' . config('services.onesignal.rest_api_key')
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);
        //  dd($response);
        return $response;

    }

    protected function sendOneSignalNotificationGuru($oneSignalId, $title, $message, $data)
    {

// dd('fire');

        $content = [
            "en" => $message,
        ];

        $fields = [
            'app_id' => '46828aab-35e5-4531-89fd-3a6102091edc',
            // 'app_id' => config('services.onesignal.app_id'),
            'include_player_ids' => [$oneSignalId],
            'headings' => ["en" => $title],
            'contents' => $content,
            'data' => $data,
        ];

        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . 'ZWZkNDk5OTYtZGZjNC00MTRmLTkwZWMtZDQ4NzIxZGUyYjg4',
            // 'Authorization: Basic ' . config('services.onesignal.rest_api_key')
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);
        // dd($response);
        return $response;

    }

    public function list()
    {

        try {
            // Retrieve all notifications including the newly created one
            $notifications = Notice::orderBy('id', 'asc')->get();

            // Transform batch JSON data back to array format for each notification
            $notifications->transform(function ($notification) {
                $notification->batch = json_decode($notification->batch, true); // Decode JSON to array
                return $notification;
            });

            return response()->json([
                'status' => true,
                'code' => 200,
                'notifications' => $notifications,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'message' => 'An error occurred while fetching notice',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function studentNoticelist(Request $request)
    {
        try {
            // Get the student_id from the request
            $studentId = $request->input('student_id');

            // Get the list of course names the student is enrolled in
            $enrolledCourseNames = DB::table('courses')
                ->join('courses_enrollements', 'courses.id', '=', 'courses_enrollements.course_id')
                ->where('courses_enrollements.student_id', $studentId)
                ->pluck('courses.name')
                ->toArray();

            // Retrieve all notifications
            $notifications = Notice::orderBy('id', 'asc')->get();

            // Filter notifications based on the student's enrolled courses
            $filteredNotifications = $notifications->filter(function ($notification) use ($enrolledCourseNames) {
                $batch = json_decode($notification->batch, true);
                if (is_array($batch)) {
                    // Check if there is any intersection between the batch and enrolled courses
                    return !empty(array_intersect($batch, $enrolledCourseNames));
                }
                return false;
            });

            // Transform batch JSON data back to array format for each notification
            $filteredNotifications->transform(function ($notification) {
                $notification->batch = json_decode($notification->batch, true); // Decode JSON to array
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'created_date' => $notification->created_at,
                    'description' => strip_tags($notification->description), // Remove HTML tags from description
                ];
            });

            return response()->json([
                'status' => true,
                'code' => 200,
                'notifications' => $filteredNotifications->values(), // Reset array keys
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'message' => 'An error occurred while fetching notice',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
