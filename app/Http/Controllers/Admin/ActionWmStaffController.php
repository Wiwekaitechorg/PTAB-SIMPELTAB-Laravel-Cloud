<?php

namespace App\Http\Controllers\Admin;

use App\Action;
use App\actionWms;
use App\actionWmStaff;
use App\CtmWilayah;
use App\Http\Controllers\Controller;
use App\Staff;
use App\StaffApi;
use App\Traits\WablasTrait;
use App\wa_history;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActionWmStaffController extends Controller
{
    use WablasTrait;
    public function index($id)
    {
        abort_unless(\Gate::allows('actionWmStaff_access'), 403);

        $staffs = actionWmStaff::selectRaw('staffs.id as staff_id, staffs.name as staff_name, staffs.code as staff_code,staffs.phone as staff_phone, work_units.name as work_unit_name ')
            ->join('action_wms', 'action_wms.id', '=', 'action_wm_staff.action_wm_id')
            ->join('staffs', 'staffs.id', '=', 'action_wm_staff.staff_id')
            ->leftJoin('work_units', 'staffs.work_unit_id', '=', 'work_units.id')
            ->where('action_wms.id', $id)
            ->get();
        // dd($staffs);
        $areas = CtmWilayah::select('id as code', 'NamaWilayah')->get();

        return view('admin.actionWmStaff.index', compact('id', 'staffs', 'areas'));
    }
    // public function create($id)
    // {
    //     $staffs = Staff::limit(10)->get();
    //     dd($staffs);
    // }

    // nambah staff untuk tindakan
    public function create($id)
    {
        abort_unless(\Gate::allows('actionWmStaff_create'), 403);
        // dd($id);

        // abort_unless(\Gate::allows('action_staff_create'), 403);



        $action = actionWms::where('id', $id)->first();
        // dd($action);
        $action_id = $action->id;
        $proposal_wm_id = $id;
        // $action_staffs = actionWmStaff::where('id', $id)->with('staff')->first();
        // dd($action_staffs);
        $staffs = Staff::selectRaw('staffs.id as id, staffs.name as name, staffs.code as code,staffs.phone as phone, work_units.name as work_unit_name ')
            ->leftJoin('work_units', 'staffs.work_unit_id', '=', 'work_units.id')
            ->where('subdapertement_id', $action->subdapertement_id)->get();
        // dd($staffs);
        // $staffs = Staff::where('dapertement_id', $action->dapertement_id)->with('action')->get();

        $action_staffs_list = DB::table('staffs')
            ->join('action_wm_staff', function ($join) use ($id) {
                $join->on('action_wm_staff.staff_id', '=', 'staffs.id')
                    ->join('action_wms', 'action_wm_staff.action_wm_id', '=', 'action_wms.id')
                    ->join('proposal_wms', 'proposal_wms.id', '=', 'action_wms.proposal_wm_id')
                    ->where('proposal_wms.status', '!=', 'close')
                    ->Where('proposal_wms.status', '!=', 'reject')
                    ->where('action_wms.id', '=', $id);
            })

            ->get();
        // dd($staffs);
        return view('admin.actionWmStaff.create', compact('proposal_wm_id', 'action_id', 'staffs', 'action', 'action_staffs_list'));

        // dd($action_staffs_list);
    }
    public function store(Request $request, $id)
    {
        abort_unless(\Gate::allows('actionWmStaff_create'), 403);
        // dd($request->all());
        $data = [
            'action_wm_id' => $request->action_id,
            'staff_id' => $request->staff_id
        ];
        actionWmStaff::create($data);

        //send notif to admin start
        $admin_arr = Staff::where('id', $request->staff_id)
            ->get();
        foreach ($admin_arr as $key => $admin) {
            $message = 'Test:' . $admin->id . 'Pergantian Water Meter Buka Aplikasi Segel Meter';
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
        }


        return redirect()->route('admin.actionWmStaff.index', $request->proposal_wm_id);
    }

    // rubah
    public function destroy(Request $request)
    {
        abort_unless(\Gate::allows('actionWmStaff_delete'), 403);
        // dd($request->all());
        $test = actionWmStaff::where('staff_id', $request->staff_id)->delete();
        return redirect()->back();
    }
}
