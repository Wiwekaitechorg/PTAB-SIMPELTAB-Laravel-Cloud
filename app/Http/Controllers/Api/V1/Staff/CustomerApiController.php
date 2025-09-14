<?php

namespace App\Http\Controllers\Api\V1\Staff;

use App\AreaStaff;
use App\CtmPelanggan;
use App\CtmWilayah;
use App\CustomerMaps;
use App\Customer;
use App\Http\Controllers\Controller;
use App\User;
use DB;
use Illuminate\Http\Request;

class CustomerApiController extends Controller
{
    public function getCtmarealgroup(Request $request, $id)
    {
        $date_now = date('Y-m-d');
        $date_comp = date('Y-m') . '-20';
        $last_4_month = date("Y-n-d", strtotime('-4 month', strtotime(date('Y-m-01'))));
        $total_data = 0;
        //set query
        $user = User::where('id', $id)->first();

        if ($user->staff_id != 0 || $user->staff_id != null) {
            $staff = Staff::where('id', $user->staff_id)->first();
            $staffPbk = CtmPbk::where('Name', $staff->pbk)->get();
            if (count($staffPbk) > 0) {
                if ($date_now > $date_comp) {
                    $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                        ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                        ->join('map_koordinatpelanggan', 'tblpelanggan.nomorrekening', '=', 'map_koordinatpelanggan.nomorrekening')
                        ->join('tblopp', 'tblopp.nomorrekening', '=', 'tblpelanggan.nomorrekening')
                        ->where('tblopp.operator',  $staff->pbk)
                        ->where('tblpelanggan.nomorrekening', $request->search)
                        ->where('tblpelanggan.status', 1)
                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                        ->having('jumlahtunggakan', '>', 1)
                        ->FilterStatus($request->status)
                        ->groupBy('tblpembayaran.nomorrekening')->paginate(10, ['*'], 'page', $request->page);
                } else {
                    $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                        ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                        ->join('map_koordinatpelanggan', 'tblpelanggan.nomorrekening', '=', 'map_koordinatpelanggan.nomorrekening')
                        ->join('tblopp', 'tblopp.nomorrekening', '=', 'tblpelanggan.nomorrekening')
                        ->where('tblopp.operator',  $staff->pbk)
                        ->where('tblpelanggan.nomorrekening', $request->search)
                        ->where('tblpelanggan.status', 1)
                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                        ->having('jumlahtunggakan', '>', 1)
                        ->FilterStatus($request->status)
                        ->groupBy('tblpembayaran.nomorrekening')->paginate(10, ['*'], 'page', $request->page);
                }
            } else {
                $qry = Customer::selectRaw('tblpelanggan.*, lat, lng, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->join('map_koordinatpelanggan', 'tblpelanggan.nomorrekening', '=', 'map_koordinatpelanggan.nomorrekening');
                if ($date_now > $date_comp) {
                    if ($request->search != '') {
                        $tes = "1";
                        $data = AreaStaff::select('area_id')->where('staff_id', $user->staff_id)->get();
                        if (count($data) > 0) {

                            for ($i = 0; $i < count($data); $i++) {
                                if ($i < 1) {

                                    $qry->where('tblpelanggan.idareal', $data[$i]->area_id)
                                        ->where('tblpelanggan.status', 1)
                                        ->where('tblpelanggan.nomorrekening', $request->search)
                                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                                        ->having('jumlahtunggakan', '>', 1);
                                    // $data2 = $data2 . ' where idareal = ' . $data[$i]->area_id;
                                } else {
                                    $qry->orWhere('tblpelanggan.idareal', $data[$i]->area_id)
                                        ->where('tblpelanggan.status', 1)
                                        ->where('tblpelanggan.nomorrekening', $request->search)
                                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                                        ->having('jumlahtunggakan', '>', 1);
                                }
                            }
                        } else {
                            $qry->where('tblpelanggan.nomorrekening', null);
                        }

                        $qry = $qry->groupBy('tblpembayaran.nomorrekening')->paginate(10, ['*'], 'page', $request->page);
                    } else {
                        $tes = "2";
                        $data = AreaStaff::select('area_id')->where('staff_id', $user->staff_id)->get();
                        if (count($data) > 0) {

                            for ($i = 0; $i < count($data); $i++) {
                                if ($i < 1) {

                                    $qry->where('tblpelanggan.idareal', $data[$i]->area_id)
                                        ->where('tblpelanggan.status', 1)
                                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                                        ->having('jumlahtunggakan', '>', 1);
                                    // $data2 = $data2 . ' where idareal = ' . $data[$i]->area_id;
                                } else {
                                    $qry->orWhere('tblpelanggan.idareal', $data[$i]->area_id)
                                        ->where('tblpelanggan.status', 1)
                                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                                        ->having('jumlahtunggakan', '>', 1);
                                }
                            }
                        } else {
                            $qry->where('tblpelanggan.nomorrekening', null);
                        }

                        $qry = $qry->groupBy('tblpembayaran.nomorrekening')->paginate(10, ['*'], 'page', $request->page);
                        // $qry->having('jumlahtunggakan', '>', 1)
                        //     ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                        //     ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                        //     ->groupBy('tblpembayaran.nomorrekening')
                        //     ->FilterStatus($request->status);
                    }
                }
                // tanggal beda
                else {
                    if ($request->search != '') {
                        $tes = "3";
                        $data = AreaStaff::select('area_id')->where('staff_id', $user->staff_id)->get();
                        if (count($data) > 0) {

                            for ($i = 0; $i < count($data); $i++) {

                                if ($i < 1) {
                                    $qry->where('tblpelanggan.idareal', $data[$i]->area_id)
                                        ->where('tblpelanggan.nomorrekening', $request->search)
                                        ->where('tblpelanggan.status', 1)
                                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                                        ->having('jumlahtunggakan', '>', 1);
                                    // $data2 = $data2 . ' where idareal = ' . $data[$i]->area_id;
                                } else {
                                    $qry->orWhere('tblpelanggan.idareal', $data[$i]->area_id)
                                        ->where('tblpelanggan.nomorrekening', $request->search)
                                        ->where('tblpelanggan.status', 1)
                                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                                        ->having('jumlahtunggakan', '>', 1);
                                }
                            }
                            $qry = $qry->groupBy('tblpembayaran.nomorrekening')
                                ->paginate(10, ['*'], 'page', $request->page);
                        }
                    } else {
                        $tes = 'k';
                        $data = AreaStaff::select('area_id')->where('staff_id', $user->staff_id)->get();
                        if (count($data) > 0) {
                            $tes = 'T';
                            for ($i = 0; $i < count($data); $i++) {
                                if ($i < 1) {

                                    $qry->where('tblpelanggan.idareal', $data[$i]->area_id)
                                        ->where('tblpelanggan.status', 1)
                                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                                        ->having('jumlahtunggakan', '>', 1);
                                    // $data2 = $data2 . ' where idareal = ' . $data[$i]->area_id;
                                } else {
                                    $qry->orWhere('tblpelanggan.idareal', $data[$i]->area_id)
                                        ->where('tblpelanggan.status', 1)
                                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                                        ->having('jumlahtunggakan', '>', 1);
                                }
                            }
                        } else {
                            $tes = 'B';
                            $qry->where('tblpelanggan.nomorrekening', null);
                        }

                        $qry = $qry->groupBy('tblpembayaran.nomorrekening')
                            ->paginate(10, ['*'], 'page', $request->page);
                    }
                    // $tes = '0';
                }
            }
        } else {
            //tessssLama
            $tes = "4";
            if ($date_now > $date_comp) {

                if ($request->search != '') {
                    $qry = Customer::selectRaw('tblpelanggan.*, ((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2)) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                        ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                        ->where('tblpelanggan.nomorrekening', $request->search)
                        ->where('tblpelanggan.status', 1)
                        ->having('jumlahtunggakan', '>', '1')
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
                        ->having('jumlahtunggakan', '>', '1')
                        ->paginate(10, ['*'], 'page', $request->page);
                }
            } else {
                if ($request->search != '') {
                    $qry = Customer::selectRaw('tblpelanggan.*, ((((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2)) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                        ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                        ->where('tblpelanggan.nomorrekening', $request->search)
                        ->where('tblpelanggan.status', 1)
                        ->having('jumlahtunggakan', '>', '1')
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
                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                        ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                        ->groupBy('tblpembayaran.nomorrekening')
                        ->having('jumlahtunggakan', '>', '1')
                        ->paginate(10, ['*'], 'page', $request->page);
                }
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

    public function ctmCustomer($id)
    {
        try {
            $ctm = CtmPelanggan::where('nomorrekening', $id)
                ->first();
            $ctm->year = date('Y');
            return response()->json([
                'message' => 'Data CTM',
                'data' => $ctm,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal Mengambil data',
                'err' => $ex,
            ]);
        }
    }

    public function ctmPay($id)
    {
        try {
            $date_now = date("Y-m-d");
            $date_comp = date("Y-m") . "-20";
            $month_now = date('n');
            $month_next = date('n', strtotime('+1 month')) - 1;
            if ($month_now > $month_next) {
                $month_next = $month_next + 12;
            }

            $data = array(
                'nomorrekening' => $id,
            );

            $url = 'https://yndvck.perumdatab.com/akademi-pelawak-tpi/tgh.api.php';

            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, count($data));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //execute post
            $ctm = curl_exec($ch);
            $ctm = json_decode($ctm);

            //close connection
            curl_close($ch);

            if ($date_now > $date_comp) {
                $ctm_lock = 0;
            } else {
                $ctm_lock = 1;
            }

            $ctm_num_row = count($ctm) - 1;
            foreach ($ctm as $key => $item) {
                //get sudah dibayar
                $item->sudahdibayar = 0;
                if ($item->statuslunas == 2) {
                    $item->sudahdibayar = $item->wajibdibayar;
                }
                $sisa = $item->wajibdibayar - $item->sudahdibayar;
                //if not paid
                if ($sisa > 0) {
                    $ctm[$key]->tglbayarterakhir = "";
                }
                //denda & $item->sudahdibayar=$item->wajibdibayar;
                $ctm[$key]->denda = 0;
                $ctm[$key]->sudahdibayar = $item->sudahdibayar;
                //set to prev
                $ctm[$key]->tahunrekening = date('Y', strtotime(date($item->tahunrekening . '-' . $item->bulanrekening . '-01') . " -1 month"));
                $ctm[$key]->bulanrekening = date('m', strtotime(date($item->tahunrekening . '-' . $item->bulanrekening . '-01') . " -1 month"));
                //if status 0
                if ($ctm[$key]->status == 0 && $key == $ctm_num_row) {
                    unset($ctm[$key]);
                }
            }
            return response()->json([
                'message' => 'Data CTM',
                'data' => $ctm,
                'month_next' => $month_next,
                'ctm_lock' => $ctm_lock,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal Mengambil data',
                'err' => $ex,
            ]);
        }
    }

    public function scanBarcode(Request $request)
    {
        $request->validate([
            'code' => 'required',
        ]);

        $code = $request->code;

        try {
            $customerMaps = CustomerMaps::where('qrcode', $code)->first();
            if (empty($customerMaps)) {
                return response()->json([
                    'status' => '0',
                    'message' => 'Data Kosong',
                ]);
            } else {
                $customer = CustomerApi::WhereMaps('code', $customerMaps->nomorrekening)->first();

                if (isset($customer)) {
                    $pass_set = 0;
                    if ($customer->password != '') {
                        $pass_set = 1;
                    }
                    return response()->json([
                        'status' => '1',
                        'message' => 'Anda terdaftar sebagai pelanggan',
                        'data' => $customer,
                        'pass_set' => $pass_set,
                    ]);
                } else {
                    return response()->json([
                        'status' => '0',
                        'message' => 'Data Kosong',
                    ]);
                }
            }
        } catch (QueryException $ex) {
            return response()->json([
                'status' => '0',
                'message' => $ex,
            ]);
        }
    }
}
