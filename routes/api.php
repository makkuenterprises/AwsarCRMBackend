<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AdminAuthController;
use App\Http\Controllers\api\StudentAuthController; 
use App\Http\Controllers\api\TeacherAuthController;
use App\Http\Controllers\api\StaffAuthController;
use App\Http\Controllers\api\CourseController;
use App\Http\Controllers\api\NotificationController;
use App\Http\Controllers\api\MeetingCreateController;
use App\Http\Controllers\api\AttendanceController;
use App\Http\Controllers\api\CourseEnrollementController;
use App\Http\Controllers\api\StudyMaterialsController;
use App\Http\Controllers\api\DetailsController;



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

Route::post('/login',[AdminAuthController::class,'adminAuthLogin']);
Route::post('/logout',[AdminAuthController::class,'adminAuthLogout']);
Route::get('/view/profile/update/{id}', [AdminAuthController::class, 'profileUpdateView']);
Route::post('/profile/update/{id}', [AdminAuthController::class, 'profileUpdate']);
Route::post('/password/update', [AdminAuthController::class, 'passwordUpdate']);

});





// Route::middleware(['admin'])->group(function () {

// ------------------------------------------------------------------------------------------------
// STUDENT CREATE ROUTES
// ------------------------------------------------------------------------------------------------

 Route::prefix('student')->group(function () {
        Route::post('/register', [StudentAuthController::class, 'StudentCreate']);
        Route::get('/list', [StudentAuthController::class, 'StudentList']);
        Route::get('view/update/{id}/', [StudentAuthController::class, 'UpdateView']);
        Route::post('update/{id}', [StudentAuthController::class, 'updateStudent']);
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
// NOTIFICATION ROUTES
// ------------------------------------------------------------------------------------------------

Route::prefix('notification')->group(function () {

      Route::post('/create', [NotificationController::class, 'create']);
      Route::get('/list', [NotificationController::class, 'List']);

}); 

// });

// ------------------------------------------------------------------------------------------------
// COURSE ENROLL ROUTES
// ------------------------------------------------------------------------------------------------


Route::post('course/enroll', [CourseEnrollementController::class, 'enrollCourse']);

// ------------------------------------------------------------------------------------------------
// STUDENT PANEL ROUTES
// ------------------------------------------------------------------------------------------------

Route::prefix('student')->group(function () {
Route::post('/login',[StudentAuthController::class,'studentAuthLogin']);
});

Route::prefix('student')->group(function () {

      Route::post('/logout',[StudentAuthController::class,'studentAuthLogout']);
      Route::get('/view/profile/update/{id}', [StudentAuthController::class, 'profileUpdateView']);
      Route::post('/profile/update/{id}', [StudentAuthController::class, 'profileUpdate']);
      Route::post('/password/update', [StudentAuthController::class, 'passwordUpdate']);

}); 



 // ------------------------------------------------------------------------------------------------
// TEACHER PANEL ROUTES
// ------------------------------------------------------------------------------------------------   

Route::prefix('teacher')->group(function () {

      Route::post('/login',[TeacherAuthController::class,'teacherAuthLogin']);

});  

Route::prefix('teacher')->group(function () {

      Route::post('/logout',[TeacherAuthController::class,'teacherAuthLogout']);
      Route::get('/view/profile/update/{id}', [TeacherAuthController::class, 'profileUpdateView']);
      Route::post('/profile/update/{id}', [TeacherAuthController::class, 'profileUpdate']);
      Route::post('/password/update', [TeacherAuthController::class, 'passwordUpdate']);


}); 

 


// ------------------------------------------------------------------------------------------------
// STAFF PANEL ROUTES
// ------------------------------------------------------------------------------------------------

Route::prefix('staff')->group(function () {
      Route::post('/login',[StaffAuthController::class,'staffAuthLogin']);
}); 

Route::prefix('staff')->group(function () {

      Route::post('/logout',[StaffAuthController::class,'staffAuthLogout']);
      Route::get('/view/profile/update/{id}', [StaffAuthController::class, 'profileUpdateView']);
      Route::post('/profile/update/{id}', [StaffAuthController::class, 'profileUpdate']);
      Route::post('/password/update', [StaffAuthController::class, 'passwordUpdate']);

}); 




// ------------------------------------------------------------------------------------------------
// MEETING ROUTES
// ------------------------------------------------------------------------------------------------

Route::prefix('meeting')->group(function () {
 
      Route::post('/create', [MeetingCreateController::class, 'create']);
     
});


// ------------------------------------------------------------------------------------------------
// DETAILS ADD
// ------------------------------------------------------------------------------------------------


Route::post('details/add', [DetailsController::class, 'index']);
Route::get('details/view/update/{id}', [DetailsController::class, 'show']);
Route::post('details/update/{id}', [DetailsController::class, 'update']);


// ------------------------------------------------------------------------------------------------
// ATTENDANCE ROUTE
// ------------------------------------------------------------------------------------------------

 
Route::prefix('attedence')->group(function () {

      Route::get('/list/{id}', [AttendanceController::class, 'getStudents']);
      Route::post('/submit-attendance', [AttendanceController::class, 'create']);
      Route::get('/list', [AttendanceController::class, 'alllist']);
});


// ------------------------------------------------------------------------------------------------
// STUDY MATERIALS UPLOAD
// ------------------------------------------------------------------------------------------------

Route::post('study-material/upload', [StudyMaterialsController::class, 'store']);
Route::get('study-materials', [StudyMaterialsController::class, 'index']); 
Route::get('student/study-materials/{course_id}', [StudyMaterialsController::class, 'studentMaterials']);
// Route::get('study-material/download/{id}/{filePath}', [StudyMaterialsController::class, 'downloadMaterial']);
Route::post('study-material/download', [StudyMaterialsController::class, 'downloadMaterial']);

      




