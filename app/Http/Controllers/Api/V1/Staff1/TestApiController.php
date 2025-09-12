<?php

namespace App\Http\Controllers\Api\V1\Staff;

use App\CtmPelanggan;
use App\CtmPembayaran;
use App\Customer;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Lock;
use App\Traits\TraitModel;
use App\AreaStaff;
use App\LockAction;
use App\Subdapertement;
use App\User;
use OneSignal;

class TestApiController extends Controller
{
    use TraitModel;

    public function index(Request $request)
    {
        $date_now = date('Y-m-d');
        $date_comp = date('Y-m') . '-20';
        $last_4_month = date("Y-n-d", strtotime('-4 month', strtotime(date('Y-m-01'))));
        $total_data = 0;
        //set query

        if ($date_now > $date_comp) {

            if ($request->status != '' && $request->search != '') {
                $qry = Customer::selectRaw('tblpelanggan.*, ((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2)) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->where('tblpelanggan.nomorrekening', $request->search)
                    ->where('tblpelanggan.status', 1)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                    ->orwhere('tblpelanggan.namapelanggan', 'like', '%' . $request->search . '%')
                    ->where('tblpelanggan.status', 1)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                    ->groupBy('tblpembayaran.nomorrekening')
                    ->where('((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2))', $status)
                    ->where('((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2))', '>', 1)
                    ->paginate(10, ['*'], 'page', $request->page);
            } else if ($request->status != '') {
                $qry = Customer::selectRaw('tblpelanggan.*, ((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2)) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->where('tblpelanggan.status', 1)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                    ->where('((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2))', $status)
                    ->where('((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2))', '>', 1)
                    ->groupBy('tblpembayaran.nomorrekening')
                    ->paginate(10, ['*'], 'page', $request->page);
            } else if ($request->search != '') {
                $qry = Customer::selectRaw('tblpelanggan.*, ((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2)) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->where('tblpelanggan.nomorrekening', $request->search)
                    ->where('tblpelanggan.status', 1)
                    ->where('((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2))', '>', 1)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                    ->orwhere('tblpelanggan.namapelanggan', 'like', '%' . $request->search . '%')
                    ->where('tblpelanggan.status', 1)
                    ->where('((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2))', '>', 1)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                    ->groupBy('tblpembayaran.nomorrekening')
                    ->paginate(10, ['*'], 'page', $request->page);
            } else {

                // $count = Customer::selectRaw('tblpelanggan.*, ((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2)) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                //     ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                //     ->where('tblpelanggan.status', 1)
                //     ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                //     ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                //     ->groupBy('tblpembayaran.nomorrekening')->get()->count();
                // if ($count % 10 === 0) {
                //     $total_data = floor($count / 10);
                // } else {
                //     $total_data = floor($count / 10) + 1;
                // }
                $qry = Customer::selectRaw('tblpelanggan.*, ((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2)) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->where('tblpelanggan.status', 1)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                    ->groupBy('tblpembayaran.nomorrekening')
                    ->where('((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2))', '>', 1)
                    ->paginate(10, ['*'], 'page', $request->page);
            }
        } else {
            if ($request->status != '' && $request->search != '') {
                $qry = Customer::selectRaw('tblpelanggan.*, ((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2)) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->where('tblpelanggan.nomorrekening', $request->search)
                    ->where('tblpelanggan.status', 1)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)

                    ->orwhere('tblpelanggan.namapelanggan', 'like', '%' . $request->search . '%')
                    ->where('tblpelanggan.status', 1)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                    ->groupBy('tblpembayaran.nomorrekening')
                    ->where('((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2))', $status)
                    ->where('((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2))', '>', 1)
                    ->paginate(10, ['*'], 'page', $request->page);
            } else if ($request->status != '') {
                $qry = Customer::selectRaw('tblpelanggan.*, ((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2)) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->where('tblpelanggan.status', 1)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                    ->where('((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2))', $status)
                    ->where('((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2))', '>', 1)
                    ->groupBy('tblpembayaran.nomorrekening')
                    ->paginate(10, ['*'], 'page', $request->page);
            } else if ($request->search != '') {
                $qry = Customer::selectRaw('tblpelanggan.*, ((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2)) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->where('tblpelanggan.nomorrekening', $request->search)
                    ->where('tblpelanggan.status', 1)
                    ->where('((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2))', '>', 1)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                    ->orwhere('tblpelanggan.namapelanggan', 'like', '%' . $request->search . '%')
                    ->where('tblpelanggan.status', 1)
                    ->where('((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2))', '>', 1)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                    ->groupBy('tblpembayaran.nomorrekening')
                    ->paginate(10, ['*'], 'page', $request->page);
            } else {

                // $count = Customer::selectRaw('tblpelanggan.*, ((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2)) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                //     ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                //     ->where('tblpelanggan.status', 1)
                //     ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                //     ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                //     ->groupBy('tblpembayaran.nomorrekening')->get()->count();
                // if ($count % 10 === 0) {
                //     $total_data = floor($count / 10);
                // } else {
                //     $total_data = floor($count / 10) + 1;
                // }
                $qry = Customer::selectRaw('tblpelanggan.*, ((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2)) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->where('tblpelanggan.status', 1)
                    ->where('((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2))', 1)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                    ->groupBy('tblpembayaran.nomorrekening')
                    // ->where('((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2))','>', 1)
                    ->paginate(10, ['*'], 'page', $request->page);
            }
        }
        try {
            if (!empty($qry)) {
                return response()->json([
                    'message' => 'Sukses',
                    'data' => $qry,
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal',
                'data' => $ex,
            ]);
        }
    }
    public function show($id)
    {
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
        $tindakan = ['tindakan' => ""];
        $inputStatus = ['inputStatus' => ""];
        $total = 0;
        $ctm_lock = 0;
        $last_4_month = date("Y-n-d", strtotime('-4 month', strtotime(date('Y-m-01'))));
        if ($date_now > $date_comp) {
            $ctm_lock_old = 0;
            $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        } else {
            $ctm_lock_old = 1;
            $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        }

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

        if ($tunggakan === 2) {
            $tindakan = ['tindakan' => "notice"];
        } else if ($tunggakan === 3) {
            $cek = LockAction::where('customer_id', $id)->where('type', 'lock')->get();
            if (count($cek) >= 1) {
                $tindakan = ['tindakan' => "notice2"];
            } else {
                $tindakan = ['tindakan' => "lock"];
            }
        } else if ($tunggakan > 3) {
            $tindakan = ['tindakan' => "cabutan"];
        }

        $cekInput = LockAction::where('customer_id', $id)->where('type', $tindakan)->get();
        if (count($cekInput) >= 1) {
            $inputStatus = ["inputStatus" => "sudah"];
        } else {
            $inputStatus = ["inputStatus" => "belum"];
        }


        $recap = [
            'tagihan' => $tagihan,
            'denda' => $denda,
            'total' => $total,
            'tunggakan' => $tunggakan,
        ];

        try {
            if (!empty($customer)) {
                return response()->json([
                    'message' => 'Sukses',
                    'data' => $customer,
                    'data2' => $dataPembayaran,
                    'data3' => $recap,
                    'data4' => $tindakan,
                    'data5' => $inputStatus,
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal',
                'data' => $ex,
            ]);
        }
    }


    public function store(Request $request)
    {

        $last_code = $this->get_last_code('lock_action');

        $code = acc_code_generate($last_code, 8, 3);
        $img_path = "/images/segelMeter";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath  = base_path().'/public';
        $dataForm = json_decode($request->form);
        $responseImage = '';

        $dataQtyImage = json_decode($request->qtyImage);
        for ($i = 1; $i <= $dataQtyImage; $i++) {
            if ($request->file('image' . $i)) {
                $resourceImage = $request->file('image' . $i);
                $nameImage = strtolower($code);
                $file_extImage = $request->file('image' . $i)->extension();
                $nameImage = str_replace(" ", "-", $nameImage);

                $img_name = $img_path . "/" . $nameImage . "-" . $dataForm->customer_id . $i . "." . $file_extImage;
                $folder_upload = 'images/segelMeter';
                $resourceImage->move($folder_upload, $img_name);

                $dataImageName[] = $img_name;
            } else {
                $responseImage = 'Image tidak di dukung';
                break;
            }
        }

        if ($responseImage != '') {
            return response()->json([
                'message' => $responseImage,
            ]);
        }
        // image
        // $resourceImage = $request->file('image');
        // $nameImage = strtolower($code);
        // $file_extImage = $request->file('image')->extension();
        // $nameImage = str_replace(" ", "-", $nameImage);

        // $img_name = $img_path . "/" . $nameImage . "-" . $dataForm->customer_id . "." . $file_extImage;

        // $resourceImage->move($basepath . $img_path, $img_name);

        // video
        $video_name = '';
        if ($request->file('video')) {

            $video_path = "/videos/segelMeter";
            $resource = $request->file('video');
            // $filename = $resource->getClientOriginalName();
            // $file_extVideo = $request->file('video')->extension();
            $video_name = $video_path . "/" . strtolower($code) . '-' . $dataForm->customer_id . '.mp4';

            $resource->move($basepath . $video_path, $video_name);
        }

        // if (!isset($dataForm->title)) {
        //     $dataForm->title = 'Tiket Keluhan';
        // }

        // if (!isset($dataForm->category_id)) {
        //     $category = CategoryApi::orderBy('id', 'ASC')->first();
        //     $dataForm->category_id = $category->id;
        // }

        //set SPK

        // $dateNow = date('Y-m-d H:i:s');
        // $subdapertement_def = Subdapertement::where('def', '1')->first();
        // $dapertement_def_id = $subdapertement_def->dapertement_id;
        // $subdapertement_def_id = $subdapertement_def->id;
        // $arr['dapertement_id'] = $dapertement_def_id;
        // $arr['month'] = date("m");
        // $arr['year'] = date("Y");
        // $last_spk = $this->get_last_code('spk-ticket', $arr);
        // $spk = acc_code_generate($last_spk, 21, 17, 'Y');

        try {

            // $ticket = LockAction::create($data);
            // if ($ticket) {
            $upload_image = new LockAction;
            $upload_image->image = str_replace("\/", "/", json_encode($dataImageName));
            $upload_image->code = $code;
            $upload_image->customer_id = $dataForm->customer_id;
            $upload_image->staff_id = $dataForm->staff_id;
            $upload_image->type = $dataForm->type;
            $upload_image->memo = $dataForm->memo;
            $upload_image->lat = $dataForm->lat;
            $upload_image->lng = $dataForm->lng;

            $upload_image->save();
            // }

            //send notif to admin

            // $admin_arr = User::where('dapertement_id', 0)->get();
            // foreach ($admin_arr as $key => $admin) {
            //     $id_onesignal = $admin->_id_onesignal;
            //     $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->memo;
            //     if (!empty($id_onesignal)) {
            //         OneSignal::sendNotificationToUser(
            //             $message,
            //             $id_onesignal,
            //             $url = null,
            //             $data = null,
            //             $buttons = null,
            //             $schedule = null
            //         );
            //     }
            // }

            //send notif to humas

            // $admin_arr = User::where('subdapertement_id', $subdapertement_def_id)
            //     ->where('staff_id', 0)
            //     ->get();
            // foreach ($admin_arr as $key => $admin) {
            //     $id_onesignal = $admin->_id_onesignal;
            //     $message = 'Humas: Keluhan Baru Diterima : ' . $dataForm->memo;
            //     if (!empty($id_onesignal)) {
            //         OneSignal::sendNotificationToUser(
            //             $message,
            //             $id_onesignal,
            //             $url = null,
            //             $data = null,
            //             $buttons = null,
            //             $schedule = null
            //         );
            //     }
            // }

            return response()->json([
                'message' => 'Segel Meter Terkirim',
                'data' => $upload_image,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }



    public function history(Request $request)
    {

        if ($request->status != '' && $request->search != ''  && $request->date != '') {
            $qry = LockAction::join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')->where('created_at', 'like', $request->date . '%')->where('type', $request->status)
                ->where('tblpelanggan.nomorrekening', $request->search)->orwhere('tblpelanggan.namapelanggan', 'like', '%' . $request->search . '%')
                ->where('created_at', 'like', $request->date . '%')->where('type', $request->status)
                ->paginate(10, ['*'], 'page', $request->page);
        } else if ($request->status && $request->date != '') {
            $qry = LockAction::join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')->where('type', $request->status)
                ->paginate(10, ['*'], 'page', $request->page);
        } else if ($request->status != '' && $request->search != '') {
            $qry = LockAction::join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')->where('type', $request->status)
                ->where('tblpelanggan.nomorrekening', $request->search)->orwhere('tblpelanggan.namapelanggan', 'like', '%' . $request->search . '%')
                ->where('type', $request->status)
                ->paginate(10, ['*'], 'page', $request->page);
        } else if ($request->search != ''  && $request->date != '') {
            $qry = LockAction::join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')->where('created_at', 'like', $request->date . '%')
                ->where('tblpelanggan.nomorrekening', $request->search)->orwhere('tblpelanggan.namapelanggan', 'like', '%' . $request->search . '%')
                ->where('created_at', 'like', $request->date . '%')
                ->paginate(10, ['*'], 'page', $request->page);
        } else if ($request->search != '') {
            $qry = LockAction::join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')->where('tblpelanggan.nomorrekening', $request->search)->orwhere('tblpelanggan.namapelanggan', 'like', '%' . $request->search . '%')
                ->paginate(10, ['*'], 'page', $request->page);
        } else if ($request->date != '') {
            $qry = LockAction::join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')->where('created_at', 'like', $request->date . '%')
                ->paginate(10, ['*'], 'page', $request->page);
        } else if ($request->status != '') {
            $qry = LockAction::join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')->where('type', $request->status)
                ->paginate(10, ['*'], 'page', $request->page);
        } else {
            $qry = LockAction::join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')->paginate(10, ['*'], 'page', $request->page);
        }

        try {
            if (!empty($qry)) {
                return response()->json([
                    'message' => 'Sukses',
                    'data' => $qry,
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal',
                'data' => $ex,
            ]);
        }
    }

    public function historyShow($id)
    {
        $customer = LockAction::join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')->where('lock_action.id', $id)
            ->first();

        try {
            if (!empty($customer)) {
                return response()->json([
                    'message' => 'Sukses',
                    'data' => $customer,
                    'data2' => json_decode($customer->image),
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal',
                'data' => $ex,
            ]);
        }
    }
}
