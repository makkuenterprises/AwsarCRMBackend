<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('clear',function() {
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('event:clear');
    Artisan::call('optimize:clear');
    Artisan::call('queue:clear');
    dd('Application Cache Cleared');
});

Route::get('setup',function() {
    Artisan::call('migrate');
    Artisan::call('db:seed');
    Artisan::call('storage:link');
    dd('Application Setup Completed');
});
Route::get('migrate',function() {
    
    Artisan::call('storage:link');
    Artisan::call('migrate');
    dd('Application Migration Completed');
});
Route::get('rollback', function() {
    try {
        // Call the artisan command and capture the exit code
        $exitCode = Artisan::call('migrate:rollback', ['--step' => 1]);
        
        // Get the output of the command
        $output = Artisan::output();

        // Check if the command was successful
        if ($exitCode === 0) {
            return response()->json(['message' => 'Application rollback completed', 'output' => $output], 200);
        } else {
            return response()->json(['message' => 'Rollback failed', 'output' => $output], 500);
        }
    } catch (\Exception $e) {
        // Log the exception
        Log::error('Rollback failed: ' . $e->getMessage());

        return response()->json(['message' => 'Rollback failed', 'error' => $e->getMessage()], 500);
    }
});
 
