<?php

namespace App\Http\Controllers\Admin;

use App\Absence;
use App\AbsenceLog;
use App\Http\Controllers\Controller;
use App\Shift;
use App\ShiftChange;
use App\ShiftPlannerStaffs;
use App\Staff;
use App\StaffApi;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use OneSignal;
use App\Traits\WablasTrait;
use App\wa_history;

class ShiftChangeController extends Controller
{
    // public function index(Request $request)
    // {
    //     $shift_changes = ShiftChange::get();

    //     dd($shift1, $shift2);
    //     return view('admin.shift_change.index', compact('shift_changes'));
    // }
    use WablasTrait;
    public function index(Request $request)
    {

        abort_unless(\Gate::allows('extra_access'), 403);
        $checker = [];
        $users = User::with(['roles'])
            ->where('id', Auth::user()->id)
            ->first();
        foreach ($users->roles as $data) {
            foreach ($data->permissions as $data2) {
                $checker[] = $data2->title;
            }
        }
        $subdapertement = Auth::user()->subdapertement_id != '0' ? Auth::user()->subdapertement_id : '';
        if (!in_array('absence_all_access', $checker)) {
            $qry = ShiftChange::selectRaw('shift_changes.id,shift_changes.created_at ,shift_changes.description ,shift_changes.status, st1.name as name1, sh1.title as shift1, s1.start as start1 ,st2.name as name2, sh2.title as shift2, s2.start as start2')
                ->join('shift_planner_staffs as s1', 's1.id', '=', 'shift_changes.shift_id')
                ->join('staffs as st1', 'st1.id', '=', 'shift_changes.staff_id')
                ->join('shift_groups as sh1', 'sh1.id', '=', 's1.shift_group_id')
                ->join('shift_planner_staffs as s2', 's2.id', '=', 'shift_changes.shift_change_id')
                ->join('staffs as st2', 'st2.id', '=', 'shift_changes.staff_change_id')
                ->join('shift_groups as sh2', 'sh2.id', '=', 's2.shift_group_id')
                ->FilterDapertement(Auth::user()->dapertement_id)
                ->orderBy('shift_changes.created_at', 'DESC');
        } else {
            $qry = ShiftChange::selectRaw('st1.id as st_id,st2.id as st_c_id, shift_changes.id,shift_changes.created_at, shift_changes.description, shift_changes.status, st1.name as name1, sh1.title as shift1, s1.start as start1 ,st2.name as name2, sh2.title as shift2, s2.start as start2')
                ->join('shift_planner_staffs as s1', 's1.id', '=', 'shift_changes.shift_id')
                ->join('staffs as st1', 'st1.id', '=', 's1.staff_id')
                ->join('shift_groups as sh1', 'sh1.id', '=', 's1.shift_group_id')
                ->join('shift_planner_staffs as s2', 's2.id', '=', 'shift_changes.shift_change_id')
                ->join('staffs as st2', 'st2.id', '=', 's2.staff_id')
                ->join('shift_groups as sh2', 'sh2.id', '=', 's2.shift_group_id')
                ->orderBy('shift_changes.created_at', 'DESC');
        }
        // $data = [];
        // foreach ($qry->get() as $value) {
        //     $data[] = [
        //         'st' => $value->st_id,
        //         'st_c' => $value->st_c_id,
        //         'id' => $value->id,
        //         'status' => $value->status,
        //     ];
        //     if ($value->status != "approve")
        //         ShiftChange::where('id', $value->id)
        //             ->update([
        //                 'staff_id' => $value->st_id,
        //                 'staff_change_id' => $value->st_c_id,
        //             ]);
        //     else {
        //         ShiftChange::where('id', $value->id)
        //             ->update([
        //                 'staff_id' => $value->st_c_id,
        //                 'staff_change_id' => $value->st_id,
        //             ]);
        //     }
        // }

        // dd($data);
        // dd($qry->get());
        if ($request->ajax()) {
            //set query
            $table = DataTables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = '';
                $editGate = '';
                $deleteGate = '';
                $crudRoutePart = 'shift_change';

                return view('partials.datatablesDuties', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });
            $table->editColumn('id', function ($row) {
                return $row->id ? $row->id : "";
            });

            $table->editColumn('name1', function ($row) {
                return $row->name1 ? $row->name1 : "";
            });

            $table->editColumn('description', function ($row) {
                return $row->description ? $row->description : "";
            });

            $table->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at : "";
            });

            $table->editColumn('shift1', function ($row) {
                return $row->shift1 ? date('Y-m-d', strtotime($row->start1)) . '(' . $row->shift1 . ')' : "";
            });
            // $table->editColumn('start1', function ($row) {
            //     return $row->start1 ? $row->start1.$row->start1 : "";
            // });

            $table->editColumn('name2', function ($row) {
                return $row->name2 ? $row->name2 : "";
            });

            $table->editColumn('shift2', function ($row) {
                return $row->shift2 ? date('Y-m-d', strtotime($row->start2))  . '(' . $row->shift2 . ')' : "";
            });

            // $table->editColumn('start2', function ($row) {
            //     return $row->start2 ? $row->start2 : "";
            // });
            $table->editColumn('status', function ($row) {
                return $row->status ? $row->status : "";
            });


            $table->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at : "";
            });
            $table->editColumn('updated_at', function ($row) {
                return $row->updated_at ? $row->updated_at : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        return view('admin.shift_change.index');
    }

    public function create(Request $request)
    {
        $last_code = $this->get_last_code('shift_group');

        $code = acc_code_generate($last_code, 8, 3);

        $shift_group = ShiftChange::where('work_type_id', $request->work_type_id)->orderBy('queue', 'DESC')->first();
        if ($shift_group) {
            $queue = $shift_group->queue + 1;
            // dd($queue);
        } else {
            $queue = 1;
            // dd($queue);
        }

        $departementlist = Dapertement::all();
        $jobs = Job::get();
        $work_units = WorkUnit::get();
        $work_type_id = $request->work_type_id;


        return view('admin.shift_group.create', compact('queue', 'code', 'departementlist', 'jobs', 'work_units', 'work_type_id'));
    }
    public function store(Request $request)
    {
        $work_type = WorkTypes::where('id', $request->work_type_id)->first();
        $data = array_merge($request->all(), ['dapertement_id' => $work_type->dapertement_id, 'job_id' => $work_type->job_id, 'work_unit_id' => $work_type->work_unit_id]);
        $shift_group = ShiftChange::create($data);
        $presence = Absence_categories::where('type', 'presence')->get();
        $break = Absence_categories::where('type', 'break')->get();
        foreach ($presence as $item) {
            ShiftGroupTimesheets::create([
                'shift_group_id' => $shift_group->id,
                'absence_category_id' => $item->id,
                'time' => '00:00:00',
                'start' => '00:00:00',
                'end' => '00:00:00',
                'duration' => '0',
            ]);
        }

        foreach ($break as $item) {
            ShiftGroupTimesheets::create([
                'shift_group_id' => $shift_group->id,
                'absence_category_id' => $item->id,
                'time' => '00:00:00',
                'start' => '00:00:00',
                'end' => '00:00:00',
                'duration' => '0',
            ]);
        }
        // dd($data->work_type_id);

        return redirect()->route('admin.shift_group.index', ["id" => $data['work_type_id']]);
    }
    public function show($id)
    {
        abort_unless(\Gate::allows('permission_show'), 403);
        $shift_group = ShiftChange::where('shift_changes.id', $id)->first();
        return view('admin..shift_group.show', compact('shift_group'));
    }
    public function edit($id)
    {
        // dd($id);
        $shift_group = ShiftChange::where('id', $id)->first();
        $dapertements = Dapertement::all();
        $subdapertements = Subdapertement::where('dapertement_id', $shift_group->dapertement_id)->get();

        return view('admin.shift_group.edit', compact('shift_group', 'dapertements', 'subdapertements'));
    }
    public function update($id, Request $request)
    {
        $shift_group = ShiftChange::where('id', $id)->first();
        $shift_group->update($request->all());
        return redirect()->route('admin.shift_group.index');
    }
    public function approve(Request $request)
    {
        // penukaran Shift start
        $shift_changes = ShiftChange::where('id', $request->id)->first();
        // shift saya
        $shift1 = ShiftPlannerStaffs::where('id',  $shift_changes->shift_change_id)->first();
        $staff_id1 = $shift1->staff_id;

        $shift2 = ShiftPlannerStaffs::where('id',  $shift_changes->shift_id)->first();
        $staff_id2 = $shift2->staff_id;
        // dd($shift1, $shift2);
        if (date('Y-m-d', strtotime($shift1->start)) >= date('Y-m-d') && date('Y-m-d', strtotime($shift2->start)) >= date('Y-m-d')) {

            $shift_Change_else = ShiftChange::where('id', '!=', $shift_changes->id)
                ->where('shift_id',  $shift_changes->shift_id)
                ->update([
                    'status' => 'reject'
                ]);

            // dd($shift_Change_else);
            // shift yang ditukar


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

            // untuk nama staff
            $stf1 = Staff::selectRaw('staffs.*')->where('staffs.id',  404)->first();
            $stf2 = Staff::selectRaw('staffs.*')->where('staffs.id',  404)->first();


            // untuk Notif start
            $admin = Staff::selectRaw('users.*')->where('staffs.id',  $staff_id1)->join('users', 'users.staff_id', '=', 'staffs.id')->first();
            $admin = Staff::selectRaw('users.*')->where('staffs.id',  404)->join('users', 'users.staff_id', '=', 'staffs.id')->first();

            $id_onesignal = $admin->_id_onesignal;
            // $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->description;
            //wa notif                
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            //get phone user

            $categoryName = "Tukar Shift";

            $phone_no = $admin->phone;
            $message = "Pengajuan sudah disejui dengan " . $stf2->name;
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

            // untuk Notif start
            $admin = Staff::selectRaw('users.*')->where('staffs.id',  $staff_id2)->join('users', 'users.staff_id', '=', 'staffs.id')->first();
            $admin = Staff::selectRaw('users.*')->where('staffs.id',  404)->join('users', 'users.staff_id', '=', 'staffs.id')->first();

            $id_onesignal = $admin->_id_onesignal;
            // $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->description;
            //wa notif                
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            //get phone user

            $categoryName = "Tukar Shift";

            $phone_no = $admin->phone;
            $message = "Pengajuan sudah disejui dengan " . $stf1->name;
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


        } else {
            dd("Tanggal sudah lewat");
        }
        // penukaran shift end
        return back();
    }

    public function reject(Request $request)
    {
        // penukaran Shift start
        $shift_changes = ShiftChange::where('id', $request->id)->first();
        $shift_changes->update([
            'status' => 'reject'
        ]);
        // penukaran shift end
        return back();
    }


    public function destroy($id)
    {
        $shift_group_timesheets = ShiftGroupTimesheets::where('shift_group_id', $id)->delete();
        $shift_planner_staff = ShiftPlannerStaffs::where('shift_group_id', $id)->delete();
        $shift_group = ShiftChange::where('id', $id)->first();
        $shift_group->delete();
        return back();
    }
    public function massDestroy(Request $request)
    {
        ShiftChange::whereIn('id', request('ids'))->delete();
        return response(null, 204);
    }
}
