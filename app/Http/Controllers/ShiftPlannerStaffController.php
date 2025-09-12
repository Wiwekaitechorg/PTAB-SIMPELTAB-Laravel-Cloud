<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Job;
use App\Requests;
use App\Shift;
use App\ShiftGroups;
use App\ShiftParent;
use App\ShiftPlannerStaff;
use App\ShiftPlannerStaffs;
use App\Staff;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ShiftPlannerStaffController extends Controller
{
    public function index(Request $request)
    {

        // abort_unless(\Gate::allows('duty_access'), 403);

        // $data = ShiftPlannerStaffs::select('shift_planner_staffs.id', 'start', 'end', DB::raw("CONCAT(shift_groups.title,'-',staffs.name) as title"))
        //     ->join('shift_groups', 'shift_planner_staffs.shift_group_id', '=', 'shift_groups.id')
        //     ->join('staffs', 'shift_planner_staffs.staff_id', '=', 'staffs.id')
        //     ->orderBy('shift_groups.queue', 'ASC')
        //     ->get();
        // dd($data);
        $shift_parent = ShiftParent::where('id', $request->id)->first();
        // dd($ShiftGroup);
        // if ($ShiftGroup == null) {
        //     dd('Buat Shift di menu shift group terlebih dahulu');
        // }
        // if ($ShiftGroup->job_id != "") {
        //     $data = ShiftPlannerStaffs::select('shift_planner_staffs.id', 'start', 'end', DB::raw("CONCAT(shift_groups.title,'-',staffs.name) as title"))
        //         ->join('shift_groups', 'shift_planner_staffs.shift_group_id', '=', 'shift_groups.id')
        //         ->join('work_types', 'work_types.id', '=', 'shift_groups.work_type_id')
        //         ->join('staffs', 'shift_planner_staffs.staff_id', '=', 'staffs.id')
        //         ->orderBy('shift_groups.queue', 'ASC')
        //         ->where('work_types.id', $request->id)
        //         ->where('shift_groups.job_id', '=', $ShiftGroup->job_id)
        //         ->get();
        // } else if ($ShiftGroup->work_unit_id != "") {
        //     $data = ShiftPlannerStaffs::select('shift_planner_staffs.id', 'start', 'end', DB::raw("CONCAT(shift_groups.title,'-',staffs.name) as title"))
        //         ->join('shift_groups', 'shift_planner_staffs.shift_group_id', '=', 'shift_groups.id')
        //         ->join('staffs', 'shift_planner_staffs.staff_id', '=', 'staffs.id')
        //         ->join('work_types', 'work_types.id', '=', 'shift_groups.work_type_id')
        //         ->orderBy('shift_groups.queue', 'ASC')
        //         ->where('work_types.id', $request->id)
        //         ->where('shift_groups.work_unit', '=', $ShiftGroup->work_unit_id)
        //         ->get();
        // } else if ($ShiftGroup->dapertement_id != "") {
        //     $data = ShiftPlannerStaffs::select('shift_planner_staffs.id', 'start', 'end', DB::raw("CONCAT(shift_groups.title,'-',staffs.name) as title"))
        //         ->join('shift_groups', 'shift_planner_staffs.shift_group_id', '=', 'shift_groups.id')
        //         ->join('staffs', 'shift_planner_staffs.staff_id', '=', 'staffs.id')
        //         ->join('work_types', 'work_types.id', '=', 'shift_groups.work_type_id')
        //         ->orderBy('shift_groups.queue', 'ASC')
        //         ->where('work_types.id', $request->id)
        //         ->where('shift_groups.dapertement_id', '=', $ShiftGroup->dapertement_id)
        //         ->get();
        // }
        // $data = ShiftPlannerStaffs::select('shift_planner_staffs.id', 'start', 'end', DB::raw("CONCAT(shift_groups.title,'-',staffs.name) as title"))
        //     ->join('shift_groups', 'shift_planner_staffs.shift_group_id', '=', 'shift_groups.id')
        //     ->join('work_types', 'work_types.id', '=', 'shift_groups.work_type_id')
        //     ->join('staffs', 'shift_planner_staffs.staff_id', '=', 'staffs.id')
        //     ->orderBy('shift_groups.queue', 'ASC')
        //     ->where('work_types.id', $request->id)
        //     ->FilterJob($ShiftGroup->job_id)
        //     ->FilterWorkUnit($ShiftGroup->work_unit_id)
        //     // ->where('shift_groups.job_id', '=', $ShiftGroup->job_id)
        //     ->get();
        // // $data = ShiftPlannerStaffs::select('shift_planner_staffs.id', 'start', 'end', DB::raw("CONCAT(shift_groups.title,'-',staffs.name) as title"))
        // $data = ShiftPlannerStaffs::select('shift_planner_staffs.id', 'start', 'end', DB::raw("CONCAT(shift_groups.title,'-',staffs.name) as title"))
        //     ->join('shift_groups', 'shift_planner_staffs.shift_group_id', '=', 'shift_groups.id')
        //     ->join('shift_parents', 'shift_parents.id', '=', 'shift_groups.shift_parent_id')
        //     ->join('staffs', 'shift_planner_staffs.staff_id', '=', 'staffs.id')
        //     ->orderBy('shift_groups.queue', 'ASC')
        //     ->where('shift_parents.id', $request->id)
        //     ->FilterJob($shift_parent->job_id)
        //     ->FilterWorkUnit($shift_parent->work_unit_id)
        //     ->FilterSubdapertement($shift_parent->subdapertement_id, $shift_parent->job_id)
        //     ->get();
        // dd($data);

        if ($request->ajax()) {
            $shift_parent = ShiftParent::where('id', $request->id)->first();
            // dd($ShiftGroup);
            // if ($ShiftGroup->job_id != "") {
            $data = ShiftPlannerStaffs::select('shift_planner_staffs.id', 'start', 'end', DB::raw("CONCAT(shift_groups.title,'-',staffs.name) as title"))
                ->join('shift_groups', 'shift_planner_staffs.shift_group_id', '=', 'shift_groups.id')
                ->join('shift_parents', 'shift_parents.id', '=', 'shift_groups.shift_parent_id')
                ->join('staffs', 'shift_planner_staffs.staff_id', '=', 'staffs.id')
                ->orderBy('shift_groups.queue', 'ASC')
                ->where('shift_parents.id', $request->id)

                ->FilterJob($shift_parent->job_id)
                ->FilterWorkUnit($shift_parent->work_unit_id)
                ->FilterSubdapertement($shift_parent->subdapertement_id, $shift_parent->job_id)
                // ->where('shift_groups.job_id', '=', $ShiftGroup->job_id)
                ->get();
            // } else if ($ShiftGroup->work_unit_id != "") {
            //     $data = ShiftPlannerStaffs::select('shift_planner_staffs.id', 'start', 'end', DB::raw("CONCAT(shift_groups.title,'-',staffs.name) as title"))
            //         ->join('shift_groups', 'shift_planner_staffs.shift_group_id', '=', 'shift_groups.id')
            //         ->join('staffs', 'shift_planner_staffs.staff_id', '=', 'staffs.id')
            //         ->join('work_types', 'work_types.id', '=', 'shift_groups.work_type_id')
            //         ->orderBy('shift_groups.queue', 'ASC')
            //         ->where('work_types.id', $request->id)
            //         ->where('shift_groups.work_unit', '=', $ShiftGroup->work_unit_id)
            //         ->get();
            // } else if ($ShiftGroup->dapertement_id != "") {
            //     $data = ShiftPlannerStaffs::select('shift_planner_staffs.id', 'start', 'end', DB::raw("CONCAT(shift_groups.title,'-',staffs.name) as title"))
            //         ->join('shift_groups', 'shift_planner_staffs.shift_group_id', '=', 'shift_groups.id')
            //         ->join('staffs', 'shift_planner_staffs.staff_id', '=', 'staffs.id')
            //         ->join('work_types', 'work_types.id', '=', 'shift_groups.work_type_id')
            //         ->orderBy('shift_groups.queue', 'ASC')
            //         ->where('work_types.id', $request->id)
            //         ->where('shift_groups.dapertement_id', '=', $ShiftGroup->dapertement_id)
            //         ->get();
            // }
            // $data = ShiftPlannerStaffs::select('shift_planner_staffs.id', 'start', 'end', DB::raw("CONCAT(shift_groups.title,'-',staffs.name) as title"))
            //     ->join('shift_groups', 'shift_planner_staffs.shift_group_id', '=', 'shift_groups.id')
            //     ->join('staffs', 'shift_planner_staffs.staff_id', '=', 'staffs.id')
            //     ->orderBy('shift_groups.queue', 'ASC')
            //     ->where('shift_groups.job_id', '=', $request->id)
            //     ->get();

            return response()->json($data);
        }
        $pgw = Staff::FilterJob($shift_parent->job_id)
            ->where('_status', '!=', 'non_active')
            ->FilterWorkUnit($shift_parent->work_unit_id)
            ->FilterSubdapertement($shift_parent->subdapertement_id, $shift_parent->job_id)
            ->where('staffs.work_type_id', '2')
            ->get();
        $sg = ShiftGroups::where('shift_parent_id', $request->id)->get();
        $id = $shift_parent->job_id;


        return view('admin.shiftstaff.index', compact('pgw', 'sg', 'id', 'shift_parent'));
    }
    public function create(Request $request)
    {
        abort_unless(\Gate::allows('duty_create'), 403);
        $type = $request->type;
        $users = User::where('staff_id', '!=', '0')->orderBy('name')->get();
        return view('admin.duty.create', compact('users', 'type'));
    }
    public function store(Request $request)
    {
        abort_unless(\Gate::allows('duty_create'), 403);
        $duty = Requests::create($request->all());
        return redirect()->route('admin.duty.index');
    }

    public function edit(Request $request)
    {
        abort_unless(\Gate::allows('duty_edit'), 403);
        // $requests = ShiftPlannerStaffs::select('shift_groups.job_id', 'shift_parent_id', 'shift_planner_staffs.id', 'start', 'end', DB::raw("CONCAT(shift_groups.title,'-',staffs.name) as title"))
        //     ->join('shift_groups', 'shift_planner_staffs.shift_group_id', '=', 'shift_groups.id')
        //     ->join('staffs', 'shift_planner_staffs.staff_id', '=', 'staffs.id')
        //     ->where('shift_planner_staffs.id', $request->id)->first();
        // dd($requests);
        // $shift_parent = ShiftParent::where('id', $request->shift_parent_id)->first();
        // $pgw = Staff::FilterJob($shift_parent->job_id)
        //     ->FilterWorkUnit($shift_parent->work_unit_id)->get();
        // dd($pgw);
        if ($request->ajax()) {
            $data = [];
            $staff = "";

            $requests = ShiftPlannerStaffs::select('shift_groups.job_id', 'shift_parent_id', 'shift_planner_staffs.staff_id', 'shift_planner_staffs.id', 'start', 'end', DB::raw("CONCAT(shift_groups.title,'-',staffs.name) as title"))
                ->join('shift_groups', 'shift_planner_staffs.shift_group_id', '=', 'shift_groups.id')
                ->join('staffs', 'shift_planner_staffs.staff_id', '=', 'staffs.id')
                ->where('shift_planner_staffs.id', $request->id)->first();
            $shift_parent = ShiftParent::where('id', $requests->shift_parent_id)->first();
            $pgw = Staff::select('staffs.*')->FilterJob($shift_parent->job_id)
                ->where('_status', '!=', 'non_active')
                ->FilterWorkUnit($shift_parent->work_unit_id)
                ->FilterSubdapertement($shift_parent->subdapertement_id, $shift_parent->job_id)
                ->where('staffs.work_type_id', '2')
                ->get();
            foreach ($pgw as $value) {
                if ($requests->staff_id == $value->id) {
                    $select = "selected";
                } else {
                    $select = "";
                }
                $staff = $staff . "<option " . $select . " value='" . $value->id . "'> " . $value->name . " </option>";
            }
            $data = [$staff, $requests];
            return response()->json($data);
        }
        // $detail = Requests::where('id', $request->id)->first();
        // $users = User::where('staff_id', '!=', '0')->orderBy('name')->get();
        // return view('admin.duty.edit', compact('users', 'type', 'detail'));
    }

    public function check(Request $request)
    {
        abort_unless(\Gate::allows('duty_edit'), 403);

        if ($request->ajax()) {
            $data = [];
            $staff = "";
            $requests = Requests::whereDate('start', '=', $request->start)->Requests::whereDate('start', '=', $request->start)->first();
            $pgw = Staff::get();
            foreach ($pgw as $value) {
                if ($requests->user_id === $value->id) {
                    $select = "selected";
                } else {
                    $select = "";
                }
                $staff = $staff . "<option " . $select . " value='" . $value->id . "'> " . $value->id . " </option>";
            }
            $data = [$staff, $requests];
            return response()->json($data);
        }
        // $detail = Requests::where('id', $request->id)->first();
        // $users = User::where('staff_id', '!=', '0')->orderBy('name')->get();
        // return view('admin.duty.edit', compact('users', 'type', 'detail'));
    }
    public function update(Request $request, $id)
    {
        abort_unless(\Gate::allows('duty_edit'), 403);
        $duty = Requests::where('id', $id)
            ->update($request->except(['_token', '_method']));
        return redirect()->route('admin.duty.index');
    }


    public function reject($id)
    {
        abort_unless(\Gate::allows('duty_edit'), 403);
        $d = Requests::where('id', $id)
            ->update(['status' => 'reject']);
        return redirect()->route('admin.duty.index');
    }
    public function approve($id)
    {
        abort_unless(\Gate::allows('duty_edit'), 403);
        $d = Requests::where('id', $id)
            ->update(['status' => 'approve']);
        return redirect()->back();
    }

    public function destroy($id)
    {
        abort_unless(\Gate::allows('duty_delete'), 403);
        Requests::where('id', $id)->delete();
        return redirect()->back();
    }

    public function index1(Request $request)
    {
        if ($request->ajax()) {
            $data = Requests::whereDate('start', '>=', $request->start)
                ->whereDate('end',   '<=', $request->end)
                ->get(['id', 'title', 'start', 'end']);
            return response()->json($data);
        }
        return view('full-calender');
    }

    public function action(Request $request)
    {
        if ($request->ajax()) {
            if ($request->type == 'add') {
                $cek = ShiftPlannerStaffs::whereDate('start', $request->start)
                    ->where('staff_id', $request->staff_id)
                    ->where('shift_group_id', $request->shift_group_id)
                    ->first();
                if ($cek) {
                    $event = "fail";
                    // } else if (date("Y-m-d",  strtotime($request->start)) <= date('Y-m-d')) {
                    //     $event = "fail";
                } else {
                    $ShiftGroup = ShiftGroups::where('id', $request->shift_group_id)->first();
                    $time = date("Y-m-d 0" . $ShiftGroup->queue . ":i:s",  strtotime($request->start));
                    $event = ShiftPlannerStaffs::create([
                        'staff_id'        =>    $request->staff_id,
                        'shift_group_id' => $ShiftGroup->id,
                        'start'        =>     $time,
                        'end'        =>     $time
                    ]);
                }

                // $event = "fail";
                return response()->json($event);
            }

            if ($request->type == 'update') {
                // $ShiftGroup = ShiftGroups::where('id', $request->shift_group_id)->first();
                $event = ShiftPlannerStaffs::find($request->id)->update([
                    'staff_id'        =>    $request->staff_id,

                    // 'end'        =>    $request->end
                ]);

                return response()->json($event);
            }

            if ($request->type == 'updateD') {

                $item = ShiftPlannerStaffs::where('id', $request->id)->first();
                $cek = ShiftPlannerStaffs::where('start', '=', $request->start)
                    ->where('staff_id', $item->staff_id)
                    ->where('shift_group_id', $item->shift_group_id)
                    ->first();
                if ($cek) {
                    $event = "fail";
                } else if (date("Y-m-d",  strtotime($request->start)) <= date('Y-m-d')) {
                    $event = "fail";
                } else if ($item->start <= date('Y-m-d')) {
                    $event = "fail";
                } else {
                    $event = ShiftPlannerStaffs::find($request->id)->update([
                        // 'staff_id'        =>    $request->staff_id,
                        'start'        =>    $request->start,
                        'end'  => $request->end,
                        // 'end'        =>    $request->end
                    ]);
                }

                return response()->json($event);
            }

            if ($request->type == 'delete') {
                $event = ShiftPlannerStaffs::find($request->id)->delete();

                return route('admin.duty.index');
                // return response()->json($event);
            }
        }
    }
}
