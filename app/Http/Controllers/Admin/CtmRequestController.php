<?php

namespace App\Http\Controllers\Admin;

use App\CtmRequest;
use App\Http\Controllers\Controller;
use App\Traits\TraitModel;
use Illuminate\Http\Request;
use App\CtmGambarmetersms;
use App\CtmGambarmeter;
use Illuminate\Support\Facades\Storage;
use App\CtmPemakaianAir as pmkr;
use DB;
use App\CtmPembayaran;

class CtmRequestController extends Controller
{
    use TraitModel;

    public function updateManual(Request $request)
    {
        abort_unless(\Gate::allows('ctmrequests_edit'), 403);

        //get pembayaran lalu
        $request->bulanrekening_lalu = $request->bulanrekening - 1;
        $tblpembayaran = CtmPembayaran::select('tglbayarterakhir','operator1')->where('tahunrekening', '=', $request->tahunrekening)
            ->where('bulanrekening', '=', $request->bulanrekening)
            ->where('nomorrekening', '=', $request->nomorrekening)
            ->first();

        //get pemakaian air
        $pemakaianair = pmkr::select('*','pemakaianair'.$request->bulanrekening.' as pemakaianair','pencatatanmeter'.$request->bulanrekening.' as pencatatanmeter','pencatatanmeter'.$request->bulanrekening_lalu.' as meterawal')->where('tahunrekening', '=',$request->tahunrekening)
            ->where('nomorrekening', '=', $request->nomorrekening)
            ->first();

        $var['nomorrekening'] = $request->nomorrekening;
        $var['pemakaianair'] = $pemakaianair->pemakaianair;
        $var['tahunbayar'] = $request->tahunrekening;
        $var['bulanbayar'] = $request->bulanrekening +1;
        $var['pencatatanmeter'] = $pemakaianair->pencatatanmeter;
        $var['meterawal']  = $pemakaianair->meterawal;
        $var['datecatatf3'] = $tblpembayaran->tglbayarterakhir;
        $var['operator']= $tblpembayaran->operator1;

        $this->insupdCtmPembayaran($var);
    }

    public function index(Request $request)
    {
        abort_unless(\Gate::allows('ctmrequests_access'), 403);
        $ctmrequests = CtmRequest::with('customer')
            ->orderBy('created_at', 'DESC')->FilterDate(request()->input('from'), request()->input('to'))->get();

        return view('admin.ctmrequests.index', compact('ctmrequests'));
    }

    public function create()
    {
        $last_code = $this->get_last_code('category');

        $code = acc_code_generate($last_code, 8, 3);

        abort_unless(\Gate::allows('ctmrequests_create'), 403);
        return view('admin.ctmrequests.create', compact('code'));
    }

    public function store(Request $request)
    {
        abort_unless(\Gate::allows('ctmrequests_create'), 403);
        $category = CtmRequest::create($request->all());

        return redirect()->route('admin.ctmrequests.index');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('ctmrequests_edit'), 403);
        $ctmrequest = CtmRequest::with('customer')->findOrFail($id);
        $img_path_old = "/gambar-test";
        $img_path = "/gambar";
        $img = 'https://www.ptab-vps-storage.com/pdam' . $ctmrequest->img;
        $img = str_replace($img_path, $img_path_old, $img);
        $ctmrequest->monthyear = $ctmrequest->month . "/" . $ctmrequest->year;
        //get image previous
        $month_prev = date('m', strtotime($ctmrequest->year . '-' . $ctmrequest->month . ' - 1 month'));
        $year_prev = date('Y', strtotime($ctmrequest->year . '-' . $ctmrequest->month . ' - 1 month'));
        $CtmGambarmetersms = CtmGambarmetersms::where('nomorrekening', '=', $ctmrequest->norek)->where('bulanrekening', '=', $month_prev)->where('tahunrekening', '=', $year_prev)->first();
        $wmmeteran = $ctmrequest->wmmeteran;
        //if CtmGambarmetersms exist
        $month = date('m', strtotime($ctmrequest->year . '-' . $ctmrequest->month . ' - 0 month'));
        $CtmGambarmetersms_now = CtmGambarmetersms::where('nomorrekening', '=', $ctmrequest->norek)->where('bulanrekening', '=', $month)->where('tahunrekening', '=', $year_prev)->first();
        if(isset($CtmGambarmetersms_now->pencatatanmeter)){
            $wmmeteran = $CtmGambarmetersms_now->pencatatanmeter;
        }

