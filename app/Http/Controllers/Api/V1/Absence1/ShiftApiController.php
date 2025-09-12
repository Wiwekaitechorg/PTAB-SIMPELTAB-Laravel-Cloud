<?php

namespace App\Http\Controllers\Api\V1\Absence1;

use App\Http\Controllers\Controller;
use App\Shift;
use App\ShiftGroups;
use App\ShiftPlannerStaffs;
use Illuminate\Http\Request;

class ShiftApiController extends Controller
{
    public function index(Request $request)
    {
        $data = [];
        $data2 = [];
        $staff_list = ShiftPlannerStaffs::selectRaw('shift_planner_staffs.id ,staffs.name as staff_name')
            ->join('staffs', 'staffs.id', '=', 'shift_planner_staffs.staff_id')
            ->where('shift_group_id', $request->shift_id)
            ->whereDate('start', '=', $request->start)
            ->whereDate('staff_id', '!=', $request->staff_id)
            ->get();

        $shift_list = ShiftGroups::get();

        foreach ($staff_list as $key => $value) {
            $data[] = ['id' => $value->id, 'name' => $value->staff_name, 'checked' => false];
        }

        foreach ($shift_list as $key => $value) {
            $data2[] = ['id' => $value->id, 'name' => $value->title, 'checked' => false];
        }
        return response()->json([
            'message' => 'success',
            'data' => $data,
            'data2' => $data2,
        ]);
    }

    public function listChange(Request $request)
    {
        $staff_list = ShiftPlannerStaffs::selectRaw('shift_planner_staffs.id ,staff.name as staff_name, staff_change.name as staffc_name, shifts.*, shift_planner_staffs.*')
            ->join('shifts', 'shifts.id', '=', 'shift_planner_staffs.shift_group_id')
            ->join('staffs as staff', 'staff.id', '=', 'shift_planner_staffs.staff_id')
            ->join('staffs as staff_change', 'staff_change.id', '=', 'shift_planner_staffs.change_staff_id')
            ->where('change_staff_id', $request->staff_id)
            ->get();

        return response()->json([
            'message' => 'pengajuan berhasil',
            'data' => $staff_list,
        ]);
    }

    public function update(Request $request)
    {
        $dataForm = json_decode($request->form);
        $change = [];
        $staff_list = ShiftPlannerStaffs::selectRaw('shift_planner_staffs.id ,staff.name as staff_name, staff_change.name as staffc_name')
            ->join('staffs as staff', 'staff.id', '=', 'shift_planner_staffs.staff_id')
            ->join('staffs as staff_change', 'staff_change.id', '=', 'shift_planner_staffs.change_staff_id')
            ->where('shift_planner_staffs.id', $dataForm->id)
            ->first();
        if ($staff_list) {
            return response()->json([
                'message' => 'pengajuan berhasil',
                'data' => $change,
            ]);
        } else {
            $change = ShiftPlannerStaffs::where('id', $dataForm->id)->update(['change_staff_id' => $dataForm->staff_id, 'description' => $dataForm->description]);
            return response()->json([
                'message' => 'pengajuan berhasil',
                'data' => $change,
            ]);
        }
    }

    public function approve(Request $request)
    {
        $change = ShiftPlannerStaffs::where('id', $request->id)->update(['staff_id' => $request->staff_id, 'change_staff_id' => '']);
        return response()->json([
            'message' => 'pengajuan berhasil',
            'data' => $change,
        ]);
    }

    public function myShift(Request $request)
    {
        $staff_list = ShiftPlannerStaffs::selectRaw('shifts.time_in, shifts.time_out, shifts.title, shift_planner_staffs.id, shift_planner_staffs.date, shift_planner_staffs.id ,staff.name as staff_name')
            ->join('staffs as staff', 'staff.id', '=', 'shift_planner_staffs.staff_id')
            ->join('shifts', 'shifts.id', '=', 'shift_planner_staffs.shift_group_id')
            ->where('shift_planner_staffs.staff_id', $request->staff_id)
            ->get();
        return response()->json([
            'message' => 'success',
            'data' =>  $staff_list,
        ]);
    }
}
