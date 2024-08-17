<?php

use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AdminAuthController; 
use App\Http\Controllers\api\StudentAuthController; 
use App\Http\Controllers\api\TeacherAuthController;
use App\Http\Controllers\api\StaffAuthController;
use App\Http\Controllers\api\CourseController;
use App\Http\Controllers\api\NotificationController;
// use App\Http\Controllers\api\MeetingCreateController; 
use App\Http\Controllers\api\AttendanceController;
use App\Http\Controllers\api\CourseEnrollementController;
use App\Http\Controllers\api\StudyMaterialsController;
use App\Http\Controllers\api\DetailsController;
use App\Http\Controllers\api\LeaveRequestController;
use App\Http\Controllers\api\ClassRoutineController;
use App\Http\Controllers\api\PaymentGatewayController; 
use App\Http\Controllers\api\DashboardData;
use App\Http\Controllers\api\subjectController;
use App\Http\Controllers\api\ImagesSlidesController;

use App\Http\Controllers\MeetingController;
use App\Http\Controllers\ZoomController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\Notification;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamResponseController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OnlinePaymentController;
 

/* 
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::group(['middleware'=>'admin','prefix'=>'admin','as'=>'admin.'],function(){
// Route::controller(AdminAuthController::class)->group(function(){

//       Route::get('adminnnnn','Admin')->name('get');
// });
// });
 

// ------------------------------------------------------------------------------------------------
// ADMIN LOGIN AND SETTING ROUTES ROUTES
// ------------------------------------------------------------------------------------------------


Route::prefix('admin')->group(function () {

Route::post('/login',[AdminAuthController::class,'adminAuthLogin'])->name('admin.login');
Route::post('/logout',[AdminAuthController::class,'adminAuthLogout']);


});  

Route::group(['middleware'=>'admin'],function(){ 
  
Route::prefix('admin')->group(function () {

Route::get('/view/profile/update/{id}', [AdminAuthController::class, 'profileUpdateView']);
Route::post('/profile/update/{id}', [AdminAuthController::class, 'profileUpdate']);
Route::post('/password/update', [AdminAuthController::class, 'passwordUpdate']);

}); 


// subject ==========================================================================

Route::post('/subjects', [subjectController::class, 'create']);
Route::put('/subjects/{id}', [subjectController::class, 'update']);
Route::delete('/subjects/{id}', [subjectController::class, 'delete']);
Route::get('/subjects', [SubjectController::class, 'index']);
Route::get('/subjects/{id}', [SubjectController::class, 'show']);



// Slides Images
Route::post('/slider-images', [ImagesSlidesController::class, 'storeMultiple']);


Route::post('create/community', [BlogController::class, 'store']);
Route::delete('/community/{id}', [BlogController::class, 'destroy']);
Route::post('/community/{id}', [BlogController::class, 'update']);

Route::get('/community/{id}', [BlogController::class, 'show']);


Route::get('/invoices', [InvoiceController::class, 'getAllInvoices']);
Route::post('/invoices/student', [InvoiceController::class, 'getAllInvoicesByStudent']);

//  payment Gateway============================================================

Route::get('/payment-gateways', [PaymentGatewayController::class, 'index']);
Route::post('/payment-gateways', [PaymentGatewayController::class, 'store']);
 

 
});    

// Route::group(['middleware'=>'admin','middleware'=>'staff'],function(){
// });  


Route::post('get-attendance-by-date', [AttendanceController::class, 'getAttendanceByDate']);

// Route::group(['middleware'=>'admin','middleware'=>'teacher','middleware'=>'staff','middleware'=>'student'],function(){
Route::get('/slider-images', [ImagesSlidesController::class, 'showImages']);



// }); 
 



Route::group(['middleware' => ['admin']], function() { 

// ATTENDANCE ADMIN===============================================================================

Route::prefix('attendance')->group(function () { 

      Route::post('/list/{course_id}', [AttendanceController::class, 'getStudents']);
      Route::get('/list', [AttendanceController::class, 'alllist']);
     
});  


Route::prefix('student')->group(function () {
      
        Route::get('/list', [StudentAuthController::class, 'StudentList']);
    
});      

// ------------------------------------------------------------------------------------------------
// STUDENT CREATE ROUTES
// ------------------------------------------------------------------------------------------------

Route::prefix('student')->group(function () {
        Route::post('/register', [StudentAuthController::class, 'StudentCreate']);
        Route::get('/list', [StudentAuthController::class, 'StudentList']);
        Route::delete('delete/{id}', [StudentAuthController::class, 'deleteStudent']);
        Route::get('course/list', [StudentAuthController::class, 'courseList']);
});

// ------------------------------------------------------------------------------------------------
// TEACHER CREATE ROUTES
// ------------------------------------------------------------------------------------------------    

Route::prefix('teacher')->group(function () {

      Route::post('/register', [TeacherAuthController::class, 'teacherCreate']);
      Route::get('/list', [TeacherAuthController::class, 'teacherList']);
      Route::get('view/update/{id}/', [TeacherAuthController::class, 'UpdateView']);
      Route::post('update/{id}', [TeacherAuthController::class, 'updateTeacher']);
      Route::delete('delete/{id}', [TeacherAuthController::class, 'deleteTeacher']);
});  

// ------------------------------------------------------------------------------------------------
// STAFF CREATE ROUTES
// ------------------------------------------------------------------------------------------------

Route::prefix('staff')->group(function () {

      Route::post('/register', [StaffAuthController::class, 'staffCreate']);
      Route::get('/list', [StaffAuthController::class, 'staffList']);
      Route::get('view/update/{id}/', [StaffAuthController::class, 'UpdateView']);
      Route::post('update/{id}', [StaffAuthController::class, 'updateStaff']);
      Route::delete('delete/{id}', [StaffAuthController::class, 'deleteStaff']);

});  

// ------------------------------------------------------------------------------------------------
// COURSE CREATE ROUTES
// ------------------------------------------------------------------------------------------------
 
Route::prefix('course')->group(function () {

      Route::post('/create', [CourseController::class, 'courseCreate']);
      Route::get('/list', [CourseController::class, 'courseList']);
      Route::get('view/update/{id}/', [CourseController::class, 'UpdateView']);
      Route::post('update/{id}', [CourseController::class, 'courseUpdate']);
      Route::delete('delete/{id}', [CourseController::class, 'deleteCourse']);

}); 
 

// ------------------------------------------------------------------------------------------------
// DETAILS ADD
// ------------------------------------------------------------------------------------------------


Route::post('details/add', [DetailsController::class, 'index']);
Route::get('details/view/update/{id}', [DetailsController::class, 'show']);
Route::post('details/update/{id}', [DetailsController::class, 'update']);

Route::get('teacher/student-list-of-teacher/{id}', [TeacherAuthController::class, 'studentListForTeacher']);



// ------------------------------------------------------------------------------------------------
// NOTIFICATION ROUTES 
// ------------------------------------------------------------------------------------------------

Route::prefix('notice')->group(function () {

      Route::post('/create', [NotificationController::class, 'create']);
      Route::get('/list', [NotificationController::class, 'List']);

}); 

// ------------------------------------------------------------------------------------------------
// COURSE ENROLL ROUTES
// ------------------------------------------------------------------------------------------------


Route::post('course/enroll', [CourseEnrollementController::class, 'enrollCourse']);

// ------------------------------------------------------------------------------------------------
// COURSE ENROLL ROUTES
// ------------------------------------------------------------------------------------------------
 


Route::post('course/enroll', [CourseEnrollementController::class, 'enrollCourse']); 
Route::post('payment-history', [CourseEnrollementController::class, 'getPaymentHistory']);
Route::post('payment-details', [CourseEnrollementController::class, 'PaymentHistory']);
Route::post('/rest-payment', [CourseEnrollementController::class, 'restPayment']);



Route::post('/enrollment-order', [OnlinePaymentController::class, 'createOrder']);
Route::post('/confirm-payment', [OnlinePaymentController::class, 'confirmPayment']);
Route::post('rest/confirm-payment', [OnlinePaymentController::class, 'restPaymentRazorpay']);



// routine
Route::get('class-routines', [ClassRoutineController::class, 'index']);
Route::post('store/class-routines', [ClassRoutineController::class, 'store']);
Route::get('class-routines/{id}', [ClassRoutineController::class, 'show']);
Route::put('class-routines/{id}', [ClassRoutineController::class, 'update']);
Route::delete('class-routines/{id}', [ClassRoutineController::class, 'destroy']);


Route::post('create/class-routines', [ClassRoutineController::class, 'createTimeSlot']);
Route::post('update/class-routines', [ClassRoutineController::class, 'updateTimeSlot']);
Route::get('delete/class-routines/{course_id}', [ClassRoutineController::class, 'deleteTimeSlotsByBatchId']);
Route::get('class-routines/time/{course_id}', [ClassRoutineController::class, 'showClassTimeRoutine']);
Route::post('assign/subject', [ClassRoutineController::class, 'assignSubject']);
Route::post('assign/subject/update', [ClassRoutineController::class, 'assignSubjectUpdate']);


}); 

// ------------------------------------------------------------------------------------------------
// STUDY MATERIALS UPLOAD
// ------------------------------------------------------------------------------------------------


Route::post('study-material/upload', [StudyMaterialsController::class, 'store']);
Route::get('study-materials', [StudyMaterialsController::class, 'index']); 
// Route::get('study-material/download/{id}/{filePath}', [StudyMaterialsController::class, 'downloadMaterial']);
Route::post('study-material/download', [StudyMaterialsController::class, 'downloadMaterial']);




// }); 




// });



//  staff===========================================================================================


// ------------------------------------------------------------------------------------------------
// STAFF PANEL ROUTES
// ------------------------------------------------------------------------------------------------

Route::prefix('staff')->group(function () {
      Route::post('/login',[StaffAuthController::class,'staffAuthLogin']);
      Route::post('/logout',[StaffAuthController::class,'staffAuthLogout']); 
});  


Route::middleware(['staff'])->group(function () {

Route::prefix('staff')->group(function () {

      Route::get('/view/profile/update/{id}', [StaffAuthController::class, 'profileUpdateView']);
      Route::post('/profile/update/{id}', [StaffAuthController::class, 'profileUpdate']);
      Route::post('/password/update', [StaffAuthController::class, 'passwordUpdate']);

}); 

}); 





// teacher==========================================================================================

// ------------------------------------------------------------------------------------------------
// TEACHER PANEL ROUTES
// ------------------------------------------------------------------------------------------------   

Route::prefix('teacher')->group(function () {
 
      Route::post('/login',[TeacherAuthController::class,'teacherAuthLogin']);
      Route::post('/logout',[TeacherAuthController::class,'teacherAuthLogout']);


});  

Route::middleware(['teacher'])->group(function () {
Route::get('teacher/today-classes/{teacherId}', [ClassRoutineController::class, 'getTodayClasses']);

      
Route::prefix('teacher')->group(function () {

      Route::get('/view/profile/update/{id}', [TeacherAuthController::class, 'profileUpdateView']);
      Route::post('/profile/update/{id}', [TeacherAuthController::class, 'profileUpdate']);
      Route::post('/password/update', [TeacherAuthController::class, 'passwordUpdate']);


}); 

Route::get('course/list/for/teacher/{id}', [CourseController::class, 'courseListForTeacher']);

Route::get('/student-list-of-teacher/{id}', [TeacherAuthController::class, 'studentListForTeacher']);




// ATTENDANCE TEACHER=============================================================================================

Route::prefix('attendance')->group(function () { 

      Route::get('course/list', [AttendanceController::class, 'alllist']);
      Route::post('student/list/{course_id}', [AttendanceController::class, 'getStudentsEnrolledInCourse']);
      Route::post('/submit-attendance', [AttendanceController::class, 'create']);
 
     
     
}); 


}); 

// ------------------------------------------------------------------------------------------------
// NOTIFICATION ROUTES
// ------------------------------------------------------------------------------------------------

Route::prefix('notice')->group(function () {

      Route::post('/create', [NotificationController::class, 'create']);
      Route::get('/list', [NotificationController::class, 'List']);

}); 
 
Route::prefix('course')->group(function () {
      Route::get('/list', [CourseController::class, 'courseList']);
}); 

// ------------------------------------------------------------------------------------------------
// STUDY MATERIALS UPLOAD
// ------------------------------------------------------------------------------------------------

Route::post('study-material/upload', [StudyMaterialsController::class, 'store']);
Route::get('study-materials', [StudyMaterialsController::class, 'index']); 
Route::get('student/study-materials/{course_id}', [StudyMaterialsController::class, 'studentMaterials']);
// Route::get('study-material/download/{id}/{filePath}', [StudyMaterialsController::class, 'downloadMaterial']);
Route::post('study-material/download', [StudyMaterialsController::class, 'downloadMaterial']);

// });
  


// ------------------------------------------------------------------------------------------------
// STUDENT PANEL ROUTES
// ------------------------------------------------------------------------------------------------

Route::prefix('student')->group(function () {
Route::post('/login',[StudentAuthController::class,'studentAuthLogin']);

});

Route::middleware(['student'])->group(function () {
Route::get('student/study-materials/{course_id}', [StudyMaterialsController::class, 'studentMaterials']);

Route::prefix('student')->group(function () {
Route::post('/logout',[StudentAuthController::class,'studentAuthLogout']);


      Route::get('/view/profile/update/{id}', [StudentAuthController::class, 'profileUpdateView']);
      Route::post('/profile/update/{id}', [StudentAuthController::class, 'profileUpdate']);
      Route::post('/password/update', [StudentAuthController::class, 'passwordUpdate']);

});  

Route::get('student/study-materials/{course_id}', [StudyMaterialsController::class, 'studentMaterials']);
Route::post('study-material/download', [StudyMaterialsController::class, 'downloadMaterial']);
 
Route::post('student/teacher/list/{id}', [StudentAuthController::class, 'TeachersLists']);

}); 
 

// ATTENDANCE STUDENT=============================================================================================

Route::prefix('attendance')->group(function () { 

      Route::get('student/course/list/{student_id}', [AttendanceController::class, 'getCoursesByStudent']);
      Route::post('/student-attendace', [AttendanceController::class, 'getAttendanceByDateStudent']);
      Route::post('between-date-wise', [AttendanceController::class, 'getAttendanceBetweenDates']);
   
     
}); 

// ------------------------------------------------------------------------------------------------
// NOTIFICATION ROUTES
// ------------------------------------------------------------------------------------------------

Route::prefix('notice')->group(function () {
      Route::get('/list', [NotificationController::class, 'List']);
      Route::post('/list/student', [NotificationController::class, 'studentNoticelist']);
}); 
 
Route::get('/notify/{Student_id}', [Notification::class, 'fetchNotifications']);
Route::post('/mark/as/read', [Notification::class, 'markAsRead']);
Route::post('/mark/all/as/read', [Notification::class, 'markAllAsRead']);


Route::post('/notification/role', [Notification::class, 'fetchNotificationsByRole']);
Route::post('/mark/as/read/role', [Notification::class, 'markAsReadByrole']);
Route::post('/mark/all/as/read/role', [Notification::class, 'markAllAsReadforRole']);
 
// });


// ------------------------------------------------------------------------------------------------
// ATTENDANCE ROUTE
// ------------------------------------------------------------------------------------------------

Route::post('/student-batch-details', [AttendanceController::class, 'getStudentBatchDetails']);
Route::get('/all-student-batch-details', [AttendanceController::class, 'getAllStudentBatchDetails']);







// ------------------------------------------------------------------------------------------------
// MEETING ROUTES
// ------------------------------------------------------------------------------------------------

// Route::prefix('meeting')->group(function () {
 
//       Route::post('/create', [MeetingCreateController::class, 'create']);
     
// });


// ---------------------------------------------------------------------------------------------------------
// LEAVE ROUTES
// ----------------------------------------------------------------------------------------------------------
// for staff and admin---------------------------------------------------------------------------------------

Route::prefix('leave-request')->group(function () {
 
      Route::get('/list', [LeaveRequestController::class, 'viewLeaveRequestList']);
      Route::post('/update-status/{id}', [LeaveRequestController::class, 'handleLeaveRequestUpdate']);
      Route::post('/update-remark/{id}', [LeaveRequestController::class, 'handleLeaveRequestUpdateRemark']);
     
});    
 
// teacher----------------------------------------------------------------------------------------------------
Route::prefix('leave-request')->group(function () {
 
      Route::post('/create', [LeaveRequestController::class, 'handleLeaveRequestCreate']);
      Route::post('role/list', [LeaveRequestController::class, 'viewLeaveRequestListForFaculty']);
     
 
}); 




// Route::get('data-dashboard', [DashboardData::class, 'dashboardaData']);




// questions-----------------------------------------------------------------------------------------------

Route::get('questions', [QuestionController::class, 'index']);
Route::get('all/questions', [QuestionController::class, 'index2']);
Route::post('questions', [QuestionController::class, 'store']); 
Route::get('questions/{id}', [QuestionController::class, 'show']);
Route::post('questions/{id}', [QuestionController::class, 'update']);
Route::delete('questions/{id}', [QuestionController::class, 'destroy']);

 
// EXAMS====================================================================================================
 

Route::post('/exams', [ExamController::class, 'createExam']);
Route::post('/exam/preview', [ExamController::class, 'getExamDetails']);

Route::post('all/exam', [ExamController::class, 'getExamsForStudent']);

Route::get('/exams/batch/{batchId}', [ExamController::class, 'listExamsForBatch']);
Route::get('/exams/questions/{examId}', [ExamController::class, 'listQuestionsForExam']);
 

Route::post('/exam-responses', [ExamResponseController::class, 'storeExamResponse']);
Route::get('/calculate-marks/{examId}/{studentId}', [ExamResponseController::class, 'calculateMarks']);



// API Route for retrieving exam responses
Route::post('student/responses/mcq', [ExamResponseController::class, 'getResponsesByBatchAndStudent']);
Route::post('student/responses/short-answer', [ExamResponseController::class, 'gradeShortAnswerResponses']);

Route::post('/student-result', [ExamResponseController::class, 'getStudentResult']);
Route::post('all/student-result', [ExamResponseController::class, 'getStudentAllResult']);
Route::post('exam/student-result', [ExamResponseController::class, 'getAllStudentsResults']);

 
Route::post('zoom/create-meeting', [ZoomController::class, 'createMeeting']);
Route::delete('/zoom/meeting/{id}', [ZoomController::class, 'deleteMeeting']);
Route::put('/zoom/meeting/{id}', [ZoomController::class, 'updateMeeting']); 
Route::get('/zoom/meeting/{id}', [ZoomController::class, 'viewMeeting']);  

Route::post('/user/meetings', [ZoomController::class, 'getUserMeetings']);

Route::middleware(['api'])->group(function () {
Route::get('/zoom-meetings', [ZoomController::class, 'getAllMeetings']); 
}); 


Route::get('show/class-routines/{course_id}', [ClassRoutineController::class, 'showClassRoutine']);

// student Admin Staff

Route::get('student/view/update/{id}/', [StudentAuthController::class, 'UpdateView']);
Route::post('student/update/{id}', [StudentAuthController::class, 'updateStudent']);
Route::get('all/community', [BlogController::class, 'list']);

Route::get('dashboard-data', [PaymentGatewayController::class, 'dashboardaData']);

Route::get('student-overview', [PaymentGatewayController::class, 'fetchChartData']);
Route::get('student-chart-data', [PaymentGatewayController::class, 'getStudentOvervieww']);


Route::post('/invoices/download', [InvoiceController::class, 'getAllInvoicesByStudentDownload']);











