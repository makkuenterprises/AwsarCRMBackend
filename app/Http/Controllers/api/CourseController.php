<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Course;

class CourseController extends Controller
{
    public function courseCreate(Request $request){
          $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'fee' => 'required|string|max:255|',
            'startDate' => ['required', 'date', 'max:250'],
            'endDate' => ['required', 'date', 'max:250'],
            'modeType' => ['required', 'string', 'min:1', 'max:250'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        try{
            $course = new Course();
            $course->name = $request->input('name');
            $course->fee = $request->input('fee');
            $course->startDate = $request->input('startDate');
            $course->endDate = $request->input('endDate');
            $course->modeType = $request->input('modeType');
            $course->save();
             return response()->json(['message' => 'Course created successfully', 'course' => $course], 201);
              }catch (Exception $e) {
          $data = ['error' => $e->getMessage()];
         return response()->json(['message' => 'An error occurred while Created Course', 'data' => $data], 500);
        }
         
    }
}
