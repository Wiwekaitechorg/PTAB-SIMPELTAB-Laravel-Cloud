<?php

namespace App\Http\Controllers\Api\V1\Staff;

use App\AreaStaff;
use App\CtmPbk;
use App\CtmPembayaran;
use App\Customer;
use App\Http\Controllers\Controller;
use App\LockAction;
use App\Staff;
use App\Traits\TraitModel;
use App\User;
use DB;
use Illuminate\Http\Request;

class SealApiController extends Controller
{
    use TraitModel;

    public function index(Request $request, $id)
    {
        $date_now = date('Y-m-d');
        $date_comp = date('Y-m') . '-20';
        $last_4_month = date("Y-n-d", strtotime('-4 month', strtotime(date('Y-m-01'))));
        $total_data = 0;
        //set query
        $user = User::where('id', $id)->first();

        $staffPbk = array();

        if ($user->staff_id != 0 || $user->staff_id != null) {
            $staff = Staff::where('id', $user->staff_id)->first();
            $staffPbk = CtmPbk::where('Name', $staff->pbk)->get();
            if (count($staffPbk) > 0) {
                if ($date_now < $date_comp) {
                    $tes = "1";
                    $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                        ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                        ->join('tblopp', 'tblopp.nomorrekening', '=', 'tblpelanggan.nomorrekening')
                        ->where('tblopp.operator', $staff->pbk)
                        ->FilterCustomerId($request->customer)
                        ->where('tblpelanggan.status', 1)
                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                        ->having('jumlahtunggakan', '>', 1)
                        ->FilterKeyword($request->search)
                        ->FilterStatusNew($request->status)
                        ->groupBy('tblpembayaran.nomorrekening')->paginate(10, ['*'], 'page', $request->page);
                } else {
                    $tes = "2";
                    $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                        ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                        ->join('tblopp', 'tblopp.nomorrekening', '=', 'tblpelanggan.nomorrekening')
                        ->where('tblopp.operator', $staff->pbk)
                        ->where('tblpelanggan.status', 1)
                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                        ->having('jumlahtunggakan', '>', 1)
                        ->FilterKeyword($request->search)
                        ->FilterStatusNew($request->status)
                        ->groupBy('tblpembayaran.nomorrekening')->paginate(10, ['*'], 'page', $request->page);
                }
            } else {
                $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening');
                if ($date_now > $date_comp) {
                    $tes = "3";
                    $data = AreaStaff::select('area_id')->where('staff_id', $user->staff_id)->get();
                    if (count($data) > 0) {

                        $qry->where(function ($query) use ($data) {
                            //$query->where('tblpelanggan.idareal', $data[0]->area_id);
                            for ($i = 0; $i < count($data); $i++) {
                                if ($i == 0) {
                                    $query->where('tblpelanggan.idareal', $data[$i]->area_id);
                                } else {
                                    $query->orWhere('tblpelanggan.idareal', $data[$i]->area_id);
                                }
                            }
                        });

                        $qry->where('tblpelanggan.status', 1)
                            ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                            ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                            ->having('jumlahtunggakan', '>', 1)
                            ->FilterStatusNew($request->status)
                            ->FilterKeyword($request->search);
                    } else {
                        $qry->where('tblpelanggan.nomorrekening', null);
                    }

                    $qry = $qry->groupBy('tblpembayaran.nomorrekening')->paginate(10, ['*'], 'page', $request->page);
                }
                // tanggal beda
                else {
                    $tes = "4";
                    $data = AreaStaff::select('area_id')->where('staff_id', $user->staff_id)->get();
                    $data_area = $data;
                    if (count($data) > 0) {

                        $qry->where(function ($query) use ($data) {
                            // $query->where('tblpelanggan.idareal', 'K010107')->orWhere('tblpelanggan.idareal', 'K010108')->orWhere('tblpelanggan.idareal', 'K010109');
                            for ($i = 0; $i < count($data); $i++) {
                                if ($i == 0) {
                                    $query->where('tblpelanggan.idareal', $data[$i]->area_id);
                                } else {
                                    $query->orWhere('tblpelanggan.idareal', $data[$i]->area_id);
                                }
                            }
                        });

                        $qry->where('tblpelanggan.status', 1)
                            ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                            ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                            ->having('jumlahtunggakan', '>', 1)
                            ->FilterStatusNew($request->status)
                            ->FilterKeyword($request->search);

                        $qry = $qry->groupBy('tblpembayaran.nomorrekening')
                            ->paginate(10, ['*'], 'page', $request->page);
                    }
                }
            }
        } else {
            if ($date_now > $date_comp) {
                $tes = "5";
                $qry = Customer::selectRaw('tblpelanggan.*, ((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2)) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->where('tblpelanggan.status', 1)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                    ->having('jumlahtunggakan', '>', 1)
                    ->FilterStatusNew($request->status)
                    ->FilterKeyword($request->search)
                    // ->having('jumlahtunggakan', '>', '1')
                    ->groupBy('tblpembayaran.nomorrekening')
                    ->paginate(10, ['*'], 'page', $request->page);
            } else {
                $tes = "6";
                $qry = Customer::selectRaw('tblpelanggan.*, ((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2)) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->where('tblpelanggan.status', 1)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                    ->having('jumlahtunggakan', '>', 1)
                    ->FilterStatusNew($request->status)
                    ->FilterKeyword($request->search)
                    // ->having('jumlahtunggakan', '>', '1')
                    ->groupBy('tblpembayaran.nomorrekening')
                    ->paginate(10, ['*'], 'page', $request->page);
            }
        }

        try {
            if (!empty($qry)) {
                return response()->json([
                    'message' => 'Sukses',
                    'data' => $qry,
                    // 'data2' => $data,
                    'data3' => $staffPbk,
                    'user' => $user,
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
        $dataPembayaran = [];
        $customer = Customer::selectRaw('tblpelanggan.*, map_koordinatpelanggan.lat, map_koordinatpelanggan.lng')->leftJoin('map_koordinatpelanggan', 'map_koordinatpelanggan.nomorrekening', '=', 'tblpelanggan.nomorrekening')->where('tblpelanggan.nomorrekening', $id)
            ->first();
        $customer->year = date('Y');
        // dd($ctm);

        // ctm pay
        $date_now = date("Y-m-d");
        $date_comp = date("Y-m") . "-20";
        $month_next = date('n', strtotime('+1 month'));
        $month_now = ($month_next - 1);
        $month_now_new = date('n');
        $year_now = date("Y");
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

        $status_paid_this_month = 0;
        foreach ($ctm as $key => $item) {
            //get this month paid
            if ($item->bulanrekening == $month_now_new && $item->tahunrekening == $year_now) {
                if ($item->statuslunas == 2) {
                    $status_paid_this_month = 1;
                }
            }
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
                'norekening' => $item->nomorrekening ? $item->nomorrekening : '',
                'periode' => $periode ? $periode : '',
                'tanggal' => $item->tglbayarterakhir ? $item->tglbayarterakhir : '',
                'm3' => $m3 ? $m3 : '',
                'wajibdibayar' => $item->wajibdibayar ? $item->wajibdibayar : '',
                'sudahbayar' => $item->sudahdibayar ? $item->sudahdibayar : '',
                'denda' => $item->denda ? $item->denda : '',
                'sisa' => $sisa ? $sisa : '',
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

        //list actions
        $actions = array();
        $input_Status = 0;

        //get last action
        if (date('d') > 20) {
            //$cek_last_action = LockAction::where('customer_id', $id)->whereBetween('created_at', [date('Y-m-21', strtotime('0 month', strtotime(date('Y-m-d')))), date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))])->orderBy('created_at', 'ASC')->first();
            $cek_last_action = LockAction::where('customer_id', $id)->whereRaw("DATE(created_at) >= '".date('Y-m-21', strtotime('0 month', strtotime(date('Y-m-d'))))."' AND DATE(created_at) <= '".date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))."'")->orderBy('created_at', 'ASC')->first();
        } else {
            //$cek_last_action = LockAction::where('customer_id', $id)->whereBetween('created_at', [date('Y-m-21', strtotime('-1 month', strtotime(date('Y-m-d')))), date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-d'))))])->orderBy('created_at', 'ASC')->first();
            $cek_last_action = LockAction::where('customer_id', $id)->whereRaw("DATE(created_at) >= '".date('Y-m-21', strtotime('-1 month', strtotime(date('Y-m-d'))))."' AND DATE(created_at) <= '".date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-d'))))."'")->orderBy('created_at', 'ASC')->first();
        }

        if ($tunggakan === 2) {
            $tindakan = ['tindakan' => "notice"];
            if ($cek_last_action) {
                if ($cek_last_action->type == 'lock_resist') {
                    $actions[] = [
                        'id' => 'resist',
                        'name' => 'Segel',
                    ];
                } else {
                    $input_Status = 1;
                }
            } else {
                $actions[] = [
                    'id' => 'lock_resist',
                    'name' => 'Hambatan Segel',
                ];
                $actions[] = [
                    'id' => 'resist',
                    'name' => 'Segel',
                ];
            }
        } else if ($tunggakan === 3) {
            $cek = LockAction::where('customer_id', $id)->where('type', 'lock')->get();
            if (count($cek) >= 1) {
                $tindakan = ['tindakan' => "notice2"];
            } else {
                $tindakan = ['tindakan' => "lock"];
            }
            if ($cek_last_action) {
                if ($cek_last_action->type == 'lock_resist') {
                    $actions[] = [
                        'id' => 'lock',
                        'name' => 'Segel',
                    ];
                } else if ($cek_last_action->type == 'lock') {
                    $input_Status = 1;
                } else {
                    $actions[] = [
                        'id' => 'lock_resist',
                        'name' => 'Hambatan Segel',
                    ];
                    $actions[] = [
                        'id' => 'lock',
                        'name' => 'Segel',
                    ];
                }
            } else {
                $actions[] = [
                    'id' => 'lock_resist',
                    'name' => 'Hambatan Segel',
                ];
                $actions[] = [
                    'id' => 'lock',
                    'name' => 'Segel',
                ];
            }
        } else if ($tunggakan > 3) {
            $tindakan = ['tindakan' => "unplug"];
            if ($cek_last_action) {
                if ($cek_last_action->type == 'unplug_resist') {
                    $actions[] = [
                        'id' => 'unplug',
                        'name' => 'Cabut',
                    ];
                } else if ($cek_last_action->type == 'unplug') {
                    $input_Status = 1;
                } else {
                    $actions[] = [
                        'id' => 'unplug_resist',
                        'name' => 'Hambatan Cabut',
                    ];
                    $actions[] = [
                        'id' => 'unplug',
                        'name' => 'Cabut',
                    ];
                }
            } else {
                $actions[] = [
                    'id' => 'unplug_resist',
                    'name' => 'Hambatan Cabut',
                ];
                $actions[] = [
                    'id' => 'unplug',
                    'name' => 'Cabut',
                ];
            }
        }

        //$cekInput = LockAction::where('customer_id', $id)->where('type', $tindakan)->get();       
        // if (count($cekInput) >= 1) {
        //     $inputStatus = ["inputStatus" => "sudah"];
        // } else {
        //     $inputStatus = ["inputStatus" => "belum"];
        // }
        if ($input_Status == 1) {
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
                    'data6' => $actions,
                    'status_paid_this_month' => $status_paid_this_month,
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

        $dataImageName = array();
        if(isset($request->qtyImage)){
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
        }}

        $dataImageToolName = array();
        if(isset($request->qtyImageTool)){
        $dataQtyImageTool = json_decode($request->qtyImageTool);
        for ($i = 1; $i <= $dataQtyImageTool; $i++) {
            if ($request->file('imageTool' . $i)) {
                $resourceImageTool = $request->file('imageTool' . $i);
                $nameImageTool = strtolower($code)."_Tool";
                $file_extImageTool = $request->file('imageTool' . $i)->extension();
                $nameImageTool = str_replace(" ", "-", $nameImageTool);

                $img_name = $img_path . "/" . $nameImageTool . "-" . $dataForm->customer_id . $i . "." . $file_extImageTool;
                $folder_upload = 'images/segelMeter';
                $resourceImageTool->move($folder_upload, $img_name);

                $dataImageToolName[] = $img_name;
            } else {
                $responseImage = 'Image tidak di dukung';
                break;
            }
        }}

        $dataImagePreworkName = array();
        if(isset($request->qtyImagePrework)){
        $dataQtyImagePrework = json_decode($request->qtyImagePrework);
        for ($i = 1; $i <= $dataQtyImagePrework; $i++) {
            if ($request->file('imagePrework' . $i)) {
                $resourceImagePrework = $request->file('imagePrework' . $i);
                $nameImagePrework = strtolower($code)."_Prework";
                $file_extImagePrework = $request->file('imagePrework' . $i)->extension();
                $nameImagePrework = str_replace(" ", "-", $nameImagePrework);

                $img_name = $img_path . "/" . $nameImagePrework . "-" . $dataForm->customer_id . $i . "." . $file_extImagePrework;
                $folder_upload = 'images/segelMeter';
                $resourceImagePrework->move($folder_upload, $img_name);

                $dataImagePreworkName[] = $img_name;
            } else {
                $responseImage = 'Image tidak di dukung';
                break;
            }
        }}

        $dataImageWorkName = array();
        if(isset($request->qtyImageWork)){
        $dataQtyImageWork = json_decode($request->qtyImageWork);
        for ($i = 1; $i <= $dataQtyImageWork; $i++) {
            if ($request->file('imageWork' . $i)) {
                $resourceImageWork = $request->file('imageWork' . $i);
                $nameImageWork = strtolower($code)."_Work";
                $file_extImageWork = $request->file('imageWork' . $i)->extension();
                $nameImageWork = str_replace(" ", "-", $nameImageWork);

                $img_name = $img_path . "/" . $nameImageWork . "-" . $dataForm->customer_id . $i . "." . $file_extImageWork;
                $folder_upload = 'images/segelMeter';
                $resourceImageWork->move($folder_upload, $img_name);

                $dataImageWorkName[] = $img_name;
            } else {
                $responseImage = 'Image tidak di dukung';
                break;
            }
        }}

        $dataImageDoneName = array();
        if(isset($request->qtyImageDone)){
        $dataQtyImageDone = json_decode($request->qtyImageDone);
        for ($i = 1; $i <= $dataQtyImageDone; $i++) {
            if ($request->file('imageDone' . $i)) {
                $resourceImageDone = $request->file('imageDone' . $i);
                $nameImageDone = strtolower($code)."_Done";
                $file_extImageDone = $request->file('imageDone' . $i)->extension();
                $nameImageDone = str_replace(" ", "-", $nameImageDone);

                $img_name = $img_path . "/" . $nameImageDone . "-" . $dataForm->customer_id . $i . "." . $file_extImageDone;
                $folder_upload = 'images/segelMeter';
                $resourceImageDone->move($folder_upload, $img_name);

                $dataImageDoneName[] = $img_name;
            } else {
                $responseImage = 'Image tidak di dukung';
                break;
            }
        }}

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
            $upload_image->image_tools = $dataImageToolName? str_replace("\/", "/", json_encode($dataImageToolName)):"";
            $upload_image->image_prework = $dataImagePreworkName? str_replace("\/", "/", json_encode($dataImagePreworkName)):"";
            $upload_image->image_work = $dataImageWorkName? str_replace("\/", "/", json_encode($dataImageWorkName)):"";
            $upload_image->image_done = $dataImageDoneName? str_replace("\/", "/", json_encode($dataImageDoneName)):"";
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

    public function history(Request $request, $id)
    {

        if ($request->status != '' && $request->search != '' && $request->date != '') {
            $qry = LockAction::join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')->where('created_at', 'like', $request->date . '%')->where('type', $request->status)
                ->where('tblpelanggan.nomorrekening', $request->search)->orwhere('tblpelanggan.namapelanggan', 'like', '%' . $request->search . '%')
                ->where('created_at', 'like', $request->date . '%')->where('type', $request->status)
                ->where('staff_id', $id)->paginate(10, ['*'], 'page', $request->page);
        } else if ($request->status && $request->date != '') {
            $qry = LockAction::join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')->where('type', $request->status)
                ->where('staff_id', $id)->paginate(10, ['*'], 'page', $request->page);
        } else if ($request->status != '' && $request->search != '') {
            $qry = LockAction::join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')->where('type', $request->status)
                ->where('tblpelanggan.nomorrekening', $request->search)->orwhere('tblpelanggan.namapelanggan', 'like', '%' . $request->search . '%')
                ->where('type', $request->status)
                ->where('staff_id', $id)->paginate(10, ['*'], 'page', $request->page);
        } else if ($request->search != '' && $request->date != '') {
            $qry = LockAction::join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')->where('created_at', 'like', $request->date . '%')
                ->where('tblpelanggan.nomorrekening', $request->search)->orwhere('tblpelanggan.namapelanggan', 'like', '%' . $request->search . '%')
                ->where('created_at', 'like', $request->date . '%')
                ->where('staff_id', $id)->paginate(10, ['*'], 'page', $request->page);
        } else if ($request->search != '') {
            $qry = LockAction::join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')->where('tblpelanggan.nomorrekening', $request->search)->orwhere('tblpelanggan.namapelanggan', 'like', '%' . $request->search . '%')
                ->where('staff_id', $id)->paginate(10, ['*'], 'page', $request->page);
        } else if ($request->date != '') {
            $qry = LockAction::join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')->where('created_at', 'like', $request->date . '%')
                ->where('staff_id', $id)->paginate(10, ['*'], 'page', $request->page);
        } else if ($request->status != '') {
            $qry = LockAction::join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')->where('type', $request->status)
                ->where('staff_id', $id)->paginate(10, ['*'], 'page', $request->page);
        } else {
            $qry = LockAction::join('simpletabctm.tblpelanggan', 'lock_action.customer_id', '=', 'tblpelanggan.nomorrekening')->where('staff_id', $id)->paginate(10, ['*'], 'page', $request->page);
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

    public function destroy($id)
    {
        $LockAction = LockAction::where('id', $id)->first();
        $i = 0;
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin", \base_path());
        $basepath  = base_path().'/public';
        foreach (json_decode($LockAction->image) as $n) {
            if (file_exists($n)) {

                unlink($basepath . $n);
            }
        }
        return LockAction::where('id', $id)->delete();
    }
}
