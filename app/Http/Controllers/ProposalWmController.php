<?php

namespace App\Http\Controllers\Admin;

use App\actionWms;
use App\actionWmStaff;
use App\CtmWilayah;
use App\Customer;
use App\Dapertement;
use App\Http\Controllers\Controller;
use App\proposalWms;
use App\StaffApi;
use App\Subdapertement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\TraitModel;
use App\Traits\WablasTrait;
use App\User;
use App\wa_history;
use Illuminate\Support\Facades\Auth;

class ProposalWmController extends Controller
{
    use TraitModel;
    use WablasTrait;

    public function index(Request $request)
    {
        // asli
        // $permissions = CtmStatussmPelanggan::where('statussm', 101)->where('tahun', date('Y'))->where('bulan', date('n'))
        //     ->orWhere('statussm', 102)->where('tahun', date('Y'))->where('bulan', (date('n') - 1))
        //     ->orWhere('statussm', 103)->where('tahun', date('Y'))->where('bulan', (date('n') - 1))->get();

        abort_unless(\Gate::allows('proposalWm_access'), 403);
        // uji coba
        foreach (Auth::user()->roles as $data) {
            $roles[] = $data->id;
        }

        $subdapertement_id = Auth::user()->subdapertement_id;
        $areas = CtmWilayah::select('id as code', 'NamaWilayah')->get();
        if (in_array('8', $roles)) {
            if (date('d') > 20) {
                $statussm = proposalWms::selectRaw('print_status,queue,tblpelanggan.idareal, proposal_wms.code, proposal_wms.customer_id, proposal_wms.status_wm, proposal_wms.priority, proposal_wms.year, proposal_wms.month, proposal_wms.id, proposal_wms.created_at, proposal_wms.updated_at, proposal_wms.status')
                    ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening')
                    // ->whereBetween('proposal_wms.created_at', [date('Y-m-21', strtotime('0 month', strtotime(date('Y-m-d')))), date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))])
                    ->FilterPriority($request->priority)
                    ->FilterStatus($request->status)
                    ->FilterStatusWM($request->statussm)
                    ->FilterAreas($request->areas)
                    ->FilterDate(request()->input('from'), request()->input('to'));
            } else {
                $statussm = proposalWms::selectRaw('print_status,queue,tblpelanggan.idareal, proposal_wms.code, proposal_wms.customer_id, proposal_wms.status_wm, proposal_wms.priority, proposal_wms.year, proposal_wms.month, proposal_wms.id, proposal_wms.created_at, proposal_wms.updated_at, proposal_wms.status')
                    ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening')
                    // ->whereBetween('proposal_wms.created_at', [date('Y-m-21', strtotime('-1 month', strtotime(date('Y-m-d')))), date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-d'))))])
                    ->FilterPriority($request->priority)
                    ->FilterStatus($request->status)
                    ->FilterStatusWM($request->statussm)
                    ->FilterAreas($request->areas)
                    ->FilterDate(request()->input('from'), request()->input('to'));
            }
        } else {
            $group_unit = Dapertement::select('dapertements.group_unit')
                ->where('dapertements.id', Auth::user()->dapertement_id)->first()->group_unit;
            $data = CtmWilayah::select('id as code', 'NamaWilayah')->where('group_unit', $group_unit)->get();
            $areas = $data;
            // $statussm = proposalWms::selectRaw('tblpelanggan.idareal, proposal_wms.code, proposal_wms.customer_id, proposal_wms.status_wm, proposal_wms.priority, proposal_wms.year, proposal_wms.month, proposal_wms.id, proposal_wms.created_at, proposal_wms.updated_at, proposal_wms.status')
            //     ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening');

            $statussm = actionWmStaff::selectRaw('proposal_wms.status_wm,
            queue,
                proposal_wms.code,
            proposal_wms.priority,
            proposal_wms.status,
            proposal_wms.id as id,
            proposal_wms.created_at,
            proposal_wms.updated_at,
            tblpelanggan.namapelanggan,
            proposal_wms.customer_id,
            tblpelanggan.alamat,
            tblpelanggan.idareal,
            proposal_wms.year,
            proposal_wms.month,
            print_status
            ')
                ->rightJoin('action_wms', 'action_wms.id', '=', 'action_wm_staff.action_wm_id')
                ->rightJoin('proposal_wms', 'action_wms.proposal_wm_id', '=', 'proposal_wms.id')
                ->join('simpletabctm.tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'proposal_wms.customer_id')
                ->join('simpletabctm.tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')


                ->leftJoin('staffs', 'staffs.id', '=', 'action_wm_staff.staff_id');
            foreach (Auth::user()->roles as $data) {
                $roles[] = $data->id;
            }
            // dd(in_array('18', $roles));
            // dd();

            if (in_array('18', $roles) || in_array('15', $roles) || in_array('16', $roles) || in_array('14', $roles) || in_array('17', $roles)) {
                if (date('d') > 20) {
                    $statussm->where('tblwilayah.group_unit', $group_unit)
                        // ->whereBetween('proposal_wms.created_at', [date('Y-m-21', strtotime('0 month', strtotime(date('Y-m-d')))), date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))])
                        ->FilterPriority($request->priority)
                        ->FilterStatus($request->status)
                        ->FilterStatusWM($request->statussm)
                        ->FilterAreas($request->areas)
                        ->FilterDate(request()->input('from'), request()->input('to'));
                    // $data2 = $data2 . ' where idareal = ' . $data[$i]->area_id;
                } else {
                    $statussm->where('tblwilayah.group_unit', $group_unit)
                        // ->whereBetween('proposal_wms.created_at', [date('Y-m-21', strtotime('-1 month', strtotime(date('Y-m-d')))), date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-d'))))])
                        ->FilterPriority($request->priority)
                        ->FilterStatus($request->status)
                        ->FilterStatusWM($request->statussm)
                        ->FilterAreas($request->areas)
                        ->FilterDate(request()->input('from'), request()->input('to'));
                }
            }
            // dd($statussm->get());
            else {
                if (date('d') > 20) {
                    $statussm->orWhere('staffs.id', Auth::user()->id)
                        // ->whereBetween('proposal_wms.created_at', [date('Y-m-21', strtotime('0 month', strtotime(date('Y-m-d')))), date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))])
                        ->FilterPriority($request->priority)
                        ->FilterStatus($request->status)
                        ->FilterStatusWM($request->statussm)
                        ->FilterAreas($request->areas)
                        ->FilterDate(request()->input('from'), request()->input('to'));
                } else {
                    $statussm->orWhere('staffs.id', Auth::user()->id)
                        // ->whereBetween('proposal_wms.created_at', [date('Y-m-21', strtotime('-1 month', strtotime(date('Y-m-d')))), date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-d'))))])
                        ->FilterPriority($request->priority)
                        ->FilterStatus($request->status)
                        ->FilterStatusWM($request->statussm)
                        ->FilterAreas($request->areas)
                        ->FilterDate(request()->input('from'), request()->input('to'));
                }
            }
        }
        // dd($statussm->get());

        if ($request->ajax()) {
            //set query
            $qry = $statussm->orderBy('proposal_wms.updated_at', 'desc');

            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'proposalWm_show';
                $editGate = 'proposalWm_edit';
                $deleteGate = 'proposalWm_create';
                $crudRoutePart = 'proposalwm';

                return view('partials.datatablesActionsProposalWM', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('code', function ($row) {
                return $row->code ? $row->queue . $row->code : "";
            });

            $table->editColumn('customer_id', function ($row) {
                return $row->customer_id ? $row->customer_id : "";
            });

            $table->editColumn('status_wm', function ($row) {

                if ($row->status_wm == 101) {
                    return "WM Kabur";
                } else if ($row->status_wm == 102) {
                    return "WM Rusak";
                } else if ($row->status_wm == 103) {
                    return "WM Mati";
                } else {
                    "";
                }
            });

            $table->editColumn('priority', function ($row) {
                if ($row->priority === "3") {
                    $value = "Hight";
                } else if ($row->priority === "2") {
                    $value = "Medium";
                } else {
                    $value = "";
                }

                return $value;
            });
            $table->editColumn('status', function ($row) {
                if ($row->print_status == 1 && $row->status == "close") {
                    return "close2";
                } else {
                    return $row->status ? $row->status : "";
                }
            });
            $table->editColumn('periode', function ($row) {
                return $row->year && $row->month ? $row->year . '-' . $row->month : "";
            });

            $table->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at : "";
            });

            $table->editColumn('updated_at', function ($row) {
                return $row->updated_at ? $row->updated_at : "";
            });

            $table->editColumn('idareal', function ($row) {
                return $row->idareal ? $row->idareal : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        return view('admin.proposalWm.index', compact('areas', 'subdapertement_id', 'roles'));
    }
    public function create()
    {
        abort_unless(\Gate::allows('proposalWm_create'), 403);
        return View('admin.proposalWm.create');
    }
    public function store(Request $request)
    {
        abort_unless(\Gate::allows('proposalWm_create'), 403);
        $customers =  Customer::join('tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')->where('nomorrekening', $request->customer_id)->get();
        $gU = $customers[0]->group_unit;
        if ($gU == "1") {
            $s = "BAP";
            $n = 14;
        } else if ($gU == "2") {
            $s = "BAPUK";
            $n = 15;
        } else if ($gU == "4") {
            $s = "BAPUP";
            $n = 15;
        } else if ($gU == "5") {
            $s = "BAPUB";
            $n = 15;
        } else if ($gU == "3") {
            $s = "BAPUS";
            $n = 15;
        } else {
            $s = "";
            $n = 15;
        }


        $last_code = $this->get_last_codeS('proposal_wm', $gU);


        $data = array_merge($request->all(), ['queue' =>  $last_code, 'code' => '/' . $s . '/' . date('n') . '/' . date('Y')]);
        // dd($data);
        if (count($customers) > 0) {
            $proposalWms = proposalWms::create($data);
        } else {
            dd("Pelanggan Tidak Ada");
        }

        $admin_arr = User::selectRaw('users.*, dapertements.*')
            ->leftJoin('dapertements', 'users.dapertement_id', '=', 'dapertements.id')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->where('group_unit', $customers[0]->group_unit)->where('role_user.role_id', 18)
            ->orWhere('role_user.role_id', 15)->where('group_unit', $customers[0]->group_unit)

            // ->orwhere('dapertement_id', '2')->where('subdapertement_id', '0')->where('staff_id', '0')->where('group_unit', $group_unit)
            ->get();
        // dd($admin_arr);
        foreach ($admin_arr as $key => $admin) {
            $message = 'Test: ' . $admin->id . ' Baru Dibuat';
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

        return redirect()->route('admin.proposalwm.index');
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('proposalWm_edit'), 403);
        $proposalwm = proposalWms::where('id', $id)->first();
        return View('admin.proposalWm.edit', compact('proposalwm', 'id'));
    }
    public function update($id, Request $request)
    {
        abort_unless(\Gate::allows('proposalWm_edit'), 403);
        $customers =  Customer::where('nomorrekening', $request->customer_id)->get();
        if (count($customers) > 0) {
            $proposalWms = proposalWms::where('id', $id)->first();
            $proposalWms->update($request->all());
        } else {
            dd("Pelanggan Tidak Ada");
        }
        return redirect()->route('admin.proposalwm.index');
    }

    public function approve($id)
    {
        abort_unless(\Gate::allows('proposalWm_approve'), 403);
        $subdapertement_id = "";
        $proposalWm = proposalWms::selectRaw('memo, priority ,customer_id,NamaStatus, tblpelanggan.namapelanggan as name, tblpelanggan.nomorrekening')
            ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening')
            ->leftJoin('simpletabctm.tblstatuswm', 'tblstatuswm.id', '=', 'proposal_wms.status_wm')
            ->where('proposal_wms.id', $id)
            ->first();
        // dd($proposalWm);
        $customer = Customer::join('tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
            ->where('nomorrekening', $proposalWm->customer_id);
        if ($customer->first()->group_unit == 1) {
            $subdapertement = "10";
        } else {
            $subdapertement = Subdapertement::select('subdapertements.id')->leftJoin('dapertements', 'dapertements.id', '=', 'subdapertements.dapertement_id')->where('subdapertements.name', 'TEKNIK')->where('group_unit', $customer->first()->group_unit)->first();
            $subdapertement = $subdapertement->id;
        }


        return View('admin.proposalWm.approve', compact('id', 'proposalWm', 'subdapertement', 'subdapertement_id'));
    }

    public function approveProses(Request $request)
    {
        abort_unless(\Gate::allows('proposalWm_approve'), 403);
        $proposalwms = proposalWms::find($request->id);
        $proposalWm = $proposalwms;

        $data = [
            'proposal_wm_id' => $request->id,
            'subdapertement_id' => $request->subdapertement_id,
            'memo' => $request->memo,
            'category' => $request->category
        ];

        actionWms::create($data);

        $proposalwms = $proposalwms->update([
            'priority' => $request->priority,
            'status' => 'active',
            'updated_at' => date('Y-m-d h:m:s'),
            'created_at' => date('Y-m-d h:m:s')
        ]);
        // dd($proposalWm);

        $customer = Customer::join('tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
            ->where('nomorrekening', $proposalWm->customer_id);

        $group_unit = CtmWilayah::where('tblwilayah.id', $customer->first()->idareal)->first()->group_unit;

        $admin_arr = User::selectRaw('users.*, dapertements.*')
            ->leftJoin('dapertements', 'users.dapertement_id', '=', 'dapertements.id')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->where('group_unit', $group_unit)->where('role_user.role_id', 17)
            ->orWhere('role_user.role_id', 14)->where('group_unit', $group_unit)
            ->orWhere('role_user.role_id', 16)->where('group_unit', $group_unit)

            // ->orwhere('dapertement_id', '2')->where('subdapertement_id', '0')->where('staff_id', '0')->where('group_unit', $group_unit)
            ->get();
        // dd($admin_arr);
        foreach ($admin_arr as $key => $admin) {
            $message = 'Test: ' . $admin->code . ' Disetujui';
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

        return redirect()->route('admin.proposalwm.index');
    }

    public function approveAll(Request $request)
    {

        foreach (Auth::user()->roles as $data) {
            $roles[] = $data->id;
        }

        if (in_array('8', $roles)) {
            $proposalWm = proposalWms::selectRaw('idareal,group_unit, tblpelanggan.idareal, proposal_wms.code, proposal_wms.customer_id, proposal_wms.status_wm, proposal_wms.priority, proposal_wms.year, proposal_wms.month, proposal_wms.id, proposal_wms.created_at, proposal_wms.updated_at, proposal_wms.status')
                ->leftJoin('action_wms', 'action_wms.proposal_wm_id', '=', 'proposal_wms.id')
                ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening')
                ->join('simpletabctm.tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
                // ->whereBetween('proposal_wms.created_at', [date('Y-m-21', strtotime('0 month', strtotime(date('Y-m-d')))), date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))])
                ->where('action_wms.id', '=', null)
                ->FilterPriority($request->priority)
                ->FilterStatus($request->status)
                ->FilterStatusWM($request->statussm)
                ->FilterAreas($request->areas)
                ->FilterDate(request()->input('from'), request()->input('to'));
        } else {
            $group_unit = Dapertement::select('dapertements.group_unit')
                ->where('dapertements.id', Auth::user()->dapertement_id)->first()->group_unit;
            $data = CtmWilayah::select('id as code', 'NamaWilayah')->where('group_unit', $group_unit)->get();
            // $proposalWm = proposalWms::selectRaw('tblpelanggan.idareal, proposal_wms.code, proposal_wms.customer_id, proposal_wms.status_wm, proposal_wms.priority, proposal_wms.year, proposal_wms.month, proposal_wms.id, proposal_wms.created_at, proposal_wms.updated_at, proposal_wms.status')
            //     ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening');

            $proposalWm = actionWmStaff::selectRaw('proposal_wms.status_wm,
                proposal_wms.code,
            proposal_wms.priority,
            proposal_wms.status,
            proposal_wms.id as id,
            proposal_wms.created_at,
            proposal_wms.updated_at,
            tblpelanggan.namapelanggan,
            tblpelanggan.nomorrekening as customer_id,
            tblpelanggan.alamat,
            tblpelanggan.idareal,
            proposal_wms.year,
            proposal_wms.month
            idareal,
            group_unit
            ')
                ->rightJoin('action_wms', 'action_wms.id', '=', 'action_wm_staff.action_wm_id')
                ->rightJoin('proposal_wms', 'action_wms.proposal_wm_id', '=', 'proposal_wms.id')
                ->join('simpletabctm.tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'proposal_wms.customer_id')
                ->join('simpletabctm.tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')


                ->leftJoin('staffs', 'staffs.id', '=', 'action_wm_staff.staff_id');
            foreach (Auth::user()->roles as $data) {
                $roles[] = $data->id;
            }
            // dd(in_array('18', $roles));
            // dd();

            if (in_array('18', $roles) || in_array('15', $roles) || in_array('16', $roles) || in_array('14', $roles) || in_array('17', $roles)) {

                $proposalWm->where('tblwilayah.group_unit', $group_unit)
                    ->where('action_wms.id', '=', null)
                    // ->whereBetween('proposal_wms.created_at', [date('Y-m-21', strtotime('0 month', strtotime(date('Y-m-d')))), date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))])
                    ->FilterPriority($request->priority)
                    ->FilterStatus($request->status)
                    ->FilterStatusWM($request->statussm)
                    ->FilterAreas($request->areas)
                    ->FilterDate(request()->input('from'), request()->input('to'));

                // $data2 = $data2 . ' where idareal = ' . $data[$i]->area_id;
                // dd($proposalWm->get());
            } else {

                $proposalWm->orWhere('staffs.id', Auth::user()->id)
                    // ->whereBetween('proposal_wms.created_at', [date('Y-m-21', strtotime('0 month', strtotime(date('Y-m-d')))), date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))])
                    ->FilterPriority($request->priority)
                    ->FilterStatus($request->status)
                    ->FilterStatusWM($request->statussm)
                    ->FilterAreas($request->areas)
                    ->FilterDate(request()->input('from'), request()->input('to'));
            }
        }

        $subdapertement = "";
        $test = [];
        foreach ($proposalWm->get() as $key => $data) {
            // dd($data->group_unit);
            if ($data->group_unit == "1") {
                $subdapertement = "10";
            } else if ($data->group_unit == "2") {
                $subdapertement = "13";
            } else if ($data->group_unit == "3") {
                $subdapertement = "20";
            } else if ($data->group_unit == "4") {
                $subdapertement = "18";
            } else if ($data->group_unit == "5") {
                $subdapertement = "16";
            } else {
                $subdapertement = "";
            }
            $r = proposalWms::where('id', $data->id)->update(['status' => 'active']);
            // dd($r);
            $test[] = ["proposal_wm_id" => $data->id, "subdapertement_id" => $subdapertement, "status" => "pending", "created_at" => date('Y-m-d h:i:s'), "updated_at" => date('Y-m-d h:i:s')];
        }
        DB::table('action_wms')->insert($test);

        return redirect()->back();
    }

    public function updatestatus(Request $request)
    {
        abort_unless(\Gate::allows('proposalWm_approve'), 403);
        // dd($request->all());
        $data = [
            'status' => $request->status,
            'updated_at' => date('Y-m-d h:m:s')
        ];
        $proposalwms = proposalWms::find($request->id);
        $proposalwms->update($data);
        return redirect()->back();
    }
    public function actionStaff()
    {
    }
    public function show($id)
    {
        abort_unless(\Gate::allows('proposalWm_show'), 403);

        $cek = actionWms::selectRaw('action_wms.id, action_wms.code,
        action_wms.proposal_wm_id,
        action_wms.memo,
        action_wms.old_image,
        action_wms.new_image,
        action_wms.image_done,
        action_wms.noWM1,
        action_wms.lng,
        action_wms.lat,
        action_wms.brandWM1,
        action_wms.standWM1,
        action_wms.noWM2,
        action_wms.brandWM2,
        action_wms.standWM2,
        action_wms.subdapertement_id,
        proposal_wms.code,
        proposal_wms.queue,
        proposal_wms.customer_id,
        proposal_wms.status,
        proposal_wms.priority,
        proposal_wms.created_at,
        proposal_wms.updated_at,
        tblpelanggan.namapelanggan,
        tblpelanggan.nomorrekening,
        tblpelanggan.alamat,
        tblpelanggan.telp,
        tblpelanggan.idareal,
        subdapertements.name
        ')
            ->with('staff')->join('proposal_wms', 'proposal_wms.id', '=', 'action_wms.proposal_wm_id')
            ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening')
            ->join('subdapertements', 'subdapertements.id', '=', 'action_wms.subdapertement_id')
            ->where('proposal_wm_id', $id)->first();




        if ($cek != null) {
            $proposalWm = $cek;
            $cek1 = 2;
        } else {
            $proposalWm = proposalWms::selectRaw('
            proposal_wms.code,
            proposal_wms.queue,
            proposal_wms.customer_id,
            proposal_wms.status,
            proposal_wms.memo,
            proposal_wms.priority,
            proposal_wms.created_at,
            proposal_wms.updated_at,
            tblpelanggan.namapelanggan,
            tblpelanggan.nomorrekening,
            tblpelanggan.alamat,
            tblpelanggan.telp,
            tblpelanggan.idareal
            ')
                ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening')
                ->where('id', $id)->first();
            $cek1 = 1;
        }
        // dd($proposalWm);
        $group_unit = CtmWilayah::where('tblwilayah.id', $proposalWm->idareal)->first()->group_unit;
        // dd($group_unit);

        $staffs = actionWmStaff::selectRaw('staffs.id as staff_id, staffs.name as staff_name, staffs.code as staff_code,staffs.phone as staff_phone ')
            ->join('action_wms', 'action_wms.id', '=', 'action_wm_staff.action_wm_id')
            ->join('staffs', 'staffs.id', '=', 'action_wm_staff.staff_id')
            ->where('proposal_wm_id', $id)
            ->get();
        // dd($proposalWm);
        return view('admin.proposalWm.show', compact('proposalWm', 'staffs', 'cek1'));
    }

    public function printspk($id)
    {
        abort_unless(\Gate::allows('proposalWm_spk'), 403);
        $proposalWm = actionWms::selectRaw('action_wms.id, action_wms.code,
        action_wms.proposal_wm_id,
        action_wms.memo,
        action_wms.old_image,
        action_wms.new_image,
        action_wms.image_done,
        action_wms.noWM1,
        action_wms.brandWM1,
        action_wms.standWM1,
        action_wms.noWM2,
        action_wms.brandWM2,
        action_wms.standWM2,
        action_wms.subdapertement_id,
        proposal_wms.code,
        proposal_wms.customer_id,
        proposal_wms.status,
        proposal_wms.priority,
        proposal_wms.created_at,
        proposal_wms.updated_at,
        tblpelanggan.namapelanggan,
        tblpelanggan.nomorrekening,
        tblpelanggan.alamat,
        tblpelanggan.telp,
        tblpelanggan.idareal,
        subdapertements.name
        ')
            ->with('staff')->join('proposal_wms', 'proposal_wms.id', '=', 'action_wms.proposal_wm_id')
            ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening')
            ->join('subdapertements', 'subdapertements.id', '=', 'action_wms.subdapertement_id')
            ->where('action_wms.id', $id)->first();

        $staffs = actionWmStaff::selectRaw('staffs.id as staff_id, staffs.name as staff_name, staffs.code as staff_code,staffs.phone as staff_phone ')
            ->join('action_wms', 'action_wms.id', '=', 'action_wm_staff.action_wm_id')
            ->join('staffs', 'staffs.id', '=', 'action_wm_staff.staff_id')
            ->where('action_wms.id', $id)
            ->get();
        // $subdapertement = [];
        // $staffs = [];
        // if (!empty($ticket->action[0])) {
        //     $subdapertement = $ticket->action[0]->subdapertement;
        //     $staffs = $ticket->action[0]->staff;
        // }
        // dd($proposalWm);
        return view('admin.proposalWm.printspk', compact('proposalWm', 'staffs'));
    }

    public function destroy($id)
    {
        abort_unless(\Gate::allows('proposalWm_create'), 403);

        $proposalWm = proposalWms::where('id', $id)->first();

        $actionWm = actionWms::where('proposal_wm_id', $proposalWm->id)->first();
        $i = 0;
        if ($actionWm) {
            //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin", \base_path());
            $basepath  = base_path().'/public';
            if ($actionWm->old_image != '') {
                foreach (json_decode($actionWm->old_image) as $n) {
                    if (file_exists($n)) {

                        unlink($basepath . $n);
                    }
                }
            }
            if ($actionWm->new_image != '') {
                foreach (json_decode($actionWm->new_image) as $n) {
                    if (file_exists($n)) {

                        unlink($basepath . $n);
                    }
                }
            }
            if ($actionWm->image_done) {
                foreach (json_decode($actionWm->image_done) as $n) {
                    if (file_exists($n)) {

                        unlink($basepath . $n);
                    }
                }
            }
            actionWms::where('proposal_wm_id', $proposalWm->id)->delete();
        }
        proposalWms::where('id', $id)->delete();

        return redirect()->back();
    }

    public function report($id)
    {

        $dayList = array(
            'Sun' => 'Minggu',
            'Mon' => 'Senin',
            'Tue' => 'Selasa',
            'Wed' => 'Rabu',
            'Thu' => 'Kamis',
            'Fri' => 'Jumat',
            'Sat' => 'Sabtu'
        );

        $monthList = array(
            0 => '',
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juli',
            7 => 'Juni',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        );


        $proposalWm = actionWms::selectRaw('action_wms.id, action_wms.code,
        action_wms.proposal_wm_id,
        action_wms.memo,
        action_wms.old_image,
        action_wms.new_image,
        action_wms.image_done,
        action_wms.noWM1,
        action_wms.brandWM1,
        action_wms.standWM1,
        action_wms.noWM2,
        action_wms.brandWM2,
        action_wms.standWM2,
        action_wms.subdapertement_id,
        proposal_wms.code,
        proposal_wms.queue,
        proposal_wms.close_queue,
        proposal_wms.customer_id,
        proposal_wms.status,
        proposal_wms.status_wm,
        proposal_wms.priority,
        proposal_wms.created_at,
        proposal_wms.updated_at,
        tblpelanggan.namapelanggan,
        tblpelanggan.nomorrekening,
        tblpelanggan.alamat,
        tblpelanggan.telp,
        tblpelanggan.idareal,
        subdapertements.name,
        dapertements.name as dapertement_name
        ')
            ->with('staff')->join('proposal_wms', 'proposal_wms.id', '=', 'action_wms.proposal_wm_id')
            ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening')
            ->join('subdapertements', 'subdapertements.id', '=', 'action_wms.subdapertement_id')
            ->join('dapertements', 'subdapertements.dapertement_id', '=', 'dapertements.id')
            ->where('proposal_wms.id', $id)->first();
        // dd($proposalWm);
        $staffs = actionWmStaff::selectRaw('staffs.id as staff_id, staffs.name as staff_name, staffs.code as staff_code,staffs.phone as staff_phone ')
            ->join('action_wms', 'action_wms.id', '=', 'action_wm_staff.action_wm_id')
            ->join('staffs', 'staffs.id', '=', 'action_wm_staff.staff_id')
            ->where('action_wms.id', $proposalWm->id)
            ->get();

        $dayName = $dayList[date('D', strtotime($proposalWm->created_at))];
        $monthName =  $monthList[date('n', strtotime($proposalWm->created_at))];
        $date = date('d', strtotime($proposalWm->created_at));
        $year = date('Y', strtotime($proposalWm->created_at));

        $proposal = proposalWms::where('id', $id)->first();
        $proposal->print_status = 1;
        $proposal->save(['updated_at' => false]);
        // proposalWms::where('id', $id)->update(['print_status' => 1]);

        return view('admin.proposalWm.report', compact('proposalWm', 'dayName', 'monthName', 'date', 'year', 'staffs'));
    }


    // 5 tahun terakhir
    public function index5Year(Request $request)
    {
        // asli
        // $permissions = CtmStatussmPelanggan::where('statussm', 101)->where('tahun', date('Y'))->where('bulan', date('n'))
        //     ->orWhere('statussm', 102)->where('tahun', date('Y'))->where('bulan', (date('n') - 1))
        //     ->orWhere('statussm', 103)->where('tahun', date('Y'))->where('bulan', (date('n') - 1))->get();

        abort_unless(\Gate::allows('proposalWm_report'), 403);
        // uji coba
        foreach (Auth::user()->roles as $data) {
            $roles[] = $data->id;
        }

        $subdapertement_id = Auth::user()->subdapertement_id;
        $areas = CtmWilayah::select('id as code', 'NamaWilayah')->get();
        if (in_array('8', $roles)) {
            if (date('d') > 20) {
                $statussm = proposalWms::selectRaw('
                queue,tblpelanggan.idareal,
                proposal_wms.code,
                proposal_wms.customer_id,
                proposal_wms.status_wm,
                proposal_wms.priority,
                proposal_wms.year,
                proposal_wms.month,
                proposal_wms.id,
                proposal_wms.created_at,
                proposal_wms.updated_at, 
                proposal_wms.status,
                count(customer_id) as jumlahpergantian')
                    //->whereBetween('proposal_wms.created_at', [date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-20', strtotime('-5 year', strtotime(date('Y-m-d'))))))), date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))])
                    ->whereRaw("DATE(proposal_wms.created_at) >= '".date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-20', strtotime('-5 year', strtotime(date('Y-m-d')))))))."' AND DATE(proposal_wms.created_at) <= '".date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))."'")
                    ->having('jumlahpergantian', '>=', 2)
                    ->FilterPriority($request->priority)
                    ->FilterStatus($request->status)
                    ->FilterStatusWM($request->statussm)
                    ->FilterJmlhPergantian($request->jmlhpergantian)
                    ->FilterAreas($request->areas)
                    ->FilterDate(request()->input('from'), request()->input('to'))
                    ->where('proposal_wms.status', 'close');
            } else {
                $statussm = proposalWms::selectRaw('
                queue,tblpelanggan.idareal,
                proposal_wms.code,
                proposal_wms.customer_id,
                proposal_wms.status_wm,
                proposal_wms.priority,
                proposal_wms.year,
                proposal_wms.month,
                proposal_wms.id,
                proposal_wms.created_at,
                proposal_wms.updated_at, 
                proposal_wms.status,
                count(customer_id) as jumlahpergantian')
                    ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening')
                    //->whereBetween('proposal_wms.created_at', [date('Y-m-20', strtotime('-1 month', strtotime(date('Y-m-20', strtotime('-5 year', strtotime(date('Y-m-d'))))))), date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-d'))))])
                    ->whereRaw("DATE(proposal_wms.created_at) >= '".date('Y-m-20', strtotime('-1 month', strtotime(date('Y-m-20', strtotime('-5 year', strtotime(date('Y-m-d')))))))."' AND DATE(proposal_wms.created_at) <= '".date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-d'))))."'")
                    ->having('jumlahpergantian', '>=', 2)
                    ->FilterPriority($request->priority)
                    ->FilterStatus($request->status)
                    ->FilterStatusWM($request->statussm)
                    ->FilterJmlhPergantian($request->jmlhpergantian)
                    ->FilterAreas($request->areas)
                    ->FilterDate(request()->input('from'), request()->input('to'))
                    ->where('proposal_wms.status', 'close');
            }
        } else {
            $group_unit = Dapertement::select('dapertements.group_unit')
                ->where('dapertements.id', Auth::user()->dapertement_id)->first()->group_unit;
            $data = CtmWilayah::select('id as code', 'NamaWilayah')->where('group_unit', $group_unit)->get();
            $areas = $data;
            // $statussm = proposalWms::selectRaw('tblpelanggan.idareal, proposal_wms.code, proposal_wms.customer_id, proposal_wms.status_wm, proposal_wms.priority, proposal_wms.year, proposal_wms.month, proposal_wms.id, proposal_wms.created_at, proposal_wms.updated_at, proposal_wms.status')
            //     ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening');

            $statussm = actionWmStaff::selectRaw('proposal_wms.status_wm,
            queue,
                proposal_wms.code,
            proposal_wms.priority,
            proposal_wms.status,
            proposal_wms.id as id,
            proposal_wms.created_at,
            proposal_wms.updated_at,
            tblpelanggan.namapelanggan,
            proposal_wms.customer_id,
            tblpelanggan.alamat,
            tblpelanggan.idareal,
            proposal_wms.year,
            proposal_wms.month,
            count(customer_id) as jumlahpergantian
            ')
                ->rightJoin('action_wms', 'action_wms.id', '=', 'action_wm_staff.action_wm_id')
                ->rightJoin('proposal_wms', 'action_wms.proposal_wm_id', '=', 'proposal_wms.id')
                ->join('simpletabctm.tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'proposal_wms.customer_id')
                ->join('simpletabctm.tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')


                ->leftJoin('staffs', 'staffs.id', '=', 'action_wm_staff.staff_id');
            foreach (Auth::user()->roles as $data) {
                $roles[] = $data->id;
            }
            // dd(in_array('18', $roles));
            // dd();

            if (in_array('18', $roles) || in_array('15', $roles) || in_array('16', $roles) || in_array('14', $roles) || in_array('17', $roles)) {
                if (date('d') > 20) {
                    $statussm->where('tblwilayah.group_unit', $group_unit)
                        //->whereBetween('proposal_wms.created_at', [date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-20', strtotime('-5 year', strtotime(date('Y-m-d'))))))), date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))])
                        ->whereRaw("DATE(proposal_wms.created_at) >= '".date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-20', strtotime('-5 year', strtotime(date('Y-m-d')))))))."' AND DATE(proposal_wms.created_at) <= '".date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))."'")
                        ->FilterPriority($request->priority)
                        ->FilterStatus($request->status)
                        ->having('jumlahpergantian', '>=', 2)
                        ->FilterStatusWM($request->statussm)
                        ->FilterJmlhPergantian($request->jmlhpergantian)
                        ->FilterAreas($request->areas)
                        ->FilterDate(request()->input('from'), request()->input('to'))
                        ->where('proposal_wms.status', 'close');
                    // $data2 = $data2 . ' where idareal = ' . $data[$i]->area_id;
                } else {
                    $statussm->where('tblwilayah.group_unit', $group_unit)
                        //->whereBetween('proposal_wms.created_at', [date('Y-m-20', strtotime('-1 month', strtotime(date('Y-m-20', strtotime('-5 year', strtotime(date('Y-m-d'))))))), date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-d'))))])
                        ->whereRaw("DATE(proposal_wms.created_at) >= '".date('Y-m-20', strtotime('-1 month', strtotime(date('Y-m-20', strtotime('-5 year', strtotime(date('Y-m-d')))))))."' AND DATE(proposal_wms.created_at) <= '".date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-d'))))."'")
                        ->FilterPriority($request->priority)
                        ->FilterStatus($request->status)
                        ->having('jumlahpergantian', '>=', 2)
                        ->FilterStatusWM($request->statussm)
                        ->FilterJmlhPergantian($request->jmlhpergantian)
                        ->FilterAreas($request->areas)
                        ->FilterDate(request()->input('from'), request()->input('to'))
                        ->where('proposal_wms.status', 'close');
                }
            }
            // dd($statussm->get());
            else {
                if (date('d') > 20) {
                    $statussm->orWhere('staffs.id', Auth::user()->id)
                        //->whereBetween('proposal_wms.created_at', [date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-20', strtotime('-5 year', strtotime(date('Y-m-d'))))))), date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))])
                        ->whereRaw("DATE(proposal_wms.created_at) >= '".date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-20', strtotime('-5 year', strtotime(date('Y-m-d')))))))."' AND DATE(proposal_wms.created_at) <= '".date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))."'")
                        ->FilterPriority($request->priority)
                        ->FilterStatus($request->status)
                        ->having('jumlahpergantian', '>=', 2)
                        ->FilterStatusWM($request->statussm)
                        ->FilterJmlhPergantian($request->jmlhpergantian)
                        ->FilterAreas($request->areas)
                        ->FilterDate(request()->input('from'), request()->input('to'))
                        ->where('proposal_wms.status', 'close');
                } else {
                    $statussm->orWhere('staffs.id', Auth::user()->id)
                        //->whereBetween('proposal_wms.created_at', [date('Y-m-20', strtotime('-1 month', strtotime(date('Y-m-20', strtotime('-5 year', strtotime(date('Y-m-d'))))))), date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-d'))))])
                        ->whereRaw("DATE(proposal_wms.created_at) >= '".date('Y-m-20', strtotime('-1 month', strtotime(date('Y-m-20', strtotime('-5 year', strtotime(date('Y-m-d')))))))."' AND DATE(proposal_wms.created_at) <= '".date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-d'))))."'")
                        ->FilterPriority($request->priority)
                        ->FilterStatus($request->status)
                        ->having('jumlahpergantian', '>=', 2)
                        ->FilterStatusWM($request->statussm)
                        ->FilterJmlhPergantian($request->jmlhpergantian)
                        ->FilterAreas($request->areas)
                        ->FilterDate(request()->input('from'), request()->input('to'))
                        ->where('proposal_wms.status', 'close');
                }
            }
        }
        // dd($statussm->groupBy('customer_id')->limit(10)->get());

        if ($request->ajax()) {
            //set query
            $qry = $statussm->groupBy('customer_id')->orderBy('proposal_wms.updated_at', 'desc');

            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'proposalWm_show';
                $editGate = '';
                $deleteGate = '';
                $crudRoutePart = 'proposalwm';

                return view('partials.datatablesActions5Year', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('code', function ($row) {
                return $row->code ? $row->queue . $row->code : "";
            });

            $table->editColumn('customer_id', function ($row) {
                return $row->customer_id ? $row->customer_id : "";
            });

            $table->editColumn('status_wm', function ($row) {

                if ($row->status_wm == 101) {
                    return "WM Kabur";
                } else if ($row->status_wm == 102) {
                    return "WM Rusak";
                } else if ($row->status_wm == 103) {
                    return "WM Mati";
                } else {
                    "";
                }
            });

            $table->editColumn('priority', function ($row) {
                if ($row->priority === "3") {
                    $value = "Hight";
                } else if ($row->priority === "2") {
                    $value = "Medium";
                } else {
                    $value = "";
                }

                return $value;
            });
            $table->editColumn('status', function ($row) {
                return $row->status ? $row->status : "";
            });
            $table->editColumn('periode', function ($row) {
                return $row->year && $row->month ? $row->year . '-' . $row->month : "";
            });

            $table->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at : "";
            });

            $table->editColumn('updated_at', function ($row) {
                return $row->updated_at ? $row->updated_at : "";
            });


            $table->editColumn('jumlahpergantian', function ($row) {
                return $row->jumlahpergantian ? $row->jumlahpergantian : "";
            });
            $table->editColumn('idareal', function ($row) {
                return $row->idareal ? $row->idareal : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        return view('admin.proposalWm.index5Year', compact('areas', 'subdapertement_id', 'roles'));
    }

    public function index5YearDetail($customer_id)
    {
        // asli
        // $permissions = CtmStatussmPelanggan::where('statussm', 101)->where('tahun', date('Y'))->where('bulan', date('n'))
        //     ->orWhere('statussm', 102)->where('tahun', date('Y'))->where('bulan', (date('n') - 1))
        //     ->orWhere('statussm', 103)->where('tahun', date('Y'))->where('bulan', (date('n') - 1))->get();

        abort_unless(\Gate::allows('proposalWm_report'), 403);
        // uji coba
        foreach (Auth::user()->roles as $data) {
            $roles[] = $data->id;
        }


        if (date('d') > 20) {
            $proposalWm = actionWms::selectRaw('action_wms.id, action_wms.code,
                action_wms.proposal_wm_id,
                action_wms.memo,
                action_wms.old_image,
                action_wms.new_image,
                action_wms.image_done,
                action_wms.noWM1,
                action_wms.lng,
                action_wms.lat,
                action_wms.brandWM1,
                action_wms.standWM1,
                action_wms.noWM2,
                action_wms.brandWM2,
                action_wms.standWM2,
                action_wms.subdapertement_id,
                proposal_wms.code,
                proposal_wms.queue,
                proposal_wms.customer_id,
                proposal_wms.status,
                proposal_wms.priority,
                proposal_wms.created_at,
                proposal_wms.updated_at,
                tblpelanggan.namapelanggan,
                tblpelanggan.nomorrekening,
                tblpelanggan.alamat,
                tblpelanggan.telp,
                tblpelanggan.idareal,
                subdapertements.name
                ')
                ->with('staff')->join('proposal_wms', 'proposal_wms.id', '=', 'action_wms.proposal_wm_id')
                ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening')
                ->join('subdapertements', 'subdapertements.id', '=', 'action_wms.subdapertement_id')
                //->whereBetween('proposal_wms.created_at', [date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-20', strtotime('-5 year', strtotime(date('Y-m-d'))))))), date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))])
                ->whereRaw("DATE(proposal_wms.created_at) >= '".date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-20', strtotime('-5 year', strtotime(date('Y-m-d')))))))."' AND DATE(proposal_wms.created_at) <= '".date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))."'")
                ->where('proposal_wms.status', 'close')
                ->where('customer_id', $customer_id)->get();
        } else {
            $proposalWm = actionWms::selectRaw('action_wms.id, action_wms.code,
                action_wms.proposal_wm_id,
                action_wms.memo,
                action_wms.old_image,
                action_wms.new_image,
                action_wms.image_done,
                action_wms.noWM1,
                action_wms.lng,
                action_wms.lat,
                action_wms.brandWM1,
                action_wms.standWM1,
                action_wms.noWM2,
                action_wms.brandWM2,
                action_wms.standWM2,
                action_wms.subdapertement_id,
                proposal_wms.code,
                proposal_wms.queue,
                proposal_wms.customer_id,
                proposal_wms.status,
                proposal_wms.priority,
                proposal_wms.created_at,
                proposal_wms.updated_at,
                tblpelanggan.namapelanggan,
                tblpelanggan.nomorrekening,
                tblpelanggan.alamat,
                tblpelanggan.telp,
                tblpelanggan.idareal,
                subdapertements.name
                ')
                ->with('staff')->join('proposal_wms', 'proposal_wms.id', '=', 'action_wms.proposal_wm_id')
                ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening')
                ->join('subdapertements', 'subdapertements.id', '=', 'action_wms.subdapertement_id')
                //->whereBetween('proposal_wms.created_at', [date('Y-m-20', strtotime('-1 month', strtotime(date('Y-m-20', strtotime('-5 year', strtotime(date('Y-m-d'))))))), date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-d'))))])
                ->whereRaw("DATE(proposal_wms.created_at) >= '".date('Y-m-20', strtotime('-1 month', strtotime(date('Y-m-20', strtotime('-5 year', strtotime(date('Y-m-d')))))))."' AND DATE(proposal_wms.created_at) <= '".date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-d'))))."'")
                ->where('proposal_wms.status', 'close')
                ->where('customer_id', $customer_id)->get();
        }
        $customer = Customer::where('nomorrekening', $customer_id)->first();
        // dd($proposalWm);

        return view('admin.proposalWm.show5Year', compact('proposalWm', 'customer'));
    }
}
