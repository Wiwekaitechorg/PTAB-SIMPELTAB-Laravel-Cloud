<?php

namespace App\Http\Controllers\Api\V1\Absence;

use App\Absence;
use App\Holiday;
use App\Http\Controllers\Controller;
use App\MessageLog;
use App\Requests;
use App\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuApiController extends Controller
{

    public function index(Request $request)
    {
        // $holiday = Holiday::selectRaw('count(id) as id')->where('date', date('Y-m-d'))->first()->id;
        // $workDay = $this->countDays(date('Y'), date('n'), array(0, 6)) - $holiday;
        // $menu = Absence::selectRaw('(SUM(value)/2)/"' . $workDay . '" as persentage, SUM(value) as total')
        //     ->groupBy(DB::raw('MONTH(register)'))
        //     ->whereBetween('register', [date("Y-n-d", strtotime('-4 month', strtotime(date('Y-m-01')))), date("Y-n-d", strtotime('+1 month', strtotime(date('Y-m-01'))))])
        //     ->where('user_id', $request->user_id)
        //     ->get();
        // $duty = Requests::where('category', 'duty')->whereDate('date', '=', date('Y-m-d'))->where('user_id', $request->user_id)->where('status', 'approve')->first();
        // $extra = Requests::where('category', 'extra')->whereDate('date', '=', date('Y-m-d'))->where('user_id', $request->user_id)->where('status', 'approve')->first();
        // $permit = Requests::where('category', 'permit')->whereDate('date', '=', date('Y-m-d'))->where('user_id', $request->user_id)->where('status', 'approve')->first();
        $staff = Staff::selectRaw('staffs.*, work_types.type, users.email, work_units.lng, work_units.lat, work_units.radius ')
            ->join('work_types', 'staffs.work_type_id', '=', 'work_types.id')
            ->join('users', 'users.staff_id', '=', 'staffs.id')
            ->join('work_units', 'work_units.id', '=', 'staffs.work_unit_id')
            ->where('staffs.id', $request->staff_id)->first();
        // $messageLogs = MessageLog::where('staff_id', $request->staff_id)->get();
        $messageLogs = MessageLog::where('staff_id', $request->staff_id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'DESC')->get();
        if (count($messageLogs) <= 0) {
            $messageCount = "";
            $messageM = "Tidak Ada Pesan Baru";
        } else {
            $messageCount = count($messageLogs);
            if ($messageCount > 10) {
                $messageCount = "10+";
            } else {
                $messageCount = "" . count($messageLogs);
            }
            $messageM = $messageLogs[0]->memo;
        }
        return response()->json([
            'message' => 'Success',
            // 'messageLog' => $messageLogs,
            'messageCount' => $messageCount,
            'staff' => $staff,
            'messageM' => $messageM,
            'month1' => 60,
            'month2' => 75,
            'month3' => 80

        ]);
    }

    // untuk nanti di API
    function countDays($year, $month, $ignore)
    {
        $count = 0;
        $counter = mktime(0, 0, 0, $month, 1, $year);
        while (date("n", $counter) == $month) {
            if (in_array(date("w", $counter), $ignore) == false) {
                $count++;
            }
            $counter = strtotime("+1 day", $counter);
        }
        return  $count;
    }
    public function test()
    {
        echo $this->countDays(date('Y'), date('n'), array(0, 6)); // 23
    }
}