        //if exist
        $img2 = "";
        if(isset($CtmGambarmetersms->idgambar)){
            $CtmGambarmeter = CtmGambarmeter::where('idgambar', '=', $CtmGambarmetersms->idgambar)->first();
            $img2 = 'https://www.ptab-vps-storage.com/pdam' . $CtmGambarmeter->filegambar;
            }
        //return
        return view('admin.ctmrequests.edit', compact('ctmrequest', 'img', 'img2', 'wmmeteran'));
    }

    public function update(Request $request, CtmRequest $ctmrequest)
    {
        abort_unless(\Gate::allows('ctmrequests_edit'), 403);
        //update status
        $ctmrequest->status = 'approve';
        $ctmrequest->save();

        //get operator 
        $tblopp = DB::connection('mysql2')->table('tblopp')->where('nomorrekening', '=', $ctmrequest->norek)
        ->select('operator')
        ->first();
        $operator = $ctmrequest->operator;
        if($tblopp){
            $operator = $tblopp->operator;
        }

        //get var
        $var['norek'] = $ctmrequest->norek;
        $var['wmmeteran'] = $request->wmmeteran; //$ctmrequest->wmmeteran
        $var['namastatus'] = $ctmrequest->namastatus;
        $var['opp'] = $ctmrequest->opp;
        $var['lat'] = $ctmrequest->lat;
        $var['lng'] = $ctmrequest->lng;
        $var['accuracy'] = $ctmrequest->accuracy;
        $var['operator'] = $operator;
        $var['nomorpengirim'] = $ctmrequest->nomorpengirim;
        $var['statusonoff'] = $ctmrequest->statusonoff;
        $var['description'] = $ctmrequest->description;
        $var['filegambar'] = $ctmrequest->img;
        $var['filegambar1'] = $ctmrequest->img1;
        $var['datecatatf1'] = $ctmrequest->datecatatf1;
        $var['datecatatf2'] = $ctmrequest->datecatatf2;
        $var['datecatatf3'] = $ctmrequest->datecatatf3;
        $var['year'] = $ctmrequest->year;
        $var['month'] = $ctmrequest->month;

        //get prev
        $year = $var['year'];
        $month = $var['month'];
        $ctm_prev = $this->getCtmPrev($var['norek'], $month, $year);
        $var['pencatatanmeterprev'] = $ctm_prev['pencatatanmeter'];
        $var['statussmprev'] = $ctm_prev['statussm'];

        //get month year rekening
        $datecatatf1_arr = explode("-", $var['datecatatf1']);
        $month_catat = $datecatatf1_arr[1];
        $year_catat = $datecatatf1_arr[0];
        $month_bayar = date('m', strtotime($datecatatf1_arr[0] . '-' . $datecatatf1_arr[1] . ' + 1 month'));
        $year_bayar = date('Y', strtotime($datecatatf1_arr[0] . '-' . $datecatatf1_arr[1] . ' + 1 month'));
        //additional var
        $var['nomorrekening'] = $var['norek'];
        $var['pencatatanmeter'] = $var['wmmeteran'];
        $var['bulanrekening'] = (int) $month_catat;
        $var['tahunrekening'] = $year_catat;
        $var['bulanbayar'] = (int) $month_bayar;
        $var['tahunbayar'] = $year_bayar;
        $var['namastatus'] = $var['namastatus'];
        $var['bulanini'] = $var['wmmeteran'];
        $var['bulanlalu'] = $var['pencatatanmeterprev'];
        $var['statusonoff'] = $var['statusonoff'];
        //img path
        $img_path_old = "/gambar-test";
        //$img_path = "/gambar-pindahan";
        $img_path = "/gambar";
        //$basepath = str_replace("laravel-simpletab", "public_html/pdam/", \base_path());
        $basepath = "/home/ptab-vps-storage/htdocs/www.ptab-vps-storage.com/pdam";
        // $path_old = $basepath . $img_path_old . "/" . $year_catat . $month_catat . "/";
        // $path = $basepath . $img_path . "/" . $year_catat . $month_catat . "/";
        // if (!is_dir($path)) {
        //     mkdir($path, 0777, true);
        // }
        $path = "/pdam" . $img_path . "/" . $year_catat . $month_catat . "/";
        $path_old = "/pdam" . $img_path_old . "/" . $year_catat . $month_catat . "/";
        if (!Storage::disk('sftp')->exists($path)) {
            //mkdir($path, 0777, true);
            Storage::disk('sftp')->makeDirectory($path, 0777, true);
        }
        $new_image_name = $var['norek'] . "_" . $var['tahunrekening'] . "_" . $month_catat . ".jpg";

        // //move into new server
        // $path_old_new_server = "/pdam/gambar-test/" . $year_catat . $month_catat . "/";
        // $path_new_server = "/pdam/gambar/" . $year_catat . $month_catat . "/";
        // Storage::disk('sftp')->put($path_new_server . $new_image_name, Storage::disk('sftp')->get($path_old_new_server . $new_image_name));
         
        //move into old server
        // copy($path_old . $new_image_name, $path . $new_image_name);
        $desti_new = $path. $new_image_name;
        $desti_old = $path_old. $new_image_name;
        if (Storage::disk('sftp')->exists($desti_new)) {
            Storage::disk('sftp')->delete($desti_new);            
        }
        $path = Storage::disk('sftp')->copy($desti_old, $desti_new);

        //get meterawal
        $getCtmMeterPrev = $this->getCtmMeterPrev($var['norek'], $var['bulanrekening'], $var['tahunrekening']);
        $meterawal = $var['pencatatanmeterprev'];

        if ((int) $var['namastatus'] == 118) {
            $meterawal = $getCtmMeterPrev['pencatatanmeter'];
        }

        //set pemakaianair
        $var['pemakaianair'] = max(0, ($var['pencatatanmeter'] - $meterawal));
        $var['meterawal'] = $meterawal;
        //insert data into gambarmeter
        $var['idgambar'] = $this->insupdCtmGambarmeter($var);
        $this->insupdCtmGambarmetersms($var);
        $this->insupdCtmMapKunjungan($var);
        $this->insupdCtmPemakaianair($var);
        $this->insupdCtmStatussmpelanggan($var);
        $this->insupdCtmStatusonoff($var);
        //insert into tblpembayaran
        $this->insupdCtmPembayaran($var);
        return redirect()->route('admin.ctmrequests.index');
    }

    public function destroy(CtmRequest $category)
    {
        abort_unless(\Gate::allows('ctmrequests_delete'), 403);

        $category->delete();

        return back();
    }

    public function reject($id)
    {
        abort_unless(\Gate::allows('ctmrequests_edit'), 403);
        $ctmrequest = CtmRequest::where('id', $id)->first();
        $ctmrequest->status = 'close';
        $ctmrequest->save();

        return back();
    }

    public function massDestory(Request $request)
    {
        CtmRequest::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }
}
