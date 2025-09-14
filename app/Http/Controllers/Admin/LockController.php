<?php
namespace App\Http\Controllers\Admin;

use App\AreaStaff;
use App\CtmPembayaran;
use App\CtmWilayah;
use App\Customer;
use App\Dapertement;
use App\Http\Controllers\Controller;
use App\Lock;
use App\LockAction;
use App\Staff;
use App\Subdapertement;
use App\Traits\TraitModel;
use App\User;
use DateTime;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OneSignal;
use PhpOffice\PhpWord\TemplateProcessor;
use Yajra\DataTables\Facades\DataTables;

class LockController extends Controller
{
    use TraitModel;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        abort_unless(\Gate::allows('lock_access'), 403);
        $areas = CtmWilayah::select('id as code', 'NamaWilayah')->get();

        if (Auth::user()->dapertement_id != 0 && Auth::user()->subdapertement_id != 0 && Auth::user()->staff_id != 0) {
            if (Auth::user()->staff_id === 0) {
                $group_unit = Dapertement::select('dapertements.group_unit')
                    ->where('dapertements.id', Auth::user()->dapertement_id)->first()->group_unit;
                $data = CtmWilayah::select('id as code', 'NamaWilayah')->where('group_unit', $group_unit)->get();
                // dd($data[0]->code);
            } else {
                $data = AreaStaff::join('simpletabctm.tblwilayah', 'tblwilayah.id', '=', 'area_id')->selectRaw('area_id as code,NamaWilayah')->where('staff_id', Auth::user()->staff_id)->get();
            }
            $areas = $data;
            // dd($data);
            $qrystf = Staff::selectRaw('staffs.*, subdapertements.name as subdapertements_name, area_staff.area_id, dapertements.name as dapertements_name, users.id as id ')
                ->join('dapertements', 'staffs.dapertement_id', '=', 'dapertements.id')
                ->join('subdapertements', 'subdapertements.id', '=', 'staffs.subdapertement_id')
                ->join('area_staff', 'staffs.id', '=', 'area_staff.staff_id')
                ->join('users', 'users.staff_id', '=', 'staffs.id')
                ->whereNull('users.deleted_at')
                ->groupBy('staffs.id');
            for ($i = 0; $i < count($data); $i++) {
                if ($i < 1) {

                    $qrystf->where('area_id', $data[$i]->code)
                        ->where('subdapertement_id', 10)
                        ->orWhere('dapertements.group_unit', '>', 1)
                        ->where('area_id', $data[$i]->code);
                    // $data2 = $data2 . ' where idareal = ' . $data[$i]->area_id;
                } else {
                    $qrystf->orWhere('area_id', $data[$i]->code)
                        ->where('subdapertement_id', 10)
                        ->orWhere('dapertements.group_unit', '>', 1)
                        ->Where('area_id', $data[$i]->code);
                }
            }

            $qrydt = LockAction::selectRaw('lock_action.*,tblopp.operator as operator,tblpelanggan.idurut as idurut, users.name as staff_name, tblpelanggan.idareal as idareal')
                ->join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')
                ->join('simpletabctm.tblopp as tblopp', 'tblopp.nomorrekening', '=', 'tblpelanggan.nomorrekening')
                ->join('users', 'lock_action.staff_id', '=', 'users.id')
                ->with('customer')
                ->with('subdapertement');
            for ($i = 0; $i < count($data); $i++) {
                if ($i < 1) {

                    $qrydt->where('tblpelanggan.idareal', $data[$i]->code)
                        ->FilterArea(request()->input('area'))
                        ->FilterStatus(request()->input('status'))
                        ->FilterDate(request()->input('from'), request()->input('to'))
                        ->FilterStaff(request()->input('staff'));
                    // $data2 = $data2 . ' where idareal = ' . $data[$i]->tblpelanggan.idareal;
                } else {
                    $qrydt->orWhere('tblpelanggan.idareal', $data[$i]->code)
                        ->FilterArea(request()->input('area'))
                        ->FilterStatus(request()->input('status'))
                        ->FilterDate(request()->input('from'), request()->input('to'))
                        ->FilterStaff(request()->input('staff'));
                }
            }
            $qry = $qrydt->orderBy('lock_action.created_at', 'DESC')->get();
        } else if (Auth::user()->name == 'ADMIN' || Auth::user()->id === 477) {
            $areas = CtmWilayah::select('id as code', 'NamaWilayah')->get();
            $qry   = LockAction::selectRaw('lock_action.*,tblopp.operator as operator,tblpelanggan.idurut as idurut, users.name as staff_name, tblpelanggan.idareal as idareal')
                ->FilterStatus(request()->input('status'))
                ->FilterDate(request()->input('from'), request()->input('to'))
                ->FilterStaff(request()->input('staff'))
                ->FilterArea(request()->input('area'))
                ->join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')
                ->join('simpletabctm.tblopp as tblopp', 'tblopp.nomorrekening', '=', 'tblpelanggan.nomorrekening')
                ->join('users', 'lock_action.staff_id', '=', 'users.id')
                ->with('customer')
                ->with('subdapertement')->orderBy('lock_action.created_at', 'DESC');
            //return request()->input('status');
            // ->where('created_at', 'like', '2022-08' . '%')->where('type', 'lock')
            // ->where('tblpelanggan.nomorrekening', 'I')->orwhere('tblpelanggan.namapelanggan', 'like', '%' . 'I' . '%')
            // ->where('created_at', 'like', '2022-08' . '%')->where('type', 'lock')
            // ->get();
            // $qrystf = Staff::selectRaw('staffs.*, subdapertements.name as subdapertements_name, area_staff.area_id, dapertements.name as dapertements_name ')
            //     ->leftJoin('subdapertements', 'subdapertements.id', '=', 'staffs.subdapertement_id')
            //     ->leftJoin('dapertements', 'dapertements.id', '=', 'staffs.dapertement_id')
            //     ->oin('area_staff', 'staffs.id', '=', 'area_staff.staff_id')->where('subdapertement_id', 10)->groupBy('staffs.id');

            $qrystf = Staff::selectRaw('staffs.*, subdapertements.name as subdapertements_name, area_staff.area_id, dapertements.name as dapertements_name, users.id as id ')
                ->join('dapertements', 'staffs.dapertement_id', '=', 'dapertements.id')
                ->join('subdapertements', 'subdapertements.id', '=', 'staffs.subdapertement_id')
                ->join('area_staff', 'staffs.id', '=', 'area_staff.staff_id')
                ->join('users', 'users.staff_id', '=', 'staffs.id')
                ->where('staffs.subdapertement_id', 10)
                ->whereNull('users.deleted_at')
                ->orWhere('dapertements.group_unit', '>', 1)
                ->whereNull('users.deleted_at')
                ->groupBy('staffs.id');
        }
        // untuk admin
        else {
            $areas = CtmWilayah::select('id as code', 'NamaWilayah')->get();
            $qry   = LockAction::selectRaw('lock_action.*,tblopp.operator as operator,tblpelanggan.idurut as idurut, users.name as staff_name, tblpelanggan.idareal as idareal')
                ->FilterStatus(request()->input('status'))
                ->FilterDate(request()->input('from'), request()->input('to'))
                ->FilterStaff(request()->input('staff'))
                ->FilterArea(request()->input('area'))
                ->join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')
                ->join('simpletabctm.tblopp as tblopp', 'tblopp.nomorrekening', '=', 'tblpelanggan.nomorrekening')
                ->join('users', 'lock_action.staff_id', '=', 'users.id')
                ->with('customer')
                ->with('subdapertement')->orderBy('lock_action.created_at', 'DESC');
            // ->where('created_at', 'like', '2022-08' . '%')->where('type', 'lock')
            // ->where('tblpelanggan.nomorrekening', 'I')->orwhere('tblpelanggan.namapelanggan', 'like', '%' . 'I' . '%')
            // ->where('created_at', 'like', '2022-08' . '%')->where('type', 'lock')
            // ->get();
            // $qrystf = Staff::selectRaw('staffs.*, subdapertements.name as subdapertements_name, area_staff.area_id, dapertements.name as dapertements_name ')
            //     ->leftJoin('subdapertements', 'subdapertements.id', '=', 'staffs.subdapertement_id')
            //     ->leftJoin('dapertements', 'dapertements.id', '=', 'staffs.dapertement_id')
            //     ->oin('area_staff', 'staffs.id', '=', 'area_staff.staff_id')->where('subdapertement_id', 10)->groupBy('staffs.id');

            $qrystf = Staff::selectRaw('staffs.*, subdapertements.name as subdapertements_name, area_staff.area_id, dapertements.name as dapertements_name, users.id as id ')
                ->join('dapertements', 'staffs.dapertement_id', '=', 'dapertements.id')
                ->join('subdapertements', 'subdapertements.id', '=', 'staffs.subdapertement_id')
                ->join('area_staff', 'staffs.id', '=', 'area_staff.staff_id')
                ->join('users', 'users.staff_id', '=', 'staffs.id')
                ->where('staffs.subdapertement_id', 10)
                ->whereNull('users.deleted_at')
                ->orWhere('dapertements.group_unit', '>', 1)
                ->whereNull('users.deleted_at')
                ->groupBy('staffs.id');
        }
        //return $qry->toSql();
        // dd($qrystf);
        // dd($qry);
        //return $qry->get();
        if ($request->ajax() && $qry) {
            $table = Datatables::of($qry);
            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('staff', '&nbsp;');

            $table->editColumn('staff', function ($row) {
                $viewGate   = 'lock_show';
                $editGate   = 'lock_edit';
                $deleteGate = 'lock_delete';
                // $staffGate = 'lock_access';
                // $actionLockGate = 'lock_action_access';
                // $viewSegelGate = 'lock_access';
                $crudRoutePart = 'locks';
                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    // 'staffGate',
                    // 'actionLockGate',
                    // 'viewSegelGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('code', function ($row) {
                return $row->customer_id ? $row->customer_id : "";
            });

            $table->editColumn('register', function ($row) {
                return $row->created_at ? $row->created_at : "";
            });

            $table->editColumn('customer', function ($row) {
                return $row->customer ? $row->customer->name : "";
            });
            $table->editColumn('idareal', function ($row) {
                return $row->idareal ? $row->idareal : "";
            });
            $table->editColumn('operator', function ($row) {
                return $row->operator ? $row->operator : "";
            });
            $table->editColumn('idurut', function ($row) {
                return $row->idurut ? $row->idurut : "";
            });
            $table->editColumn('description', function ($row) {
                return $row->memo ? $row->memo : "";
            });
            $table->editColumn('status', function ($row) {
                return $row->type ? $row->type : "";
            });
            $table->editColumn('staff_id', function ($row) {
                return $row->staff_id ? $row->staff_id : "";
            });
            $table->editColumn('staff_name', function ($row) {
                return $row->staff_name ? $row->staff_name : "";
            });
            $table->editColumn('subdapertement', function ($row) {
                return $row->subdapertement ? $row->subdapertement->name : "";
            });
            $table->editColumn('start', function ($row) {
                return $row->created_at ? $row->created_at : "";
            });
            $table->editColumn('end', function ($row) {
                return $row->updated_at ? $row->updated_at : "";
            });

            $table->rawColumns(['staff', 'placeholder']);

            $table->addIndexColumn();

            return $table->make(true);
        }

