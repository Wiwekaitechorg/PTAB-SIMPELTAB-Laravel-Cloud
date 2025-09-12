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
use Illuminate\Support\Facades\App;
use Barryvdh\DomPDF\Facade\Pdf;

class LockTController extends Controller
{
    use TraitModel;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $id = '11094';
        $customer = Customer::join('map_koordinatpelanggan', 'map_koordinatpelanggan.nomorrekening', '=', 'tblpelanggan.nomorrekening')->where('tblpelanggan.nomorrekening', $id)
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
        // foreach ($dataPembayaran as $value) {
        //     dd($value['speriode']);
        // }

        $recap = [
            'tagihan' => $tagihan,
            'denda' => $denda,
            'total' => $total,
            'tunggakan' => $tunggakan,
        ];

        // dd($customer);
        $day = date('D');
        $dayList = array(
            'Sun' => 'Minggu',
            'Mon' => 'Senin',
            'Tue' => 'Selasa',
            'Wed' => 'Rabu',
            'Thu' => 'Kamis',
            'Fri' => 'Jumat',
            'Sat' => 'Sabtu'
        );
        $day2Name = array(
            '01' => 'Satu',
            '02' => 'Dua',
            '03' => 'Tiga',
            '04' => 'Empat',
            '05' => 'Lima',
            '06' => 'Enam',
            '07' => 'Tujuh',
            '08' => 'Delapan',
            '09' => 'Sembilan',
            '10' => 'Sepuluh',
            '11' => 'Sebelas',
            '12' => 'Dua Belas',
            '13' => 'Tiga Belas',
            '14' => 'Empat Belas',
            '15' => 'Lima Belas',
            '16' => 'Enam Belas',
            '17' => 'Tujuh Belas',
            '18' => 'Delapan Belas',
            '19' => 'Sembilan Belas',
            '20' => 'Dua Puluh',
            '21' => 'Dua Puluh Satu',
            '22' => 'Dua Puluh Dua',
            '23' => 'Dua PUluh Tiga',
            '24' => 'Dua Puluh Empat',
            '25' => 'Dua Puluh Lima',
            '26' => 'Dua Puluh Enam',
            '27' => 'Dua Puluh tujuh',
            '28' => 'Dua Puluh Delapan',
            '29' => 'Dua Puluh Sembilan',
            '30' => 'Tiga Puluh',
            '31' => 'Tiga Puluh Satu',
        );
        $monthRomawi = array(
            0 => '',
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
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
        $yearRibu = array(
            0 => '',
            1 => 'Seribu',
            2 => 'Dua Ribu',
            3 => 'Tiga Ribu',
            4 => 'Empat Ribu',
            5 => 'Lima Ribu',
            6 => 'Enam Ribu',
            7 => 'Tujuh Ribu',
            8 => 'Delapan Ribu',
            9 => 'Sembilan Ribu',
        );
        $yearRatusan = array(
            0 => '',
            1 => 'Seratus',
            2 => 'Dua ratus',
            3 => 'Tiga ratus',
            4 => 'Empat ratus',
            5 => 'Lima ratus',
            6 => 'Enam ratus',
            7 => 'Tujuh ratus',
            8 => 'Delapan ratus',
            9 => 'Sembilan ratus',
        );
        if (date('d') == '12' || date('d') == '13' || date('d') == '14' || date('d') == '15' || date('d') == '16' || date('d') == '17' || date('d') == '18' || date('d') == '19') {
            $yearPuluh = array(
                0 => '',
                1 => 'sebelas',
                2 => 'Dua belas',
                3 => 'Tiga belas',
                4 => 'Empat belas',
                5 => 'Lima belas',
                6 => 'Enam belas',
                7 => 'Tujuh belas',
                8 => 'Delapan belas',
                9 => 'Sembilan belas',
            );
            $yearSatuan = array(
                0 => '',
                1 => '',
                2 => '',
                3 => '',
                4 => '',
                5 => '',
                6 => '',
                7 => '',
                8 => '',
                9 => '',
            );
        } else {
            $yearPuluh = array(
                0 => '',
                1 => 'sebelas',
                2 => 'Dua puluh',
                3 => 'Tiga puluh',
                4 => 'Empat puluh',
                5 => 'Lima puluh',
                6 => 'Enam puluh',
                7 => 'Tujuh puluh',
                8 => 'Delapan puluh',
                9 => 'Sembilan puluh',
            );
            $yearSatuan = array(
                0 => '',
                1 => 'satu',
                2 => 'Dua',
                3 => 'Tiga',
                4 => 'Empat',
                5 => 'Lima',
                6 => 'Enam',
                7 => 'Tujuh',
                8 => 'Delapan',
                9 => 'Sembilan',
            );
        }
        $angkaTertulis = "";
        $nominal = [
            0 => '',
            1 => '',
            2 => 'puluh',
            3 => 'ratus',
            4 => '',
            5 => 'puluh',
            6 => 'ratus',
            7 => 'juta',
            8 => 'puluh juta',
            9 => 'ratus juta'
        ];
        $nominaldepan = array(
            0 => '',
            1 => 'satu',
            2 => 'Dua',
            3 => 'Tiga',
            4 => 'Empat',
            5 => 'Lima',
            6 => 'Enam',
            7 => 'Tujuh',
            8 => 'Delapan',
            9 => 'Sembilan',
        );
        $jumlahT = array(
            0 => 'nol',
            1 => 'satu',
            2 => 'Dua',
            3 => 'Tiga',
            4 => 'Empat',
            5 => 'Lima',
            6 => 'Enam',
            7 => 'Tujuh',
            8 => 'Delapan',
            9 => 'Sembilan',
        );
        // $total = "1111212";
        // dd(substr($total, 0, 1));
        for ($i = 0; $i <= strlen($total); $i++) {


            //diatas puluhan
            if (strlen($total) - $i > 5) {
                if (substr($total, $i, 1) == "1") {
                    if (strlen($total) - $i != 7) {
                        $angkaTertulis = $angkaTertulis . ' se' . $nominal[strlen($total) - $i];
                    } else {
                        $angkaTertulis = $angkaTertulis . ' ' . $nominaldepan[(int)substr($total, $i, 1)] . ' ' . $nominal[strlen($total) - $i];
                    }
                } else if (substr($total, $i, 1) != '0') {
                    $angkaTertulis = $angkaTertulis . ' ' . $nominaldepan[(int)substr($total, $i, 1)] . ' ' . $nominal[strlen($total) - $i];
                } else {
                }
            }

            //dibawah ratusan
            else {
                if (substr($total, $i, 1) == "0") {
                } else if (strlen($total) - $i === 5 && substr($total, $i, 1) == "1"  && substr($total, $i + 1, 1) != "1") {
                    $angkaTertulis = $angkaTertulis . ' ' . $nominaldepan[(int)substr($total, $i + 1, 1)] . ' belas';
                } else if (strlen($total) - $i === 5 && substr($total, $i, 1) == "1" && substr($total, $i + 1, 1) == "1") {
                    $angkaTertulis = $angkaTertulis . ' sebelas';
                } else if (strlen($total) - $i === 5 && substr($total, $i, 1) != "1") {
                    $angkaTertulis = $angkaTertulis . ' ' . $nominaldepan[(int)substr($total, $i, 1)] . ' ' . $nominal[strlen($total) - $i] . ' ' . $nominaldepan[(int)substr($total, $i + 1, 1)];
                } else if (strlen($total) - $i === 3) {
                    if (substr($total, $i, 1) == "1") {
                        if (strlen($total) - $i != 7) {
                            $angkaTertulis = $angkaTertulis . ' se' . $nominal[strlen($total) - $i];
                        } else {
                            $angkaTertulis = $angkaTertulis . ' ' . $nominaldepan[(int)substr($total, $i, 1)] . ' ' . $nominal[strlen($total) - $i];
                        }
                    } else if (substr($total, $i, 1) != '0') {
                        $angkaTertulis = $angkaTertulis . ' ' . $nominaldepan[(int)substr($total, $i, 1)] . ' ' . $nominal[strlen($total) - $i];
                    } else {
                    }
                } else if (strlen($total) - $i === 2 && substr($total, $i, 1) == "1"  && substr($total, $i + 1, 1) != "1") {
                    $angkaTertulis = $angkaTertulis . ' ' . $nominaldepan[(int)substr($total, $i + 1, 1)] . ' belas';
                } else if (strlen($total) - $i === 2 && substr($total, $i, 1) == "1" && substr($total, $i + 1, 1) == "1") {
                    $angkaTertulis = $angkaTertulis . ' sebelas';
                } else if (strlen($total) - $i === 2 && substr($total, $i, 1) != "1") {
                    $angkaTertulis = $angkaTertulis . $nominaldepan[(int)substr($total, $i, 1)] . ' ' . $nominal[strlen($total) - $i] . ' ' . $nominaldepan[(int)substr($total, $i + 1, 1)];
                }

                // else if (strlen($total) - $i === 4 && substr($total, $i, 1) == "1") {
                //     $angkaTertulis = $angkaTertulis . ' ' . $nominaldepan[(int)substr($total, $i, 1)];
                // }
                if (strlen($total) - $i === 4) {
                    $angkaTertulis = $angkaTertulis . ' ribu';
                } else {
                }
            }
        }
        // dd

        // dd($angkaTertulis);
        (date('d'));
        $data = [
            'angkaTertulis' => $angkaTertulis,
            'day' => date('d'), 'month' => date('m'), 'year' => date('Y'),
            'monthRomawi' => $monthRomawi[date('n')],
            'day2Name' => $day2Name[date('d')], 'dayName' => $dayList[$day], 'monthName' => $monthList[date('n')],
            'yearRibu' => $yearRibu[substr(date('Y'), 0, 1)], 'yearRatus' => $yearRatusan[substr(date('Y'), 1, 1)],
            'yearPuluh' => $yearPuluh[substr(date('Y'), 2, 1)], 'yearSatuan' => $yearSatuan[substr(date('Y'), 3, 1)],
            'nama_staff' => 'Tenaya', 'dapartement' => 'Distribusi', 'namapelanggan' => $customer->namapelanggan,
            'nomorrekening' => $customer->nomorrekening, 'address' => $customer->alamat, 'total' => rupiah($total),
            'idareal' => $customer->idareal, 'jumlahtunggakan' => $tunggakan, 'jumlahtunggakanT' => $jumlahT[$tunggakan]
        ];

        $firstBulan = $monthList[date('n', strtotime($dataPembayaran[0]['periode']))];
        $lastBulan = $monthList[date('n', strtotime($dataPembayaran[count($dataPembayaran) - 1]['periode']))];
        // dd($firstBulan);
        // dd($data['day']);
        // $pdf = App::make('dompdf');
        $pdf = pdf::loadView('admin.lockT.hambatanPenyegelan', compact('data', 'lastBulan', 'firstBulan'));
        $pdf->setPaper('Legal', 'potrait')->render();
        return $pdf->stream();
        // $pdf = PDF::make();
        // return $pdf->stream();
        // return view('admin.lockT.perintahPenyegelan', compact('data', 'dataPembayaran'));
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
        return view('admin.lockT.create', compact('dapertements', 'subdapertements', 'dapertement_id', 'subdapertement_id', 'customer_id', 'scb'));
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
                    );
                }
            }

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
                    );
                }
            }

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

        $dataPembayaran = array();
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

        return view('admin.lockT.show', compact('customer', 'dataPembayaran', 'recap', 'lock'));
    }

    public function sppPrint($lock_id)
    {
        $id = '1041';
        $customer = Customer::join('map_koordinatpelanggan', 'map_koordinatpelanggan.nomorrekening', '=', 'tblpelanggan.nomorrekening')->where('tblpelanggan.nomorrekening', '1041')
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

        // dd($customer);
        $day = date('D');
        $dayList = array(
            'Sun' => 'Minggu',
            'Mon' => 'Senin',
            'Tue' => 'Selasa',
            'Wed' => 'Rabu',
            'Thu' => 'Kamis',
            'Fri' => 'Jumat',
            'Sat' => 'Sabtu'
        );
        $day2Name = array(
            '01' => 'Satu',
            '02' => 'Dua',
            '03' => 'Tiga',
            '04' => 'Empat',
            '05' => 'Lima',
            '06' => 'Enam',
            '07' => 'Tujuh',
            '08' => 'Delapan',
            '09' => 'Sembilan',
            '10' => 'Sepuluh',
            '11' => 'Sebelas',
            '12' => 'Dua Belas',
            '13' => 'Tiga Belas',
            '14' => 'Empat Belas',
            '15' => 'Lima Belas',
            '16' => 'Enam Belas',
            '17' => 'Tujuh Belas',
            '18' => 'Delapan Belas',
            '19' => 'Sembilan Belas',
            '20' => 'Dua Puluh',
            '21' => 'Dua Puluh Satu',
            '22' => 'Dua Puluh Dua',
            '23' => 'Dua PUluh Tiga',
            '24' => 'Dua Puluh Empat',
            '25' => 'Dua Puluh Lima',
            '26' => 'Dua Puluh Enam',
            '27' => 'Dua Puluh tujuh',
            '28' => 'Dua Puluh Delapan',
            '29' => 'Dua Puluh Sembilan',
            '30' => 'Tiga Puluh',
            '31' => 'Tiga Puluh Satu',
        );
        $monthList = array(
            0 => 'Januari',
            1 => 'Februari',
            2 => 'Maret',
            3 => 'April',
            4 => 'Mei',
            5 => 'Juli',
            6 => 'Juni',
            7 => 'Agustus',
            8 => 'September',
            9 => 'Oktober',
            10 => 'November',
            11 => 'September',
            12 => 'Desember'
        );
        $yearRibu = array(
            0 => '',
            1 => 'Seribu',
            2 => 'Dua Ribu',
            3 => 'Tiga Ribu',
            4 => 'Empat Ribu',
            5 => 'Lima Ribu',
            6 => 'Enam Ribu',
            7 => 'Tujuh Ribu',
            8 => 'Delapan Ribu',
            9 => 'Sembilan Ribu',
        );
        $yearRatusan = array(
            0 => '',
            1 => 'Seratus',
            2 => 'Dua ratus',
            3 => 'Tiga ratus',
            4 => 'Empat ratus',
            5 => 'Lima ratus',
            6 => 'Enam ratus',
            7 => 'Tujuh ratus',
            8 => 'Delapan ratus',
            9 => 'Sembilan ratus',
        );
        $yearPuluh = array(
            0 => '',
            1 => 'sebelas',
            2 => 'Dua puluh',
            3 => 'Tiga puluh',
            4 => 'Empat puluh',
            5 => 'Lima puluh',
            6 => 'Enam puluh',
            7 => 'Tujuh puluh',
            8 => 'Delapan puluh',
            9 => 'Sembilan puluh',
        );
        $yearSatuan = array(
            0 => '',
            1 => 'satu',
            2 => 'Dua',
            3 => 'Tiga',
            4 => 'Empat',
            5 => 'Lima',
            6 => 'Enam',
            7 => 'Tujuh',
            8 => 'Delapan',
            9 => 'Sembilan',
        );
        // dd(date('d'));
        $templateProcessor =  new TemplateProcessor('template-word/word2.docx');
        $templateProcessor->setValue('day', date('d'));
        $templateProcessor->setValue('month', date('m'));
        $templateProcessor->setValue('year', date('Y'));
        $templateProcessor->setValue('day2Name',  $day2Name[date('d')]);
        $templateProcessor->setValue('dayName', $dayList[$day]);
        $templateProcessor->setValue('monthName', $monthList[date('n')]);
        $templateProcessor->setValue('yearRibu', $yearRibu[substr(date('Y'), 0, 1)]);
        $templateProcessor->setValue('yearRatus', $yearRatusan[substr(date('Y'), 1, 1)]);
        $templateProcessor->setValue('yearPuluh', $yearPuluh[substr(date('Y'), 2, 1)]);
        $templateProcessor->setValue('yearSatuan', $yearSatuan[substr(date('Y'), 3, 1)]);
        $templateProcessor->setValue('nama_staff', 'Tenaya');
        $templateProcessor->setValue('dapartement', 'Distribusi');
        $templateProcessor->setValue('namapelanggan', $customer->namapelanggan);
        $templateProcessor->setValue('nomorrekening', $customer->nomorrekening);
        $templateProcessor->setValue('adress', $customer->alamat);
        $templateProcessor->setValue('total', $item->wajibdibayar);
        $templateProcessor->setValue('idareal', $customer->idareal);
        $templateProcessor->setValue('jumlahtunggakan', $tunggakan);
        $fileName = date('d') . ' ' . Bulan(date('m')) . ' ' . date('Y');
        $templateProcessor->saveAs($fileName . '.docx');
        return response()->download($fileName . '.docx');
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

        return view('admin.lockT.actionStaff', compact('action'));
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

        return view('admin.lockT.actionStaffCreate', compact('lockaction_id', 'staffs', 'action', 'action_staffs', 'action_staffs_list'));
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

    function list($lockaction_id)
    {
        abort_unless(\Gate::allows('lock_action_access'), 403);
        $actions = LockAction::with('subdapertement')
            ->with('lock')
            ->where('lock_id', $lockaction_id)
            ->get();
        return view('admin.lockT.list', compact('lockaction_id', 'actions'));
    }

    public function actioncreate($lock_id)
    {
        abort_unless(\Gate::allows('lock_action_create'), 403);
        $lock = Lock::findOrFail($lock_id);
        $dapertements = Dapertement::where('id', $lock->dapertement_id)->get();

        $staffs = Staff::all();
        return view('admin.lockT.actionCreate', compact('dapertements', 'lock_id', 'staffs'));
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
        return view('admin.lockT.lockView', compact('lock'));
    }
}
