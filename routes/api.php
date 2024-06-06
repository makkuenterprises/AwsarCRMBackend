<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AdminAuthController;
use App\Http\Controllers\api\StudentAuthController; 
use App\Http\Controllers\api\TeacherAuthController;
use App\Http\Controllers\api\StaffAuthController;
use App\Http\Controllers\api\CourseController;
use App\Http\Controllers\api\NotificationController;


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

Route::prefix('admin')->group(function () {

Route::post('/login',[AdminAuthController::class,'adminAuthLogin']);
Route::post('/logout',[AdminAuthController::class,'adminAuthLogout']);
Route::get('/view/profile/update/{id}', [AdminAuthController::class, 'profileUpdateView']);
Route::post('/profile/update/{id}', [AdminAuthController::class, 'profileUpdate']);

});

Route::prefix('student')->group(function () {
Route::post('/login',[StudentAuthController::class,'studentAuthLogin']);
Route::post('/logout',[StudentAuthController::class,'studentAuthLogout']);
});

// Route::middleware(['admin'])->group(function () {
 Route::prefix('student')->group(function () {
        Route::post('/register', [StudentAuthController::class, 'StudentCreate']);
        Route::get('/list', [StudentAuthController::class, 'StudentList']);
        Route::get('view/update/{id}/', [StudentAuthController::class, 'UpdateView']);
        Route::post('update/{id}', [StudentAuthController::class, 'updateStudent']);
        Route::delete('delete/{id}', [StudentAuthController::class, 'deleteStudent']);
    });
// });


 
Route::prefix('teacher')->group(function () {
    Route::post('/login',[TeacherAuthController::class,'teacherAuthLogin']);
    Route::post('/logout',[TeacherAuthController::class,'teacherAuthLogout']);
});  

Route::prefix('teacher')->group(function () {
      Route::post('/register', [TeacherAuthController::class, 'teacherCreate']);
      Route::get('/list', [TeacherAuthController::class, 'teacherList']);
      Route::get('view/update/{id}/', [TeacherAuthController::class, 'UpdateView']);
      Route::post('update/{id}', [TeacherAuthController::class, 'updateTeacher']);
      Route::delete('delete/{id}', [TeacherAuthController::class, 'deleteTeacher']);
});      

Route::prefix('staff')->group(function () {
      Route::post('/login',[StaffAuthController::class,'staffAuthLogin']);
      Route::post('/logout',[StaffAuthController::class,'staffAuthLogout']);
});  

Route::prefix('staff')->group(function () {
      Route::post('/register', [StaffAuthController::class, 'staffCreate']);
      Route::get('/list', [StaffAuthController::class, 'staffList']);
      Route::get('view/update/{id}/', [StaffAuthController::class, 'UpdateView']);
      Route::post('update/{id}', [StaffAuthController::class, 'updateStaff']);
      Route::delete('delete/{id}', [StaffAuthController::class, 'deleteStaff']);
}); 

Route::prefix('course')->group(function () {
      Route::post('/create', [CourseController::class, 'courseCreate']);
      Route::get('/list', [CourseController::class, 'courseList']);
      Route::get('view/update/{id}/', [CourseController::class, 'UpdateView']);
      Route::post('update/{id}', [CourseController::class, 'courseUpdate']);
      Route::delete('delete/{id}', [CourseController::class, 'deleteCourse']);
}); 

Route::prefix('notification')->group(function () {
      Route::post('/create', [NotificationController::class, 'create']);
      Route::get('/list', [NotificationController::class, 'List']);
}); 
