<?php

namespace App\Http\Controllers\Admin;

use App\CtmGambarmetersms;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CtmStatussmPelanggan;
use App\CtmWilayah;
use App\Customer;
use App\Dapertement;
use App\proposalWms;
use App\StaffApi;
use App\Traits\TraitModel;
use App\Traits\WablasTrait;
use App\User;
use App\wa_history;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class StatusWmController extends Controller
{
    use WablasTrait;
    use TraitModel;


    public function index(Request $request)
    {
        // asli
        // $permissions = CtmStatussmPelanggan::where('statussm', 101)->where('tahun', date('Y'))->where('bulan', date('n'))
        //     ->orWhere('statussm', 102)->where('tahun', date('Y'))->where('bulan', (date('n') - 1))
        //     ->orWhere('statussm', 103)->where('tahun', date('Y'))->where('bulan', (date('n') - 1))->get();
        // $jam1 = strtotime('24:00');

        // $jam2 = strtotime('09:00');
        // if ($jam1 > $jam2) {
        //     dd("tessss1");
        // } else {
        //     dd("tessss2");
        // }
        abort_unless(\Gate::allows('statusWm_access'), 403);
        // uji coba

        $areas = CtmWilayah::select('id as code', 'NamaWilayah')->get();
        foreach (Auth::user()->roles as $data) {
            $roles[] = $data->id;
        }
        if (in_array('8', $roles)) {
            if (date('d') < 21) {
                // $cek = proposalWms::where('year', date('Y', strtotime('-1 month', strtotime(date('Y-m-d')))))
                //     ->where('month', date('m', strtotime('-1 month', strtotime(date('Y-m-d')))));
                $statussm = CtmStatussmPelanggan::selectRaw('proposal_wms.customer_id,proposal_wms.status as proposalwm_status, tblstatuswm.NamaStatus,tblstatussmpelanggan.nomorrekening, tblpelanggan.namapelanggan, tblpelanggan.idareal as idareal, tblstatussmpelanggan.bulan, tblstatussmpelanggan.tahun')
                    ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblstatussmpelanggan.nomorrekening')
                    ->join('tblstatuswm', 'tblstatuswm.id', '=', 'tblstatussmpelanggan.statussm')
                    ->leftJoin('simpletab.proposal_wms', function ($join) {
                        $join->on('proposal_wms.customer_id', '=', 'simpletabctm.tblstatussmpelanggan.nomorrekening');
                        $join->on('proposal_wms.month', '=', 'tblstatussmpelanggan.bulan');
                        $join->on('proposal_wms.year', '=', 'tblstatussmpelanggan.tahun');
                    })
                    // ->where('statussm', 101)->where('tahun', date('Y', strtotime('-1 month', strtotime(date('Y-m-d')))))->where('bulan', date('m', strtotime('-1 month', strtotime(date('Y-m-d')))))->FilterStatusWM($request->statussm)->FilterAreas($request->areas)
                    // ->orWhere('statussm', 102)->where('tahun', date('Y', strtotime('-1 month', strtotime(date('Y-m-d')))))->where('bulan', date('m', strtotime('-1 month', strtotime(date('Y-m-d')))))->FilterStatusWM($request->statussm)->FilterAreas($request->areas)
                    ->where('statussm', '<=', 103)
                    ->where('statussm', '>=', 101)
                    ->where(function ($query) use ($request) {
                        //$query->where('tblpelanggan.idareal', $data[0]->area_id);
                        $query->where('tahun', date('Y', strtotime('-1 month', strtotime(date('Y-m-d')))))
                            ->where('bulan', date('m', strtotime('-1 month', strtotime(date('Y-m-d')))))
                            ->FilterStatusWM($request->statussm)->FilterAreas($request->areas)
                            ->orWhere('tahun', date('Y', strtotime('0 month', strtotime(date('Y-m-d')))))
                            ->where('bulan', date('m', strtotime('0 month', strtotime(date('Y-m-d')))))
                            ->FilterStatusWM($request->statussm)->FilterAreas($request->areas);
                    })
                    ->orderBy('tahun', 'DESC')
                    ->orderBy('bulan', 'DESC');

                // dd('tesss');
                // ->where('tahun', date('Y', strtotime('-1 month', strtotime(date('Y-m-d')))))
                // ->where('bulan', date('m', strtotime('-1 month', strtotime(date('Y-m-d')))))
            } else {
                // $cek = proposalWms::where('year', date('Y', strtotime('0 month', strtotime(date('Y-m-d')))))
                //     ->where('month', date('m', strtotime('0 month', strtotime(date('Y-m-d')))));
                $statussm = CtmStatussmPelanggan::selectRaw('proposal_wms.customer_id,proposal_wms.status as proposalwm_status,tblstatuswm.NamaStatus,tblstatussmpelanggan.nomorrekening, tblpelanggan.namapelanggan, tblpelanggan.idareal as idareal, tblstatussmpelanggan.bulan, tblstatussmpelanggan.tahun')
                    ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblstatussmpelanggan.nomorrekening')
                    ->join('tblstatuswm', 'tblstatuswm.id', '=', 'tblstatussmpelanggan.statussm')
                    ->leftJoin('simpletab.proposal_wms', function ($join) {
                        $join->on('proposal_wms.customer_id', '=', 'simpletabctm.tblstatussmpelanggan.nomorrekening');
                        $join->on('proposal_wms.month', '=', 'tblstatussmpelanggan.bulan');
                        $join->on('proposal_wms.year', '=', 'tblstatussmpelanggan.tahun');
                    })
                    ->where('statussm', '<=', 103)
                    ->where('statussm', '>=', 101)
                    ->where(function ($query) use ($request) {
                        //$query->where('tblpelanggan.idareal', $data[0]->area_id);
                        $query->where('tahun', date('Y', strtotime('0 month', strtotime(date('Y-m-d')))))
                            ->where('bulan', date('m', strtotime('0 month', strtotime(date('Y-m-d')))))
                            ->FilterStatusWM($request->statussm)->FilterAreas($request->areas)
                            ->orWhere('tahun', date('Y', strtotime('+1 month', strtotime(date('Y-m-d')))))
                            ->where('bulan', date('m', strtotime('+1 month', strtotime(date('Y-m-d')))))
                            ->FilterStatusWM($request->statussm)->FilterAreas($request->areas);
                    })
                    ->orderBy('tahun', 'DESC')
                    ->orderBy('bulan', 'DESC');
                // ->where('tahun', date('Y', strtotime('0 month', strtotime(date('Y-m-d')))))->where('bulan', date('m', strtotime('0 month', strtotime(date('Y-m-d')))))->FilterStatusWM($request->statussm)->FilterAreas($request->areas);
                // ->orWhere('statussm', 102)->where('tahun', date('Y', strtotime('0 month', strtotime(date('Y-m-d')))))->where('bulan', date('m', strtotime('0 month', strtotime(date('Y-m-d')))))->FilterStatusWM($request->statussm)->FilterAreas($request->areas)
                // ->orWhere('statussm', 103)->where('tahun', date('Y', strtotime('0 month', strtotime(date('Y-m-d')))))->where('bulan', date('m', strtotime('0 month', strtotime(date('Y-m-d')))))->FilterStatusWM($request->statussm)->FilterAreas($request->areas);
            }
        } else {
            $group_unit = Dapertement::select('dapertements.group_unit')
                ->where('dapertements.id', Auth::user()->dapertement_id)->first()->group_unit;
            $areas = CtmWilayah::select('id as code', 'NamaWilayah')->where('group_unit', $group_unit)->get();

            if (date('d') < 21) {

                // $cek = proposalWms::join('simpletabctm.tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'proposal_wms.customer_id')
                //     ->where('tblwilayah.group_unit', $group_unit)
                //     ->where('year', date('Y', strtotime('-1 month', strtotime(date('Y-m-d')))))
                //     ->where('month', date('m', strtotime('-1 month', strtotime(date('Y-m-d')))));
                $statussm = CtmStatussmPelanggan::selectRaw('proposal_wms.customer_id,proposal_wms.status as proposalwm_status,tblstatuswm.NamaStatus,tblstatussmpelanggan.nomorrekening, tblpelanggan.namapelanggan, tblpelanggan.idareal as idareal, tblstatussmpelanggan.bulan, tblstatussmpelanggan.tahun')
                    ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblstatussmpelanggan.nomorrekening')
                    ->join('tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
                    ->join('tblstatuswm', 'tblstatuswm.id', '=', 'tblstatussmpelanggan.statussm')
                    ->leftJoin('simpletab.proposal_wms', function ($join) {
                        $join->on('proposal_wms.customer_id', '=', 'simpletabctm.tblstatussmpelanggan.nomorrekening');
                        $join->on('proposal_wms.month', '=', 'tblstatussmpelanggan.bulan');
                        $join->on('proposal_wms.year', '=', 'tblstatussmpelanggan.tahun');
                    })
                    ->where('tblwilayah.group_unit', $group_unit)
                    ->where('statussm', '<=', 103)
                    ->where('statussm', '>=', 101)
                    ->where(function ($query) use ($request) {
                        //$query->where('tblpelanggan.idareal', $data[0]->area_id);
                        $query->where('tahun', date('Y', strtotime('-1 month', strtotime(date('Y-m-d')))))
                            ->where('bulan', date('m', strtotime('-1 month', strtotime(date('Y-m-d')))))
                            ->FilterStatusWM($request->statussm)->FilterAreas($request->areas)
                            ->orWhere('tahun', date('Y', strtotime('0 month', strtotime(date('Y-m-d')))))
                            ->where('bulan', date('m', strtotime('0 month', strtotime(date('Y-m-d')))))
                            ->FilterStatusWM($request->statussm)->FilterAreas($request->areas);
                    })
                    ->orderBy('tahun', 'DESC')
                    ->orderBy('bulan', 'DESC');
                // ->where('tahun', date('Y', strtotime('-1 month', strtotime(date('Y-m-d')))))->where('bulan', date('m', strtotime('-1 month', strtotime(date('Y-m-d')))))
                // ->FilterStatusWM($request->statussm)->FilterAreas($request->areas);
            } else {

                // $cek = proposalWms::join('simpletabctm.tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'proposal_wms.customer_id')
                //     ->where('tblwilayah.group_unit', $group_unit)
                //     ->where('year', date('Y', strtotime('0 month', strtotime(date('Y-m-d')))))
                //     ->where('month', date('m', strtotime('0 month', strtotime(date('Y-m-d')))));
                $statussm = CtmStatussmPelanggan::selectRaw('proposal_wms.customer_id,proposal_wms.status as proposalwm_status,tblstatuswm.NamaStatus,tblstatussmpelanggan.nomorrekening, tblpelanggan.namapelanggan, tblpelanggan.idareal as idareal, tblstatussmpelanggan.bulan, tblstatussmpelanggan.tahun')
                    ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblstatussmpelanggan.nomorrekening')
                    ->join('tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
                    ->join('tblstatuswm', 'tblstatuswm.id', '=', 'tblstatussmpelanggan.statussm')
                    ->leftJoin('simpletab.proposal_wms', function ($join) {
                        $join->on('proposal_wms.customer_id', '=', 'simpletabctm.tblstatussmpelanggan.nomorrekening');
                        $join->on('proposal_wms.month', '=', 'tblstatussmpelanggan.bulan');
                        $join->on('proposal_wms.year', '=', 'tblstatussmpelanggan.tahun');
                    })
                    ->where('tblwilayah.group_unit', $group_unit)
                    ->where('statussm', '<=', 103)
                    ->where('statussm', '>=', 101)
                    ->where(function ($query) use ($request) {
                        //$query->where('tblpelanggan.idareal', $data[0]->area_id);
                        $query->where('tahun', date('Y', strtotime('0 month', strtotime(date('Y-m-d')))))
                            ->where('bulan', date('m', strtotime('0 month', strtotime(date('Y-m-d')))))
                            ->FilterStatusWM($request->statussm)->FilterAreas($request->areas)
                            ->orWhere('tahun', date('Y', strtotime('+1 month', strtotime(date('Y-m-d')))))
                            ->where('bulan', date('m', strtotime('+1 month', strtotime(date('Y-m-d')))))
                            ->FilterStatusWM($request->statussm)->FilterAreas($request->areas);
                    })
                    ->orderBy('tahun', 'DESC')
                    ->orderBy('bulan', 'DESC');
                // ->where('tahun', date('Y', strtotime('0 month', strtotime(date('Y-m-d')))))->where('bulan', date('m', strtotime('0 month', strtotime(date('Y-m-d')))))->FilterStatusWM($request->statussm)->FilterAreas($request->areas)
                // ->FilterStatusWM($request->statussm)->FilterAreas($request->areas);
            }

            // $data = CtmWilayah::select('id as code', 'NamaWilayah')->where('group_unit', $group_unit)->get();
        }

        // $cek = $cek->get();


        // $statussm = CtmStatussmPelanggan::selectRaw('tblstatuswm.NamaStatus,tblstatussmpelanggan.nomorrekening, tblpelanggan.namapelanggan, tblpelanggan.idareal as idareal, tblstatussmpelanggan.bulan, tblstatussmpelanggan.tahun')
        //     ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblstatussmpelanggan.nomorrekening')
        //     ->join('tblstatuswm', 'tblstatuswm.id', '=', 'tblstatussmpelanggan.statussm')
        //     ->where('statussm', 101)->where('tahun', '2021')->where('bulan', date('m', strtotime('-1 month', strtotime(date('Y-m-d')))))->FilterStatusWM($request->statussm)->FilterAreas($request->areas)
        //     ->orWhere('statussm', 102)->where('tahun', '2021')->where('bulan', date('m', strtotime('-1 month', strtotime(date('Y-m-d')))))->FilterStatusWM($request->statussm)->FilterAreas($request->areas)
        //     ->orWhere('statussm', 103)->where('tahun', '2021')->where('bulan', date('m', strtotime('-1 month', strtotime(date('Y-m-d')))))->FilterStatusWM($request->statussm)->FilterAreas($request->areas);

        // dd($statussm);

        if ($request->ajax()) {
            //set query
            $qry = $statussm;

            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = '';
                $editGate = '';
                $deleteGate = '';
                $crudRoutePart = 'statuswm';

                foreach (Auth::user()->roles as $data) {
                    $roles[] = $data->id;
                }

                $roles = $roles;

                return view('partials.datatablesActionStatusSm', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'roles',
                    'row'
                ));
            });
            $table->editColumn('nomorrekening', function ($row) {
                return $row->nomorrekening ? $row->nomorrekening : "";
            });

            $table->editColumn('namapelanggan', function ($row) {
                return $row->namapelanggan ? $row->namapelanggan : "";
            });

            $table->editColumn('periode', function ($row) {
                return $row->bulan && $row->tahun ? $row->bulan . '-' . $row->tahun : "";
            });
            $table->editColumn('NamaStatus', function ($row) {
                return $row->NamaStatus ? $row->NamaStatus : "";
            });

            $table->editColumn('idareal', function ($row) {
                return $row->idareal ? $row->idareal : "";
            });


            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        return view('admin.statusWm.index', compact('areas'));
    }
    public function view()
    {
    }

    public function create(Request $request)
    {
        abort_unless(\Gate::allows('proposalWm_create'), 403);



        $statussm = CtmGambarmetersms::selectRaw('gambarmeter.*,gambarmetersms.nomorrekening')
            // ->leftJoin('tblstatussmpelanggan', function ($join) {
            //     $join->on('gambarmetersms.nomorrekening', '=', 'tblstatussmpelanggan.nomorrekening');
            //     $join->on('gambarmetersms.bulanrekening', '=', 'tblstatussmpelanggan.bulan');
            //     $join->on('gambarmetersms.tahunrekening', '=', 'tblstatussmpelanggan.tahun');
            // })
            // ->leftJoin('tblstatussmpelanggan', 'gambarmetersms.nomorrekening', '=', 'tblstatussmpelanggan.nomorrekening')
            ->leftJoin('gambarmeter', function ($join) {
                $join->on('gambarmeter.idgambar', '=', 'gambarmetersms.idgambar');
                $join->on('gambarmeter.bulanrekening', '=', 'gambarmetersms.bulanrekening');
                $join->on('gambarmeter.tahunrekening', '=', 'gambarmetersms.tahunrekening');
            });
        // ->join('tblstatuswm', 'tblstatuswm.id', '=', 'tblstatussmpelanggan.statussm');
        // dd($statussm->limit(10)->get());

        // dd($statussm->where('gambarmetersms.nomorrekening', $request->customer_id)->get());
        // ->leftJoin('gambarmeter', 'gambarmeter.idgambar', '=', 'gambarmetersms.idgambar');


        // $statussm = CtmStatussmPelanggan::selectRaw('gambarmetersms.*')->leftJoin('gambarmetersms', 'gambarmetersms.nomorrekening', '=', 'tblstatussmpelanggan.nomorrekening')
        //     ->leftJoin('gambarmeter', 'gambarmeter.idgambar', '=', 'gambarmeter.idgambar')
        //     ->leftJoin('gambarmetersms', function ($join) {
        //         $join->on('gambarmetersms.nomorrekening', '=', 'tblstatussmpelanggan.nomorrekening');
        //         $join->on('gambarmetersms.bulanrekening', '=', 'tblstatussmpelanggan.bulan');
        //         $join->on('gambarmetersms.tahunrekening', '=', 'tblstatussmpelanggan.tahun');
        //     })
        //     ->leftJoin('gambarmeter', function ($join) {
        //         $join->on('gambarmeter.idgambar', '=', 'tblstatussmpelanggan.nomorrekening');
        //         $join->on('gambarmeter.bulanrekening', '=', 'tblstatussmpelanggan.bulan');
        //         $join->on('gambarmeter.tahunrekening', '=', 'tblstatussmpelanggan.tahun');
        //     });

        for ($i = 3; $i > 0; $i -= 1) {
            if ($request->month - $i + 1 > 0) {
                $data = $request->month - $i + 1;
                $month =   $i - 3 + 1;
                $statussm->orWhere('gambarmetersms.bulanrekening', $data)->where('gambarmetersms.tahunrekening', $request->year)->where('gambarmetersms.nomorrekening', $request->customer_id);

                // dd($statussm->limit(5)->where('gambarmetersms.tahunrekening', $request->customer_id)->get());
                // $year = $request->year;
            } else {
                $month = 12 + $request->month - $i + 1;
                $statussm->orWhere('gambarmetersms.bulanrekening', $month)->where('gambarmetersms.tahunrekening', $request->year - 1)->where('gambarmetersms.nomorrekening', $request->customer_id);
                // dd($statussm->limit(5)->where('tblstatussmpelanggan.nomorrekening', '47902')->get());
                // $year = $request->year - 1;
            }
        }
        // dd($statussm->groupBy('gambarmetersms.idgambar')->limit(10)->get());
        $statussm = $statussm->orderBy('tanggal', 'asc')->get();

        $data1 = [];
        foreach ($statussm as $key => $data) {
            $d =  CtmStatussmPelanggan::selectRaw('tblstatuswm.NamaStatus')->join('tblstatuswm', 'tblstatuswm.id', '=', 'tblstatussmpelanggan.statussm')
                ->where('tblstatussmpelanggan.bulan', $data->bulanrekening)
                ->where('tblstatussmpelanggan.tahun', $data->tahunrekening)
                ->where('tblstatussmpelanggan.nomorrekening', $data->nomorrekening)
                ->first();
            $data1[] = ['customer_id' => $data->nomorrekening, 'bulanrekening' => $data->bulanrekening, 'tahunrekening' => $data->tahunrekening, 'operator' => $data->operator, 'filegambar' => $data->filegambar, 'status_wm' => $d ? $d->NamaStatus : 'Terbaca'];
        }
        // dd($data1);
        // $statussm =  $statussm->orderBy('tanggal', 'asc')->get();
        // dd($month, $year);


        $now = CtmStatussmPelanggan::selectRaw('tblstatussmpelanggan.*, tblstatuswm.NamaStatus, tblpelanggan.namapelanggan as name, tblstatuswm.id as status_wm_id')->join('tblstatuswm', 'tblstatuswm.id', '=', 'tblstatussmpelanggan.statussm')
            ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblstatussmpelanggan.nomorrekening')
            ->where('bulan', $request->month)
            ->where('tahun', $request->year)
            ->where('tblstatussmpelanggan.nomorrekening', $request->customer_id)->first();
        // dd($now);
        $data = $data1;
        return View('admin.statusWm.approve', compact('statussm', 'now', 'data'));
    }
    public function approve(Request $request)
    {
        abort_unless(\Gate::allows('proposalWm_create'), 403);

        $customers =  Customer::join('tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')->where('nomorrekening', $request->customer_id)->get();

        // dd($customers[0]->group_unit);
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

        $cek = proposalWms::where('customer_id', $request->customer_id)->where('year', $request->year)->where('month', $request->month)->get();

        if (count($customers) > 0) {
            // dd($request->all());
            if (count($cek) > 0) {
                dd("Data Sudah Diinputkan");
            } else {
                $proposalWms = proposalWms::create($data);
            }
        } else {
            dd("Pelanggan Tidak Ada");
        }

        $group_unit = Customer::join('tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
            ->where('nomorrekening', $proposalWms->customer_id)->first()->group_unit;
        //send notif to admin start
        //send notif to admin start
        $admin_arr = User::selectRaw('users.*, dapertements.*')
            ->leftJoin('dapertements', 'users.dapertement_id', '=', 'dapertements.id')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->where('group_unit', $group_unit)->where('role_user.role_id', 18)
            ->orWhere('group_unit', $group_unit)->where('role_user.role_id', 15)

            // ->orwhere('dapertement_id', '2')->where('subdapertement_id', '0')->where('staff_id', '0')->where('group_unit', $group_unit)
            ->get();
        // dd($admin_arr);
        foreach ($admin_arr as $key => $admin) {
            $message = 'Test: ' . $admin->id . ' Sudah Disetujui';
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
            // if (!empty(json_decode($wa_sent)->data->messages)) {
            //     $array_merg = array_merge(json_decode($wa_sent)->data->messages, $array_merg);
            // }
            // foreach ($array_merg as $key => $value) {
            //     if (!empty($value->ref_id)) {
            //         wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
            //     }
            // }
        }

        // $subAdminPengamat = User::selectRaw('users.*, dapertements.*')
        //     ->leftJoin('dapertements', 'users.dapertement_id', '=', 'dapertements.id')
        //     ->join('role_user', 'users.id', '=', 'role_user.user_id')
        //     ->where('subdapertement_id', '12')->where('group_unit', $group_unit)->where('role_user.role_id', 6)
        //     ->orWhere('group_unit', $group_unit)->where('role_user.role_id', 11)
        //     ->get();
        // $subteknikDistribusi = User::selectRaw('users.*, dapertements.*')
        //     ->leftJoin('dapertements', 'users.dapertement_id', '=', 'dapertements.id')
        //     ->join('role_user', 'users.id', '=', 'role_user.user_id')
        //     ->where('subdapertement_id', '13')->where('group_unit', $group_unit)->where('role_user.role_id', 6)
        //     ->orwhere('dapertement_id', '2')->where('subdapertement_id', '0')->where('staff_id', '0')->where('group_unit', $group_unit)
        //     ->get();

        // $segelMeter = User::selectRaw('users.*, dapertements.*')
        //     ->leftJoin('dapertements', 'users.dapertement_id', '=', 'dapertements.id')
        //     ->join('role_user', 'users.id', '=', 'role_user.user_id')
        //     ->where('subdapertement_id', '10')->where('group_unit', $group_unit)->where('role_user.role_id', 6)
        //     ->get();

        return redirect()->route('admin.statuswm.index');
    }

    public function reject(Request $request)
    {
        abort_unless(\Gate::allows('proposalWm_create'), 403);
        $last_code = $this->get_last_code('proposal_wm');

        $code = acc_code_generate($last_code, 8, 3);
        $data = array_merge($request->all(), ['status' => 'reject', 'code' => $code]);

        $cek = proposalWms::where('customer_id', $request->customer_id)->where('year', $request->year)->where('month', $request->month)->get();
        $customers =  Customer::where('nomorrekening', $request->customer_id)->get();
        if (count($customers) > 0) {
            // dd($request->all());
            if (count($cek) > 0) {
                dd("Data Sudah Diinputkan");
            } else {
                $proposalWms = proposalWms::create($data);
            }
        } else {
            dd("Pelanggan Tidak Ada");
        }

        $group_unit = Customer::join('tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
            ->where('nomorrekening', $proposalWms->customer_id)->first()->group_unit;
        //send notif to admin start
        //send notif to admin start
        $admin_arr = User::selectRaw('users.*, dapertements.*')
            ->leftJoin('dapertements', 'users.dapertement_id', '=', 'dapertements.id')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->where('group_unit', $group_unit)->where('role_user.role_id', 18)
            ->orWhere('group_unit', $group_unit)->where('role_user.role_id', 15)

            // ->orwhere('dapertement_id', '2')->where('subdapertement_id', '0')->where('staff_id', '0')->where('group_unit', $group_unit)
            ->get();
        // dd($admin_arr);
        foreach ($admin_arr as $key => $admin) {
            $message = 'Test: ' . $admin->id . ' Sudah Disetujui';
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
            // if (!empty(json_decode($wa_sent)->data->messages)) {
            //     $array_merg = array_merge(json_decode($wa_sent)->data->messages, $array_merg);
            // }
            // foreach ($array_merg as $key => $value) {
            //     if (!empty($value->ref_id)) {
            //         wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
            //     }
            // }
        }


        return redirect()->route('admin.statuswm.index');
    }
}
