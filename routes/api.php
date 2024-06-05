<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AdminAuthController;
use App\Http\Controllers\api\StudentAuthController; 
use App\Http\Controllers\api\TeacherAuthController;


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

// Route::get('/',function(){
//     return response()->json(["status"=>true, 'code'=>200, "data"=>"Hello Simran"]);
// });

Route::prefix('admin')->group(function () {
Route::post('/login',[AdminAuthController::class,'adminAuthLogin']);
Route::post('/logout',[AdminAuthController::class,'adminAuthLogout']);

});

Route::prefix('student')->group(function () {
Route::post('/login',[StudentAuthController::class,'studentAuthLogin']);
Route::post('/logout',[StudentAuthController::class,'studentAuthLogout']);
});

// Route::middleware(['admin'])->group(function () {
 Route::prefix('student')->group(function () {
        Route::post('/register', [StudentAuthController::class, 'StudentCreate']);
        Route::post('/list', [StudentAuthController::class, 'StudentList']);
        Route::get('view/update/{id}/', [StudentAuthController::class, 'UpdateView']);
        Route::post('update/{id}', [StudentAuthController::class, 'updateStudent']);
        // Route::put('update/{id}', [StudentAuthController::class, 'updateStudent']);
        Route::delete('delete/{id}', [StudentAuthController::class, 'deleteStudent']);
    });
// });



Route::prefix('teacher')->group(function () {

Route::post('/login',[TeacherAuthController::class,'teacherAuthLogin']);
Route::post('/logout',[TeacherAuthController::class,'teacherAuthLogout']);

      Route::post('/register', [TeacherAuthController::class, 'teacherCreate']);
      Route::put('/{id}', [TeacherAuthController::class, 'updateTeacher']);
      Route::delete('/{id}', [TeacherAuthController::class, 'deleteTeacher']);

});      

