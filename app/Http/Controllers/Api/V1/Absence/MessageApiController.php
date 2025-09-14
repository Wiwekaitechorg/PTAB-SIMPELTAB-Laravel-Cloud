<?php

namespace App\Http\Controllers\Api\V1\Absence;

use App\Http\Controllers\Controller;
use App\MessageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageApiController extends Controller
{
    public function index(Request $request)
    {
        $message_log =  MessageLog::where('staff_id', $request->staff_id)

            ->where(function ($query) {
                $query->where('lat', '=', null)
                    ->orWhere('lat', '=', '');
                // ->orWhere('status', 'close');
            })
            ->orderBy(DB::raw("FIELD(status ,\"pending\", \"read\")"))
            ->orderBy('created_at', 'DESC')
            ->where('created_at', '<=', date('Y-m-d H:i:s'))
            ->paginate(3, ['*'], 'page', $request->page);
        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'data' => $message_log,
        ]);
    }
    public function read(Request $request)
    {
        $message_log = MessageLog::where('id', $request->id)->first();
        // return response()->json([
        //     'message' => 'Pengajuan Terkirim',
        //     'data' => $message_log,
        // ]);
        $message_log->status = "read";
        $message_log->save();
        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'data' => $message_log,
        ]);
    }

    public function check(Request $request)
    {
        $message_log = MessageLog::where('id', $request->id)->first();
        $message_log->update([
            'status' => 'read',
            'lat' => $request->lat,
            'lng' => $request->lng
        ]);
        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'data' => $message_log,
        ]);
    }
}
