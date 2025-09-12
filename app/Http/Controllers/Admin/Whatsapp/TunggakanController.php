<?php

namespace App\Http\Controllers\Admin\Whatsapp;

use App\CategoryWa;
use App\CtmWilayah;
use App\Customer;
use App\Http\Controllers\Controller;
use App\LockAction;
use App\pelanggan_tiro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\WablasTrait;
use App\wa_history;
use App\WaTemplate;
use GuzzleHttp\Promise\Create;

class TunggakanController extends Controller
{
    public function index()
    {
        $templates = WaTemplate::where('category_wa_id', 2)->get();
        return view('admin.whatsapp.tunggakan.template', compact('templates'));
    }

    public function create(Request $request)
    {
        $data = [
            'template' => $request->template,
            'message' => $request->message,
            'status' => $request->status,
            'type' => $request->type,
        ];
        return view('admin.whatsapp.tunggakan.createMessage', compact('data'));
    }

    function gantiformat($nomorhp)
    {
        //Terlebih dahulu kita trim dl
        $nomorhp = trim($nomorhp);
        //bersihkan dari karakter yang tidak perlu
        $nomorhp = strip_tags($nomorhp);
        // Berishkan dari spasi
        $nomorhp = str_replace(" ", "", $nomorhp);
        // bersihkan dari bentuk seperti  (022) 66677788
        $nomorhp = str_replace("(", "", $nomorhp);
        // bersihkan dari format yang ada titik seperti 0811.222.333.4
        $nomorhp = str_replace(".", "", $nomorhp);

        //cek apakah mengandung karakter + dan 0-9
        if (!preg_match('/[^+0-9]/', trim($nomorhp))) {
            // cek apakah no hp karakter 1-3 adalah +62
            if (substr(trim($nomorhp), 0, 3) == '+62') {
                $nomorhp = trim($nomorhp);
            }
            // cek apakah no hp karakter 1 adalah 0
            elseif (substr($nomorhp, 0, 1) == '0') {
                $nomorhp = '62' . substr($nomorhp, 1);
            }
        }
        return $nomorhp;
    }

    public function store(Request $request)
    {
        $date_now = date('Y-m-d');
        $date_comp = date('Y-m') . '-20';
        $last_4_month = date("Y-n-d", strtotime('-4 month', strtotime(date('Y-m-01'))));

        $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
            ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
            ->where('tblpelanggan.status', 1);
        if ($date_now > $date_comp) {
            $qry->having('jumlahtunggakan', '>', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->groupBy('tblpembayaran.nomorrekening')
                ->FilterStatus(request()->input('status'));
        } else {
            $qry->having('jumlahtunggakan', '>', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)

                ->groupBy('tblpembayaran.nomorrekening')
                ->FilterStatus(request()->input('status'));
        }
        $data2 = [];
        $data = $qry->get();
        if ($request->status == '3') {
            foreach ($data as $value) {
                $nomorhp = $value->telp;
                $nomorhp = trim($nomorhp);
                //bersihkan dari karakter yang tidak perlu
                $nomorhp = strip_tags($nomorhp);
                // Berishkan dari spasi
                $nomorhp = str_replace(" ", "", $nomorhp);
                // bersihkan dari bentuk seperti  (022) 66677788
                $nomorhp = str_replace("(", "", $nomorhp);
                // bersihkan dari format yang ada titik seperti 0811.222.333.4
                $nomorhp = str_replace(".", "", $nomorhp);
                $test = LockAction::where('customer_id', $value->nomorrekening)->where('type', 'lock')->first();
                if ($request->type == 'lock') {
                    if (!$test) {
                        if ($nomorhp === "") {
                            $data1 = [
                                'phone' => $this->gantiformat($nomorhp),
                                'name' => $value->namapelanggan,
                                'alamat' => $value->alamat,
                                'status' => "gagal",
                            ];
                            wa_history::create($data1);
                        } else if (!preg_match("/^[0-9]*$/", $nomorhp)) {
                            $data2 = [
                                'phone' => $this->gantiformat($nomorhp),
                                'name' => $value->namapelanggan,
                                'alamat' => $value->alamat,
                                'status' => "gagal",
                            ];
                            wa_history::create($data2);
                        } else {
                            $data2 = [
                                'phone' => $this->gantiformat($nomorhp),
                                'name' => $value->namapelanggan,
                                'alamat' => $value->alamat
                            ];
                            wa_history::create($data2);
                        }
                    }
                } else {
                    if ($test) {
                        if ($nomorhp === "") {
                            $data1 = [
                                'phone' => $this->gantiformat($nomorhp),
                                'name' => $value->namapelanggan,
                                'alamat' => $value->alamat,
                                'status' => "gagal",
                            ];
                            wa_history::create($data1);
                        } else if (!preg_match("/^[0-9]*$/", $nomorhp)) {
                            $data2 = [
                                'phone' => $this->gantiformat($nomorhp),
                                'name' => $value->namapelanggan,
                                'alamat' => $value->alamat,
                                'status' => "gagal",
                            ];
                            wa_history::create($data2);
                        } else {
                            $data2 = [
                                'phone' => $this->gantiformat($nomorhp),
                                'name' => $value->namapelanggan,
                                'alamat' => $value->alamat
                            ];
                            wa_history::create($data2);
                        }
                    }
                }
            }
        } else {
            foreach ($data as $value) {
                $nomorhp = $value->telp;
                $nomorhp = trim($nomorhp);
                //bersihkan dari karakter yang tidak perlu
                $nomorhp = strip_tags($nomorhp);
                // Berishkan dari spasi
                $nomorhp = str_replace(" ", "", $nomorhp);
                // bersihkan dari bentuk seperti  (022) 66677788
                $nomorhp = str_replace("(", "", $nomorhp);
                // bersihkan dari format yang ada titik seperti 0811.222.333.4
                $nomorhp = str_replace(".", "", $nomorhp);
                if ($nomorhp === "") {
                    $data1 = [
                        'phone' => $this->gantiformat($nomorhp),
                        'name' => $value->namapelanggan,
                        'alamat' => $value->alamat,
                        'status' => "gagal",
                    ];
                    wa_history::create($data1);
                } else if (!preg_match("/^[0-9]*$/", $nomorhp)) {
                    $data1 = [
                        'phone' => $this->gantiformat($nomorhp),
                        'name' => $value->namapelanggan,
                        'alamat' => $value->alamat,
                        'status' => "gagal",
                    ];
                    wa_history::create($data1);
                } else {
                    $data1 = [
                        'phone' => $this->gantiformat($nomorhp),
                        'name' => $value->namapelanggan,
                        'alamat' => $value->alamat
                    ];
                    wa_history::create($data1);
                }
            }
        }
        dd($data2);
    }
}
