<?php

namespace App\Http\Controllers\Admin;

use App\CtmPembayaran;
use App\Customer;
use App\Dapertement;
use App\Http\Controllers\Controller;
use App\Lock;
use App\LockAction;
use App\Staff;
use App\Subdapertement;
use App\Traits\TraitModel;
use App\User;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use OneSignal;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpWord\TemplateProcessor;
use DateTime;

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
        $qry = Lock::selectRaw('locks.*,tblopp.operator as operator,tblpelanggan.idurut as idurut,tblpelanggan.idareal as idareal')
                ->join('simpletabctm.tblpelanggan as tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'locks.customer_id')
                ->join('simpletabctm.tblopp as tblopp', 'tblopp.nomorrekening', '=', 'tblpelanggan.nomorrekening')
                ->with('customer')
                ->with('subdapertement')
                ->orderBy('tblpelanggan.idareal', 'asc')
                ->orderBy('tblopp.operator', 'asc')
                ->orderBy('tblpelanggan.idurut', 'asc')
                ->get();
        if (request()->input('status') != '') {
            $qry = Lock::selectRaw('locks.*,tblopp.operator as operator,tblpelanggan.idurut as idurut,tblpelanggan.idareal as idareal')
                ->FilterStatus(request()->input('status'))
                ->join('simpletabctm.tblpelanggan as tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'locks.customer_id')
                ->join('simpletabctm.tblopp as tblopp', 'tblopp.nomorrekening', '=', 'tblpelanggan.nomorrekening')
                ->with('customer')
                ->with('subdapertement')
                ->orderBy('created_at', 'DESC')
                ->orderBy('tblpelanggan.idareal', 'asc')
                ->orderBy('tblopp.operator', 'asc')
                ->orderBy('tblpelanggan.idurut', 'asc')
                ->get();
        }

        if ($request->ajax()) {
            $table = Datatables::of($qry);
            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('staff', '&nbsp;');

            $table->editColumn('staff', function ($row) {
                $viewGate = 'lock_show';
                $editGate = '';
                $deleteGate = 'lock_delete';
                $staffGate = 'lock_access';
                $actionLockGate = 'lock_action_access';
                // $viewSegelGate = 'lock_access';
                $crudRoutePart = 'lock';
                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'staffGate',
                    'actionLockGate',
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
                return $row->description ? $row->description : "";
            });
            $table->editColumn('status', function ($row) {
                return $row->status ? $row->status : "";
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

        return view('admin.lock.index');
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
        $subdapertement_id = 10;
        $arr['subdapertement_id'] = $subdapertement_id;
        $arr['month'] = date("m");
        $arr['year'] = date("Y");
        $last_scb = $this->get_last_code('scb-lock', $arr);
        $scb = acc_code_generate($last_scb, 16, 12, 'Y');
        //
        $subdapertement = Subdapertement::where('id', $subdapertement_id)->first();
        $subdapertements = Subdapertement::where('dapertement_id', $subdapertement->dapertement_id)->get();
        $dapertement_id = $subdapertement->dapertement_id;
        $dapertements = Dapertement::where('id', $subdapertement->dapertement_id)->get();
        $customer_id = $request->id;
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
            'code' => 'required',
            'customer_id' => 'required',
            'subdapertement_id' => 'required',
            'description' => 'required',
        ]);

        try {
            $lock = Lock::create($request->all());
            //send notif to admin
            $admin_arr = User::where('dapertement_id', 0)->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                $message = 'Admin: Perintah Penyegelan Baru Diteruskan : ' . $request->description;
                if (!empty($id_onesignal)) {
                    OneSignal::sendNotificationToUser(
                        $message,
                        $id_onesignal,
                        $url = null,
                        $data = null,
                        $buttons = null,
                        $schedule = null
                    );}}

            //send notif to sub departement terkait
            $subdapertement_obj = Subdapertement::where('id', $request->subdapertement_id)->first();
            $admin_arr = User::where('dapertement_id', $subdapertement_obj->dapertement_id)
                ->where('subdapertement_id', 0)
                ->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                $message = 'Bagian: Perintah Penyegelan Baru Diteruskan : ' . $request->description;
                if (!empty($id_onesignal)) {
                    OneSignal::sendNotificationToUser(
                        $message,
                        $id_onesignal,
                        $url = null,
                        $data = null,
                        $buttons = null,
                        $schedule = null
                    );}}

            //send notif to sub departement terkait
            $admin_arr = User::where('subdapertement_id', $request->subdapertement_id)
                ->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                $message = 'Sub Bagian: Perintah Penyegelan Baru Diteruskan : ' . $request->description;
                if (!empty($id_onesignal)) {
                    OneSignal::sendNotificationToUser(
                        $message,
                        $id_onesignal,
                        $url = null,
                        $data = null,
                        $buttons = null,
                        $schedule = null
                    );}}
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
    public function show(Lock $lock)
    {
        abort_unless(\Gate::allows('lock_show'), 403);
        $id = $lock->customer_id;
        $code = $lock->code;
        $customer = Customer::where('nomorrekening', $id)
            ->first();
        $customer->year = date('Y');
        // dd($ctm);

        // ctm pay
        $date_now = date("Y-m-d");
        $date_comp = date("Y-m") . "-20";
        $month_next = date('n', strtotime('+1 month'));
        $month_now = ($month_next - 1);
        $tunggakan = 0;
        $tagihan = 0;
        $denda = 0;
        $total = 0;
        $ctm_lock = 0;
        if ($date_now > $date_comp) {
            $ctm_lock_old = 0;
            $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        } else {
            $ctm_lock_old = 1;
            $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        }

        $dataPembayaran=array();
        foreach ($ctm as $key => $item) {
            $m3 = $item->bulanini - $item->bulanlalu;
            $sisa = $item->wajibdibayar - $item->sudahdibayar;
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
                'norekening' => $item->nomorrekening,
                'periode' => $periode,
                'tanggal' => $item->tglbayarterakhir,
                'm3' => $m3,
                'wajibdibayar' => $item->wajibdibayar,
                'sudahbayar' => $item->sudahdibayar,
                'denda' => $item->denda,
                'sisa' => $sisa,
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
            'tagihan' => $tagihan,
            'denda' => $denda,
            'total' => $total,
            'tunggakan' => $tunggakan,
        ];

        return view('admin.lock.show', compact('customer', 'dataPembayaran', 'recap', 'lock'));
    }

    public function sppPrint($lock_id)
    {
        $lock = Lock::with('staff')->findOrFail($lock_id);
        $id = $lock->customer_id;
        $code = $lock->code;
        $customer = Customer::where('nomorrekening', $id)
            ->first();
        $customer->year = date('Y');
        // dd($ctm);

        // ctm pay
        $date_now = date("Y-m-d");
        $date_comp = date("Y-m") . "-20";
        $month_next = date('n', strtotime('+1 month'));
        $month_now = ($month_next - 1);
        $tunggakan = 0;
        $tagihan = 0;
        $denda = 0;
        $total = 0;
        $ctm_lock = 0;
        if ($date_now > $date_comp) {
            $ctm_lock_old = 0;
            $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->where('tblpembayaran.statuslunas', '=', 0)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        } else {
            $ctm_lock_old = 1;
            $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                ->where('tblpembayaran.statuslunas', '=', 0)
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        }
        $dataPembayaran=array();
        foreach ($ctm as $key => $item) {
            $m3 = $item->bulanini - $item->bulanlalu;
            $sisa = $item->wajibdibayar - $item->sudahdibayar;
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
                'norekening' => $item->nomorrekening,
                'periode' => $periode,
                'tanggal' => $item->tglbayarterakhir,
                'm3' => $m3,
                'wajibdibayar' => $item->wajibdibayar,
                'sudahbayar' => $item->sudahdibayar,
                'denda' => $item->denda,
                'sisa' => $sisa,
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
            'tagihan' => $tagihan,
            'denda' => $denda,
            'total' => $total,
            'tunggakan' => $tunggakan,
        ];

        // return $lock;
        $petugas = '' ;
        $periode ='' ;
        foreach ($lock->staff as $index => $staff_row){
            $petugas = $staff_row->name;
        }
     
        foreach ($dataPembayaran as $key=>$item ){
            $periode .= ','.Bulan ( (new DateTime($item['periode']))->format('m')).'-'. (new DateTime($item['periode']))->format('Y') .' = '. Rupiah($item['wajibdibayar'] - $item['sudahbayar']);
        }
        $templateProcessor =  new TemplateProcessor('template-word/word1.docx');
        $templateProcessor->setValue('code',$lock->code);
        $templateProcessor->setValue('petugas',$petugas);
        $templateProcessor->setValue('namapelanggan',$customer->namapelanggan);
        $templateProcessor->setValue('alamat',$customer->alamat);
        $templateProcessor->setValue('noSBG', $customer->nomorrekening);
        $templateProcessor->setValue('area', $customer->idareal);
        $templateProcessor->setValue('periode', $periode);
        $templateProcessor->setValue('total', is_int($recap['total']) ? Rupiah ($recap['total']) : $recap['total']);
        $templateProcessor->setValue('tanggal', date('d') .' '. Bulan(date('m')) .' '. date('Y'));
        $fileName = date('d') .' '. Bulan(date('m')) .' '. date('Y');
        $templateProcessor->saveAs($fileName.'.docx');
        return response()->download($fileName.'.docx');
        // return view('admin.lock.spp', compact('customer', 'dataPembayaran', 'recap', 'lock'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Lock $lock)
    {
        abort_unless(\Gate::allows('lock_delete'), 403);
        try {
            $lock->delete();
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

    function list($lockaction_id) {
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
        $lock = Lock::findOrFail($lock_id);
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
            $basepath  = base_path().'/public';
            foreach ($request->file('image') as $key => $image) {
                $resourceImage = $image;
                $nameImage = time() + $key;
                $file_extImage = $image->extension();
                $nameImage = str_replace(" ", "-", $nameImage);
                $img_name = $img_path . "/" . $nameImage . "." . $file_extImage;

                $resourceImage->move($basepath . $img_path, $img_name);
                $dataImageName[] = $nameImage . "." . $file_extImage;
            }
        }
        $data = array(
            'code' => $request->code,
            'type' => $request->type,
            'image' => str_replace("\/", "/", json_encode($dataImageName)),
            'memo' => $request->memo,
            'lock_id' => $request->lock_id,
        );

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
