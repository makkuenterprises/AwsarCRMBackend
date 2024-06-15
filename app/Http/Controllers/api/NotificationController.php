<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Notification;


class NotificationController extends Controller
{
    public function create(Request $request){

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:1|max:255',
            'description' => ['required', 'string', 'min:1', 'max:250'],
            'sendTo' => ['required', 'string', 'min:1', 'max:250'],
        ]);
        if ($validator->fails()) {
            return response()->json([
             'status' => false,
               'code'=>400,
              'errors' => $validator->errors()
              ], 400);
        }
        try{
            $notification = new Notification();
            $notification->title = $request->input('title');
            $notification->description = $request->input('description');
            $notification->sendTo = $request->input('sendTo');
            $notification->save();
            return response()->json(['status'=>true,'code'=>200,'message' => 'Notification created successfully', 'notification' => $notification], 200);
            }catch (Exception $e) {
            $data = ['error' => $e->getMessage()];
            return response()->json(['status'=>false,'code'=>500,'message' => 'An error occurred while Created Notification', 'data' => $data], 500);
        }
         

    }

    public function list(){

       $notification = Notification::orderByDesc('id')->get();
       return response()->json(['status'=>true,'code'=>200,'data'=>$notification]);

    }
}
