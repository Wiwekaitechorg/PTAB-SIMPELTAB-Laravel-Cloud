<?php

namespace App\Http\Controllers\Api\V1\Absence;

use App\Absence;
use App\AbsenceLog;
use App\Http\Controllers\Controller;
use App\Shift;
use App\ShiftChange;
use App\ShiftPlannerStaff;
use App\ShiftPlannerStaffs;
use App\Staff;
use App\StaffApi;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\WablasTrait;
use App\wa_history;
use OneSignal;

class ShiftChangeApiController extends Controller
{
    use WablasTrait;
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
            ->where('shift_changes.staff_change_id', $request->staff_id)
            // ->where('shift_changes.status', '!=', 'approve')
            // ->where('shift_changes.status', '!=', 'reject')
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
            ->where('shift_changes.staff_id', $request->staff_id)
            // ->where('shift_changes.status', '!=', 'approve')
            // ->where('shift_changes.status', '!=', 'reject')
            ->paginate(3, ['*'], 'page', $request->page);

        return response()->json([
            'message' => 'daftar pengajuan',
            'data' =>  $shiftChange,
        ]);
    }

    public function changeShiftApprove(Request $request)
    {
        $shiftChange = ShiftChange::where('id', $request->id)->first();
        // $shiftChange->update(
        //     [
        //         'status' => $request->status
        //     ]
        // );
        // if ($request->status == "reject") {
        //     $message = "Ditolak";
        // } else {
        //     // penukaran Shift start
        //     $shift_changes = ShiftChange::where('id', $request->id)->first();
        //     // shift saya
        //     $shift1 = ShiftPlannerStaffs::where('id',  $shift_changes->shift_change_id)->first();
        //     $staff_id1 = $shift1->staff_id;
        //     // shift yang ditukar
        //     $shift2 = ShiftPlannerStaffs::where('id',  $shift_changes->shift_id)->first();
        //     $staff_id2 = $shift2->staff_id;
        //     $shift1->update([
        //         'staff_id' => $staff_id2
        //     ]);
        //     $shift2->update([
        //         'staff_id' => $staff_id1
        //     ]);
        //     $shift_changes->update([
        //         'status' => 'approve'
        //     ]);
        //     // penukaran shift end
        //     $message = "Diterima";
        // }

        return response()->json([
            'message' => $message,
            'data' =>  $shiftChange,
            // 'llll' => $request->status,
            // 'sskks' => $request->id
        ]);
    }


    public function store(Request $request)
    {
        $dataForm = json_decode($request->form);
        // $dataForm = $request;

        $shift1 = ShiftPlannerStaffs::where('id', $dataForm->shift_change_id)
            ->first();
        $shift2 = ShiftPlannerStaffs::where('id', $dataForm->id)
            ->first();

        // if ($dataForm->date > date('Y-m-d')) {
        $data = [
            'shift_id' => $dataForm->shift_change_id,
            'shift_change_id' =>  $dataForm->id,
            'description' => $dataForm->description,
            'staff_id' => $shift1->staff_id,
            'staff_change_id' => $shift2->staff_id,
            'status' => 'pending',
        ];
        $shiftChange = ShiftChange::create($data);


        // untuk Notif start
        $admin = Staff::selectRaw('users.*')->where('staffs.id',  $dataForm->staff_id)->join('users', 'users.staff_id', '=', 'staffs.id')->first();
        $id_onesignal = $admin->_id_onesignal;
        // $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->description;
        //wa notif                
        $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
        $wa_data_group = [];
        //get phone user

        $categoryName = "Tukar Shift";

        $phone_no = $admin->phone;
        $message = "Pengajuan " . $categoryName . " oleh " . $admin->name;
        $wa_data = [
            'phone' => $this->gantiFormat($phone_no),
            'customer_id' => null,
            'message' => $message,
            'template_id' => '',
            'status' => 'gagal',
            'ref_id' => $wa_code,
            'created_at' => date('Y-m-d h:i:sa'),
            'updated_at' => date('Y-m-d h:i:sa')
        ];
        $wa_data_group[] = $wa_data;
        DB::table('wa_histories')->insert($wa_data);
        $wa_sent = WablasTrait::sendText($wa_data_group);
        $array_merg = [];
        if (!empty(json_decode($wa_sent)->data->messages)) {
            $array_merg = array_merge(json_decode($wa_sent)->data->messages, $array_merg);
        }
        foreach ($array_merg as $key => $value) {
            if (!empty($value->ref_id)) {
                wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
            }
        }

        // //onesignal notif                                
        if (!empty($id_onesignal)) {
            OneSignal::sendNotificationToUser(
                $message,
                $id_onesignal,
                $url = null,
                $data = null,
                $buttons = null,
                $schedule = null
            );
        }
        // // untuk notif end

        //send notif to admin
        $bagian = Staff::selectRaw('users.*')->where('staffs.id',  $dataForm->staff_id)->join('users', 'users.staff_id', '=', 'staffs.id')->first();
        $admin_arr = User::where('dapertement_id', $bagian->dapertement_id)
            ->where('subdapertement_id', 0)
            ->where('staff_id', 0)->get();
        foreach ($admin_arr as $key => $admin) {
            $id_onesignal = $admin->_id_onesignal;
            // $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->description;
            //wa notif                
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            //get phone user
            if ($admin->staff_id > 0) {
                $staff = StaffApi::where('id', $admin->staff_id)->first();
                $phone_no = $staff->phone;
            } else {
                $phone_no = $admin->phone;
            }
            $wa_data = [
                'phone' => $this->gantiFormat($phone_no),
                'customer_id' => null,
                'message' => $message,
                'template_id' => '',
                'status' => 'gagal',
                'ref_id' => $wa_code,
                'created_at' => date('Y-m-d h:i:sa'),
                'updated_at' => date('Y-m-d h:i:sa')
            ];
            $wa_data_group[] = $wa_data;
            DB::table('wa_histories')->insert($wa_data);
            $wa_sent = WablasTrait::sendText($wa_data_group);
            $array_merg = [];
            if (!empty(json_decode($wa_sent)->data->messages)) {
                $array_merg = array_merge(json_decode($wa_sent)->data->messages, $array_merg);
            }
            foreach ($array_merg as $key => $value) {
                if (!empty($value->ref_id)) {
                    wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
                }
            }
            //onesignal notif                                
            if (!empty($id_onesignal)) {
                OneSignal::sendNotificationToUser(
                    $message,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );
            }
        }





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

    public function changeShiftAdminList(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        if ($user->dapertement_id != 5 && $user->dapertement_id != '') {
            $shiftChange = ShiftChange::selectRaw('shift_changes.id,shift_changes.description, shift_changes.created_at, shift_changes.status, st1.name as name1, sh1.title as shift1, s1.start as start1 ,st2.name as name2, sh2.title as shift2, s2.start as start2')
                ->join('shift_planner_staffs as s1', 's1.id', '=', 'shift_changes.shift_id')
                ->join('staffs as st1', 'st1.id', '=', 's1.staff_id')
                ->join('shift_groups as sh1', 'sh1.id', '=', 's1.shift_group_id')
                ->join('shift_planner_staffs as s2', 's2.id', '=', 'shift_changes.shift_change_id')
                ->join('staffs as st2', 'st2.id', '=', 's2.staff_id')
                ->join('shift_groups as sh2', 'sh2.id', '=', 's2.shift_group_id')
                ->orderBy('shift_changes.created_at', 'DESC')
                ->FilterDapertement($user->dapertement_id)->paginate(3, ['*'], 'page', $request->page);
        } else {
            $shiftChange = ShiftChange::selectRaw('shift_changes.id,shift_changes.description, shift_changes.created_at, shift_changes.status, st1.name as name1, sh1.title as shift1, s1.start as start1 ,st2.name as name2, sh2.title as shift2, s2.start as start2')
                ->join('shift_planner_staffs as s1', 's1.id', '=', 'shift_changes.shift_id')
                ->join('staffs as st1', 'st1.id', '=', 's1.staff_id')
                ->join('shift_groups as sh1', 'sh1.id', '=', 's1.shift_group_id')
                ->join('shift_planner_staffs as s2', 's2.id', '=', 'shift_changes.shift_change_id')
                ->join('staffs as st2', 'st2.id', '=', 's2.staff_id')
                ->join('shift_groups as sh2', 'sh2.id', '=', 's2.shift_group_id')
                ->orderBy('shift_changes.created_at', 'DESC')
                ->paginate(3, ['*'], 'page', $request->page);
        }


        return response()->json([
            'message' => 'berhasil',
            'data' =>  $shiftChange,
        ]);
    }

    public function approveAdmin(Request $request)
    {
        // penukaran Shift start
        $shift_changes = ShiftChange::where('id', $request->id)->first();
        // shift saya
        $shift1 = ShiftPlannerStaffs::where('id',  $shift_changes->shift_change_id)->first();
        $staff_id1 = $shift1->staff_id;
        // shift yang ditukar
        $shift2 = ShiftPlannerStaffs::where('id',  $shift_changes->shift_id)->first();
        $staff_id2 = $shift2->staff_id;

        // hapus absen sebelumnya
        $ab1 =  Absence::select('absences.id')->join('absence_logs', 'absence_logs.absence_id', '=', 'absences.id')
            ->where('absence_category_id', '1')
            ->where('absence_logs.status', '1')
            ->where('staff_id', $staff_id1)
            ->where('shift_planner_id', $shift_changes->shift_change_id)->first();
        // dd($ab1);
        if ($ab1) {
            AbsenceLog::where('absence_id', $ab1->id)->delete();
            Absence::where('id', $ab1->id)->delete();
        }
        // dd($tess, $tef);

        $ab2 =  Absence::select('absences.id')->join('absence_logs', 'absence_logs.absence_id', '=', 'absences.id')
            ->where('absence_category_id', '1')
            ->where('absence_logs.status', '1')
            ->where('staff_id', $staff_id2)
            ->where('shift_planner_id', $shift_changes->shift_id)->first();
        if ($ab2) {
            AbsenceLog::where('absence_id', $ab2->id)->delete();
            Absence::where('id', $ab2->id)->delete();
        }
        // hapus absen sebelumnya end

        $shift1->update([
            'staff_id' => $staff_id2
        ]);
        $shift2->update([
            'staff_id' => $staff_id1
        ]);
        $shift_changes->update([
            'status' => 'approve'
        ]);
        // penukaran shift end
        return response()->json([
            'message' => 'penukaran disetujui',
        ]);
    }

    public function rejectAdmin(Request $request)
    {
        // penukaran Shift start
        $shift_changes = ShiftChange::where('id', $request->id)->first();
        $shift_changes->update([
            'status' => 'reject'
        ]);
        // penukaran shift end
        return response()->json([
            'message' => 'penukaran ditolak',
        ]);
    }
}
