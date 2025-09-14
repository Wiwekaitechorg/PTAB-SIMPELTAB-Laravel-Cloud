<?php

namespace App\Http\Controllers\Api\V1\Absence;

use App\Http\Controllers\Controller;
use App\ShiftChange;
use Illuminate\Http\Request;

class ShiftChangeApiController extends Controller
{
    public function index(Request $request)
    {
        $shiftChange = ShiftChange::selectRaw('shiftsA.date, shifts.title, shifts.time_in, shifts.time_out, usersA.name, shift_changes.status, shiftsC.date as c_date, shiftsCC.title as c_title, shiftsCC.time_in as C_time_in, shiftsCC.time_out as C_time_out')
            ->leftJoin('shift_staff as shiftsA', 'shiftsA.id', '=', 'shift_changes.shift_id')
            ->leftJoin('shift_staff as shiftsC', 'shiftsC.id', '=', 'shift_changes.shift_change_id')
            ->leftJoin('shifts', 'shifts.id', '=', 'shiftsA.shift_id')
            ->leftJoin('shifts as shiftsCC', 'shiftsCC.id', '=', 'shiftsC.shift_id')
            ->leftJoin('users as usersA', 'usersA.id', '=', 'shiftsA.staff_id')
            ->where('shiftsC.staff_id', $request->id)
            ->get();
        return response()->json([
            'message' => 'pengajuan berhasil',
            'data' =>  $shiftChange,
        ]);
    }

    public function changeShift(Request $request)
    {
        $shiftChange = ShiftChange::selectRaw('C.title as title1, D.title as title2, A.start as start1, B.start as start2, shift_changes.id as id, shift_changes.status as status ')
            ->join('shift_planner_staffs as A', 'shift_changes.shift_change_id', '=', 'A.id')
            ->join('shift_groups as C', 'A.shift_group_id', '=', 'C.id')
            ->join('shift_planner_staffs as B', 'shift_changes.shift_id', '=', 'B.id')
            ->join('shift_groups as D', 'B.shift_group_id', '=', 'D.id')
            ->FilterDate($request->from, $request->to)
            ->where('A.staff_id', $request->staff_id)
            ->paginate(3, ['*'], 'page', $request->page);

        return response()->json([
            'message' => 'daftar persetujuan',
            'data' =>  $shiftChange,
        ]);
    }


    public function changeShiftProposal(Request $request)
    {
        $shiftChange = ShiftChange::selectRaw('C.title as title1, D.title as title2, A.start as start1, B.start as start2, shift_changes.id as id, shift_changes.status as status ')
            ->join('shift_planner_staffs as A', 'shift_changes.shift_change_id', '=', 'A.id')
            ->join('shift_groups as C', 'A.shift_group_id', '=', 'C.id')
            ->join('shift_planner_staffs as B', 'shift_changes.shift_id', '=', 'B.id')
            ->join('shift_groups as D', 'B.shift_group_id', '=', 'D.id')
            ->FilterDate($request->from, $request->to)
            ->where('B.staff_id', $request->staff_id)
            ->paginate(3, ['*'], 'page', $request->page);

        return response()->json([
            'message' => 'daftar pengajuan',
            'data' =>  $shiftChange,
        ]);
    }

    public function changeShiftApprove(Request $request)
    {
        $shiftChange = ShiftChange::where('id', $request->id)->first();
        $shiftChange->update(
            [
                'status' => 'approve'
            ]
        );

        return response()->json([
            'message' => 'daftar pengajuan',
            'data' =>  $shiftChange,
        ]);
    }


    public function store(Request $request)
    {
        $dataForm = json_decode($request->form);
        // if ($dataForm->date > date('Y-m-d')) {
        $data = [
            'shift_id' => $dataForm->id,
            'shift_change_id' => $dataForm->shift_change_id,
            'description' => $dataForm->description,
            'status' => 'pending',
        ];
        $shiftChange = ShiftChange::create($data);
        return response()->json([
            'message' => 'pengajuan berhasil',
            'data' =>  $shiftChange,
        ]);
        // } else {
        //     return response()->json([
        //         'message' => 'failed',
        //     ]);
        // }
    }
}
