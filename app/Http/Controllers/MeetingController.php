<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ZoomService;
 
class MeetingController extends Controller
{
    protected $zoomService;

    public function __construct(ZoomService $zoomService)
    {
        $this->zoomService = $zoomService;
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'topic'      => 'required|string',
            'start_time' => 'required|date',
            'duration'   => 'required|integer',
            'agenda'     => 'nullable|string',
        ]);

        $meeting = $this->zoomService->createMeeting($data);

        return response()->json($meeting);
    }
}