        $staff = $qrystf->get();

        return view('admin.lock.index', compact('staff', 'areas'));
        // return $subdepartementlist;

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        abort_unless(\Gate::allows('lock_create'), 403);
        //code gnr
        $subdapertement_id        = 10;
        $arr['subdapertement_id'] = $subdapertement_id;
        $arr['month']             = date("m");
        $arr['year']              = date("Y");
        $last_scb                 = $this->get_last_code('scb-lock', $arr);
        $scb                      = acc_code_generate($last_scb, 16, 12, 'Y');
        //
        $subdapertement  = Subdapertement::where('id', $subdapertement_id)->first();
        $subdapertements = Subdapertement::where('dapertement_id', $subdapertement->dapertement_id)->get();
        $dapertement_id  = $subdapertement->dapertement_id;
        $dapertements    = Dapertement::where('id', $subdapertement->dapertement_id)->get();
        $customer_id     = $request->id;
        return view('admin.lock.create', compact('dapertements', 'subdapertements', 'dapertement_id', 'subdapertement_id', 'customer_id', 'scb'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(\Gate::allows('lock_create'), 403);
        $request->validate([
            'code'              => 'required',
            'customer_id'       => 'required',
            'subdapertement_id' => 'required',
            'description'       => 'required',
        ]);

        try {
            $lock = Lock::create($request->all());
            //send notif to admin
            $admin_arr = User::where('dapertement_id', 0)->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                $message      = 'Admin: Perintah Penyegelan Baru Diteruskan : ' . $request->description;
                if (! empty($id_onesignal)) {
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

            //send notif to sub departement terkait
            $subdapertement_obj = Subdapertement::where('id', $request->subdapertement_id)->first();
            $admin_arr          = User::where('dapertement_id', $subdapertement_obj->dapertement_id)
                ->where('subdapertement_id', 0)
                ->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                $message      = 'Bagian: Perintah Penyegelan Baru Diteruskan : ' . $request->description;
                if (! empty($id_onesignal)) {
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

            //send notif to sub departement terkait
            $admin_arr = User::where('subdapertement_id', $request->subdapertement_id)
                ->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                $message      = 'Sub Bagian: Perintah Penyegelan Baru Diteruskan : ' . $request->description;
                if (! empty($id_onesignal)) {
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
            //redirect
            return redirect()->route('admin.lock.index');
        } catch (\Throwable $th) {
            return back()->withErrors($th);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        abort_unless(\Gate::allows('lock_show'), 403);
        // dd($id);
        $customer = LockAction::selectRaw('staffs.name as staff_name, staffs.code as staff_code, lock_action.*')->join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')
            ->join('users', 'lock_action.staff_id', '=', 'users.id')
            ->leftJoin('staffs', 'users.staff_id', '=', 'staffs.id')
            ->where('lock_action.id', $id)
            ->with('customer')
            ->first();
        // dd($customer);
        // return $customer;
        return view('admin.lock.show', compact('customer'));
    }

    public function sppPrint($lock_id)
    {
        $lock     = Lock::with('staff')->findOrFail($lock_id);
        $id       = $lock->customer_id;
        $code     = $lock->code;
        $customer = Customer::where('nomorrekening', $id)
            ->first();
        $customer->year = date('Y');
        // dd($ctm);

        // ctm pay
        $date_now   = date("Y-m-d");
        $date_comp  = date("Y-m") . "-20";
        $month_next = date('n', strtotime('+1 month'));
        $month_now  = ($month_next - 1);
        $tunggakan  = 0;
        $tagihan    = 0;
        $denda      = 0;
        $total      = 0;
        $ctm_lock   = 0;
        if ($date_now > $date_comp) {
            $ctm_lock_old = 0;
            $ctm          = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->where('tblpembayaran.statuslunas', '=', 0)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        } else {
            $ctm_lock_old = 1;
            $ctm          = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                ->where('tblpembayaran.statuslunas', '=', 0)
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        }
        $dataPembayaran = [];
        foreach ($ctm as $key => $item) {
            $m3      = $item->bulanini - $item->bulanlalu;
            $sisa    = $item->wajibdibayar - $item->sudahdibayar;
            $tagihan = $tagihan + $sisa;

            if ($month_now == $item->bulanrekening && $ctm_lock_old == 1) {
                $ctm_lock = 1;
            }

            if ($sisa > 0 && $ctm_lock == 0) {
                $tunggakan = $tunggakan + 1;
            }

            //if not paid
            if ($sisa > 0) {
                $item->tglbayarterakhir = "";
            }
            //set to prev
            $periode = date('Y-m', strtotime(date($item->tahunrekening . '-' . $item->bulanrekening . '-01') . " -1 month"));

            $dataPembayaran[$key] = [
                // 'no' => $key +1,
                'norekening'   => $item->nomorrekening,
                'periode'      => $periode,
                'tanggal'      => $item->tglbayarterakhir,
                'm3'           => $m3,
                'wajibdibayar' => $item->wajibdibayar,
                'sudahbayar'   => $item->sudahdibayar,
                'denda'        => $item->denda,
                'sisa'         => $sisa,
            ];
        }

        if ($tunggakan > 0 && $tunggakan < 2) {
            $denda = 10000;
            $total = $tagihan + $denda;
            $denda = $denda;
        }
        if ($tunggakan > 1 && $tunggakan < 4) {
            $denda = 50000;
            $total = $tagihan + $denda;
            $denda = $denda;
        }
        if ($tunggakan > 3) {
            $denda = 'SSB (Sanksi Denda Setara Sambungan Baru)';
            $total = $tagihan;
        }

        $recap = [
            'tagihan'   => $tagihan,
            'denda'     => $denda,
            'total'     => $total,
            'tunggakan' => $tunggakan,
        ];

        // return $lock;
        $petugas = '';
        $periode = '';
        foreach ($lock->staff as $index => $staff_row) {
            $petugas = $staff_row->name;
        }

        foreach ($dataPembayaran as $key => $item) {
            $periode .= ',' . Bulan((new DateTime($item['periode']))->format('m')) . '-' . (new DateTime($item['periode']))->format('Y') . ' = ' . Rupiah($item['wajibdibayar'] - $item['sudahbayar']);
        }
        $templateProcessor = new TemplateProcessor('template-word/word1.docx');
        $templateProcessor->setValue('code', $lock->code);
        $templateProcessor->setValue('petugas', $petugas);
        $templateProcessor->setValue('namapelanggan', $customer->namapelanggan);
        $templateProcessor->setValue('alamat', $customer->alamat);
        $templateProcessor->setValue('noSBG', $customer->nomorrekening);
        $templateProcessor->setValue('area', $customer->idareal);
        $templateProcessor->setValue('periode', $periode);
        $templateProcessor->setValue('total', is_int($recap['total']) ? Rupiah($recap['total']) : $recap['total']);
        $templateProcessor->setValue('tanggal', date('d') . ' ' . Bulan(date('m')) . ' ' . date('Y'));
        $fileName = date('d') . ' ' . Bulan(date('m')) . ' ' . date('Y');
        $templateProcessor->saveAs($fileName . '.docx');
        return response()->download($fileName . '.docx');
        // return view('admin.lock.spp', compact('customer', 'dataPembayaran', 'recap', 'lock'));
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('lock_edit'), 403);
        $lock = LockAction::selectRaw('staffs.name as staff_name, staffs.code as staff_code, lock_action.*')->join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')
            ->join('users', 'lock_action.staff_id', '=', 'users.id')
            ->leftJoin('staffs', 'users.staff_id', '=', 'staffs.id')
            ->where('lock_action.id', $id)
            ->with('customer')
            ->first();
        $types_array = [
            ['id' => 'lock_resist', 'title' => 'Hambatan Segel'],
            ['id' => 'lock', 'title' => 'Segel'],
            ['id' => 'unplug_resist', 'title' => 'Hambatan Cabut'],
            ['id' => 'unplug', 'title' => 'Cabut'],
            ['id' => 'notice', 'title' => 'Penyampaian Surat'],
            ['id' => 'notice2', 'title' => 'Penyampaian Surat 2'],
        ];
        $types = array_map(function ($item) {
            return (object) $item;
        }, $types_array);

        return view('admin.lock.edit', compact('lock', 'types'));
    }

    public function update(Request $request)
    {
        abort_unless(\Gate::allows('lock_edit'), 403);
        $lockaction = LockAction::where('id', $request->id_hidden)->first();

        $img_path = "/images/segelMeter";
        // //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath = base_path() . '/public';

        //img
        $images_old = [];
        foreach (json_decode($lockaction->image, true) as $key => $image) {
            $images_old[] = $image;
        }
        $deletePath = $request->delete_images;
        $images_fil = $images_old;
        //unlink img deleted  
        if ($request->delete_images) {
            foreach ($request->delete_images as $key => $delimage) {
                $file_path = $basepath . $delimage;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            //deleted img
            $filtered = array_filter($images_old, function ($item) use ($deletePath) {
                return ! in_array($item, $deletePath);
            });
            $images_fil = [];
            foreach ($filtered as $key => $image) {
                $images_fil[] = $image;
            }
        }
        //new img
        $images_new = [];
        if ($request->file('image')) {
            foreach ($request->file('image') as $key => $image) {
                $file_extImage = $image->extension();
                $nameImage     = str_replace(" ", "-", strtolower($request->code)) . '_' . $request->customer_id . '_' . date('YmdHis') . '_' . mt_rand(1000, 9999);
                $img_name      = $img_path . "/" . $nameImage . "." . $file_extImage;
                $image->move($basepath . $img_path, $img_name);
                $images_new[] = $img_name;
            }
        }
        //merge img
        $images = array_merge($images_fil, $images_new);

        //work img
        $image_works_old = [];
        if (!empty($lockaction->image_work) && json_decode($lockaction->image_work)){
        foreach (json_decode($lockaction->image_work, true) as $key => $image_work) {
            $image_works_old[] = $image_work;
        }}
        $deletePath = $request->delete_image_works;
        $image_works_fil = $image_works_old;
        //unlink img deleted  
        if ($request->delete_image_works) {
            foreach ($request->delete_image_works as $key => $delimage_work) {
                $file_path = $basepath . $delimage_work;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            //deleted img
            $filtered = array_filter($image_works_old, function ($item) use ($deletePath) {
                return ! in_array($item, $deletePath);
            });
            $image_works_fil = [];
            foreach ($filtered as $key => $image_work) {
                $image_works_fil[] = $image_work;
            }
        }
        //new img
        $image_works_new = [];
        if ($request->file('image_work')) {
            foreach ($request->file('image_work') as $key => $image_work) {
                $file_extImage = $image_work->extension();
                $nameImage     = str_replace(" ", "-", strtolower($request->code)) . '_' . $request->customer_id . '_' . date('YmdHis') . '_' . mt_rand(1000, 9999);
                $img_name      = $img_path . "/" . $nameImage . "." . $file_extImage;
                $image_work->move($basepath . $img_path, $img_name);
                $image_works_new[] = $img_name;
            }
        }
        //merge img
        $image_works = array_merge($image_works_fil, $image_works_new);

        //prework img
        $image_preworks_old = [];
        if (!empty($lockaction->image_prework) && json_decode($lockaction->image_prework)){
        foreach (json_decode($lockaction->image_prework, true) as $key => $image_prework) {
            $image_preworks_old[] = $image_prework;
        }}
        $deletePath = $request->delete_image_preworks;
        $image_preworks_fil = $image_preworks_old;
        //unlink img deleted  
        if ($request->delete_image_preworks) {
            foreach ($request->delete_image_preworks as $key => $delimage_prework) {
                $file_path = $basepath . $delimage_prework;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            //deleted img
            $filtered = array_filter($image_preworks_old, function ($item) use ($deletePath) {
                return ! in_array($item, $deletePath);
            });
            $image_preworks_fil = [];
            foreach ($filtered as $key => $image_prework) {
                $image_preworks_fil[] = $image_prework;
            }
        }
        //new img
        $image_preworks_new = [];
        if ($request->file('image_prework')) {
            foreach ($request->file('image_prework') as $key => $image_prework) {
                $file_extImage = $image_prework->extension();
                $nameImage     = str_replace(" ", "-", strtolower($request->code)) . '_' . $request->customer_id . '_' . date('YmdHis') . '_' . mt_rand(1000, 9999);
                $img_name      = $img_path . "/" . $nameImage . "." . $file_extImage;
                $image_prework->move($basepath . $img_path, $img_name);
                $image_preworks_new[] = $img_name;
            }
        }
        //merge img
        $image_preworks = array_merge($image_preworks_fil, $image_preworks_new);

        //tool img
        $image_tools_old = [];
        if (!empty($lockaction->image_tools) && json_decode($lockaction->image_tools)){
        foreach (json_decode($lockaction->image_tools, true) as $key => $image_tool) {
            $image_tools_old[] = $image_tool;
        }}
        $deletePath = $request->delete_image_tools;
        $image_tools_fil = $image_tools_old;
        //unlink img deleted  
        if ($request->delete_image_tools) {
            foreach ($request->delete_image_tools as $key => $delimage_tool) {
                $file_path = $basepath . $delimage_tool;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            //deleted img
            $filtered = array_filter($image_tools_old, function ($item) use ($deletePath) {
                return ! in_array($item, $deletePath);
            });
            $image_tools_fil = [];
            foreach ($filtered as $key => $image_tool) {
                $image_tools_fil[] = $image_tool;
            }
        }
        //new img
        $image_tools_new = [];
        if ($request->file('image_tool')) {
            foreach ($request->file('image_tool') as $key => $image_tool) {
                $file_extImage = $image_tool->extension();
                $nameImage     = str_replace(" ", "-", strtolower($request->code)) . '_' . $request->customer_id . '_' . date('YmdHis') . '_' . mt_rand(1000, 9999);
                $img_name      = $img_path . "/" . $nameImage . "." . $file_extImage;
                $image_tool->move($basepath . $img_path, $img_name);
                $image_tools_new[] = $img_name;
            }
        }
        //merge img
        $image_tools = array_merge($image_tools_fil, $image_tools_new);

        //done img
        $image_dones_old = [];
        if (!empty($lockaction->image_done) && json_decode($lockaction->image_done)){
        foreach (json_decode($lockaction->image_done, true) as $key => $image_done) {
            $image_dones_old[] = $image_done;
        }}
        $deletePath = $request->delete_image_dones;
        $image_dones_fil = $image_dones_old;
        //unlink img deleted  
        if ($request->delete_image_dones) {
            foreach ($request->delete_image_dones as $key => $delimage_done) {
                $file_path = $basepath . $delimage_done;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            //deleted img
            $filtered = array_filter($image_dones_old, function ($item) use ($deletePath) {
                return ! in_array($item, $deletePath);
            });
            $image_dones_fil = [];
            foreach ($filtered as $key => $image_done) {
                $image_dones_fil[] = $image_done;
            }
        }
        //new img
        $image_dones_new = [];
        if ($request->file('image_done')) {
            foreach ($request->file('image_done') as $key => $image_done) {
                $file_extImage = $image_done->extension();
                $nameImage     = str_replace(" ", "-", strtolower($request->code)) . '_' . $request->customer_id . '_' . date('YmdHis') . '_' . mt_rand(1000, 9999);
                $img_name      = $img_path . "/" . $nameImage . "." . $file_extImage;
                $image_done->move($basepath . $img_path, $img_name);
                $image_dones_new[] = $img_name;
            }
        }
        //merge img
        $image_dones = array_merge($image_dones_fil, $image_dones_new);

        //up data
        $data    = [
            'type'          => $request->type,            
            'memo'          => $request->memo,
            'image'         => str_replace("\/", "/", json_encode($images)),
            'image_work'    => str_replace("\/", "/", json_encode($image_works)),
            'image_prework' => str_replace("\/", "/", json_encode($image_preworks)),
            'image_tools'   => str_replace("\/", "/", json_encode($image_tools)),
            'image_done'    => str_replace("\/", "/", json_encode($image_dones)),
        ];
        //return $data;
        $lockaction->update($data);
        return redirect()->route('admin.locks.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        abort_unless(\Gate::allows('lock_action_delete'), 403);
        $lockaction = LockAction::find($request->_id);
        try {
            //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin", \base_path());
            $basepath = base_path() . '/public';
            $img      = $lockaction->image;
            $img      = str_replace('"', '', $img);
            $img      = str_replace('[', '', $img);
            $img      = str_replace(']', '', $img);
            $img_arr  = explode(",", $img);
            foreach ($img_arr as $img_name) {
                $file_path = $basepath . $img_name;
                if (trim($img_name) != '' && file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            $lockaction->delete();
            return back();
        } catch (QueryException $e) {
            return back()->withErrors(['Mohon hapus dahulu data yang terkait']);
        }
    }

    public function lockactionStaff($lockaction_id)
    {
        abort_unless(\Gate::allows('lock_staff_access'), 403);
        $action = Lock::findOrFail($lockaction_id);

        return view('admin.lock.actionStaff', compact('action'));
    }

    public function lockactionStaffCreate($lockaction_id)
    {

        abort_unless(\Gate::allows('lock_staff_create'), 403);

        $action = Lock::findOrFail($lockaction_id);

        $action_staffs = Lock::where('id', $lockaction_id)->with('staff')->first();

        $staffs = Staff::where('subdapertement_id', $action->subdapertement_id)->get();

        $action_staffs_list = DB::table('staffs')
            ->join('lock_staff', function ($join) {
                $join->on('lock_staff.staff_id', '=', 'staffs.id');
            })
            ->get();

        return view('admin.lock.actionStaffCreate', compact('lockaction_id', 'staffs', 'action', 'action_staffs', 'action_staffs_list'));
    }

    public function lockactionStaffStore(Request $request)
    {
        abort_unless(\Gate::allows('lock_staff_create'), 403);
        $action = Lock::findOrFail($request->lockaction_id);

        if ($action) {
            $cek = $action->staff()->attach($request->staff_id);
        }

        return redirect()->route('admin.lock.actionStaff', $request->lockaction_id);
    }

    public function lockactionStaffDestroy($lockaction_id, $staff_id)
    {
        abort_unless(\Gate::allows('lock_staff_delete'), 403);

        $action = Lock::findOrFail($lockaction_id);

        if ($action) {
            $cek = $action->staff()->detach($staff_id);

            if ($cek) {
                $action = Lock::where('id', $lockaction_id)->with('staff')->first();

                $cekAllStatus = false;

                $dateNow = date('Y-m-d H:i:s');

                $action->update();
            }
        }

        return redirect()->route('admin.lock.actionStaff', $lockaction_id);
    }

    public function list($lockaction_id)
    {
        abort_unless(\Gate::allows('lock_action_access'), 403);
        $actions = LockAction::with('subdapertement')
            ->with('lock')
            ->where('lock_id', $lockaction_id)
            ->get();
        return view('admin.lock.list', compact('lockaction_id', 'actions'));
    }

    public function actioncreate($lock_id)
    {
        abort_unless(\Gate::allows('lock_action_create'), 403);
        $lock         = Lock::findOrFail($lock_id);
        $dapertements = Dapertement::where('id', $lock->dapertement_id)->get();

        $staffs = Staff::all();
        return view('admin.lock.actionCreate', compact('dapertements', 'lock_id', 'staffs'));
    }

    public function lockstore(Request $request)
    {
        abort_unless(\Gate::allows('lock_action_create'), 403);
        $dateNow = date('Y-m-d H:i:s');

        if ($request->file('image')) {
            $img_path = "/pdf";
            //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
            $basepath = base_path() . '/public';
            foreach ($request->file('image') as $key => $image) {
                $resourceImage = $image;
                $nameImage     = time() + $key;
                $file_extImage = $image->extension();
                $nameImage     = str_replace(" ", "-", $nameImage);
                $img_name      = $img_path . "/" . $nameImage . "." . $file_extImage;

                $resourceImage->move($basepath . $img_path, $img_name);
                $dataImageName[] = $nameImage . "." . $file_extImage;
            }
        }
        $data = [
            'code'    => $request->code,
            'type'    => $request->type,
            'image'   => str_replace("\/", "/", json_encode($dataImageName)),
            'memo'    => $request->memo,
            'lock_id' => $request->lock_id,
        ];

        $action = LockAction::create($data);
        return redirect()->route('admin.lock.list', $request->lock_id);
    }

    public function lockactionDestroy(Request $request, LockAction $action)
    {
        abort_unless(\Gate::allows('lock_action_delete'), 403);

        $action->delete();

        return redirect()->route('admin.lock.list', $action->lock_id);
    }

    public function LockView($lock_id)
    {
        abort_unless(\Gate::allows('lock_action_show'), 403);

        $lock = LockAction::findOrFail($lock_id);
        return view('admin.lock.lockView', compact('lock'));
    }
}
