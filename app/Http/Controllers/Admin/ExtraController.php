<?php

namespace App\Http\Controllers\Admin;

use App\Absence;
use App\AbsenceLog;
use App\AbsenceRequest;
use App\AbsenceRequestLogs;
use App\Holiday;
use App\Http\Controllers\Controller;
use App\MessageLog;
use App\Requests;
use App\ShiftPlannerStaffs;
use App\Staff;
use App\User;
use OneSignal;
use App\Traits\WablasTrait;
use App\wa_history;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class ExtraController extends Controller
{
    use WablasTrait;
    public function index(Request $request)
    {

        abort_unless(\Gate::allows('extra_access'), 403);
        $checker = [];
        $users = user::with(['roles'])
            ->where('id', Auth::user()->id)
            ->first();
        foreach ($users->roles as $data) {
            foreach ($data->permissions as $data2) {
                $checker[] = $data2->title;
            }
        }
        $subdapertement = Auth::user()->subdapertement_id != '0' ? Auth::user()->subdapertement_id : '';

        if (!in_array('absence_all_access', $checker)) {
            $qry = AbsenceRequest::selectRaw('absence_requests.*, staffs.name as staff_name')->join('staffs', 'staffs.id', '=', 'absence_requests.staff_id')
                ->where('absence_requests.category', 'extra')
                ->where('staffs.dapertement_id', Auth::user()->dapertement_id)
                ->FilterStatus($request->status)
                ->FilterDateStart($request->from, $request->to);
            if ($subdapertement != '') {
                $qry = $qry->where('subdapertement_id', Auth::user()->subdapertement_id);
                // dd($subdapertement, 'nbhgv');
            }
        } else {
            $qry = AbsenceRequest::selectRaw('absence_requests.*, staffs.name as staff_name')->join('staffs', 'staffs.id', '=', 'absence_requests.staff_id')
                ->where('absence_requests.category', 'extra')
                ->FilterStatus($request->status)
                ->FilterDateStart($request->from, $request->to);
        }
        // ->orderBy('staffs.NIK')
        // ->orderBy('absence_requests.created_at', 'DESC');
        // dd($qry->get());
        if ($request->ajax()) {
            //set query
            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'duty_access';
                $editGate = '';
                $deleteGate = 'extra_delete';
                $crudRoutePart = 'extra';

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

            $table->editColumn('staff_name', function ($row) {
                return $row->staff_name ? $row->staff_name : "";
            });

            $table->editColumn('desciption', function ($row) {
                return $row->desciption ? $row->desciption : "";
            });

            $table->editColumn('start', function ($row) {
                return $row->start ? $row->start : "";
            });

            $table->editColumn('end', function ($row) {
                return $row->end ? $row->end : "";
            });
            $table->editColumn('type', function ($row) {
                return $row->type ? $row->type : "";
            });
            $table->editColumn('start', function ($row) {
                return $row->start ? $row->start : "";
            });
            $table->editColumn('status', function ($row) {
                return $row->status ? $row->status : "";
            });
            $table->editColumn('category', function ($row) {
                return $row->category ? $row->category : "";
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
        return view('admin.extra.index');
    }
    public function create(Request $request)
    {
        abort_unless(\Gate::allows('extra_create'), 403);
        $type = $request->type;
        $staffs = Staff::orderBy('name')->get();
        return view('admin.extra.create', compact('staffs', 'type'));
    }
    public function store(Request $request)
    {
        abort_unless(\Gate::allows('extra_create'), 403);
        $data = [
            'category' => 'extra',
            'type' => 'other',
            'staff_id' => $request->staff_id,
            'time' => $request->time,
            'start' => $request->start,
            'description' => $request->description,
        ];
        $extra = AbsenceRequest::create($data);
        return redirect()->route('admin.extra.index');
    }

    public function show($id)
    {
        abort_unless(\Gate::allows('extra_access'), 403);
        $extra = AbsenceRequest::selectRaw('absence_requests.*, staffs.name as staff_name')
            ->join('staffs', 'staffs.id', '=', 'absence_requests.staff_id')
            ->where('absence_requests.id', $id)->first();

        $file = AbsenceRequestLogs::where('absence_request_id', $id)->get();

        // dd($extra, $file);
        return view('admin.extra.show', compact('extra', 'file'));
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('extra_edit'), 403);
        $requests = Requests::where('id', $id)->first();
        $type = $requests->type;
        $users = User::where('staff_id', '!=', '0')->orderBy('name')->get();
        return view('admin.extra.edit', compact('users', 'type', 'requests'));
    }
    public function update(Request $request, $id)
    {
        abort_unless(\Gate::allows('extra_edit'), 403);
        $extra = Requests::where('id', $id)
            ->update($request->except(['_token', '_method']));
        return redirect()->route('admin.extra.index');
    }


    public function reject($id)
    {
        abort_unless(\Gate::allows('extra_edit'), 403);
        $d = AbsenceRequest::where('id', $id)
            ->update(['status' => 'reject']);

        $d = AbsenceRequest::where('id', $id)->first();
        $message = "Lembur anda tanggal " . $d->start . " ditolak";
        MessageLog::create([
            'staff_id' => $d->staff_id,
            'memo' => "Lembur anda tanggal " . $d->start . " ditolak",
            'type' => 'message',
            'status' => 'pending',
        ]);

        // untuk Notif start
        $admin = Staff::selectRaw('users.*')->where('staffs.id', $d->staff_id)->join('users', 'users.staff_id', '=', 'staffs.id')->first();
        $id_onesignal = $admin->_id_onesignal;
        // $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->description;
        //wa notif                
        $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
        $wa_data_group = [];
        //get phone user
        if ($d->staff_id > 0) {
            $staff = Staff::where('id', $d->staff_id)->first();
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
        // untuk notif end

        return redirect()->back();
    }
    public function approve($id)
    {
        abort_unless(\Gate::allows('extra_delete'), 403);
        $d = AbsenceRequest::where('id', $id)
            ->update(['status' => 'approve']);

        $d = AbsenceRequest::where('id', $id)->first();
        $message = "Lembur anda tanggal " . $d->start . " diterima";

        // jika libur start
        $holiday = Holiday::whereDate('start', '=', date("Y-m-d", strtotime($d->start)))->first();

        if ($holiday) {
            $shiftStaff = ShiftPlannerStaffs::where('staff_id', $d->staff_id)->whereDate('start', '=', date('Y-m-d', strtotime($d->start)))->first();
            // dd($shiftStaff, date('Y-m-d', strtotime($d->start)));
            if ($shiftStaff) {
                $absenceLog = AbsenceLog::where('shift_planner_id', $shiftStaff->id)->first();
                if ($absenceLog) {
                    $absence = Absence::where('id', $absenceLog->absence_id)->first();
                    if ($absence) {
                        foreach ($absence->absence_logs as $data) {
                            // dd($data->id);
                            AbsenceLog::where('id', $data->id)->delete();
                        }
                        // dd('sss');
                        Absence::where('id', $absenceLog->absence_id)->delete();
                    }
                }
            }
            $shiftStaff = ShiftPlannerStaffs::where('staff_id', $d->staff_id)->whereDate('start', '=', date('Y-m-d', strtotime($d->start)))->delete();
        }
        // jika libur end

        MessageLog::create([
            'staff_id' => $d->staff_id,
            'memo' => $message,
            'type' => 'message',
            'status' => 'pending',
        ]);

        // untuk Notif start
        $admin = Staff::selectRaw('users.*')->where('staffs.id', $d->staff_id)->join('users', 'users.staff_id', '=', 'staffs.id')->first();
        $id_onesignal = $admin->_id_onesignal;
        // $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->description;
        //wa notif                
        $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
        $wa_data_group = [];
        //get phone user
        if ($d->staff_id > 0) {
            $staff = Staff::where('id', $d->staff_id)->first();
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
        // untuk notif end



        return redirect()->back();
    }
}
