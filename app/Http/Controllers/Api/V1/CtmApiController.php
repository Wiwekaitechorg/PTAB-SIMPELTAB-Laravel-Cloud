<?php

namespace App\Http\Controllers\api\v1\customer;

use App\CtmGambarmetersms;
use App\CtmPemakaianAir;
use App\CtmPembayaran;
use App\CtmRequest;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Traits\TraitModel;
use Illuminate\Http\Request;
use DB;
use App\CtmPelanggan;
use App\wa_history;
use App\Traits\WablasTrait;
use App\WaTemplate;
use Illuminate\Support\Facades\Storage;

class CtmApiController extends Controller
{
    use TraitModel;
    use WablasTrait;

    public function ctmUse($id)
    {
        try {
            $data = array();
            for ($i = 0; $i < 12; $i++) {
                $index = $i + 1;
                $ctm = CtmPemakaianAir::selectRaw("pencatatanmeter" . $index . " AS pencatatanmeter,pemakaianair" . $index . " AS pemakaianair")
                    ->where('nomorrekening', $id)
                    ->where('tahunrekening', date('Y'))
                    ->first();

                if (!empty($ctm) && $ctm->pencatatanmeter > 0) {
                    array_push($data, array(
                        'bulanrekening' => $index,
                        'tahunrekening' => date('Y'),
                        'pencatatanmeter' => $ctm->pencatatanmeter,
                        'pemakaianair' => $ctm->pemakaianair,
                    ));
                }
            }
            // $data = json_encode($data);
            // $data = json_decode($data, true, JSON_UNESCAPED_SLASHES);
            return response()->json([
                'message' => 'Data CTM',
                'data' => $data,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal Mengambil data',
                'err' => $ex,
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

    public function ctmPayOLD($id)
    {
        try {
            $date_now = date("Y-m-d");
            $date_comp = date("Y-m") . "-20";
            $month_now = date('n');
            $month_next = date('n', strtotime('+1 month')) - 1;
            if ($month_now > $month_next) {
                $month_next = $month_next + 12;
            }
            if ($date_now > $date_comp) {
                $ctm_lock = 0;
                $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                    ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->where('tblpembayaran.nomorrekening', $id)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                    ->orderBy('tblpembayaran.tahunrekening', 'ASC')
                    ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                    ->get();
            } else {
                $ctm_lock = 1;
                $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                    ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->where('tblpembayaran.nomorrekening', $id)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                    ->orderBy('tblpembayaran.tahunrekening', 'ASC')
                    ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                    ->get();
            }
            $ctm_num_row = count($ctm) - 1;
            foreach ($ctm as $key => $item) {
                $sisa = $item->wajibdibayar - $item->sudahdibayar;
                //if not paid
                if ($sisa > 0) {
                    $ctm[$key]->tglbayarterakhir = "";
                }
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

    public function ctmPay($id)
    {
        try {
            $date_now = date("Y-m-d");
            $date_comp = date("Y-m") . "-20";
            $month_now = date('n');
            $year_now = date('Y');
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
            $status_paid_this_month = 0;
            foreach ($ctm as $key => $item) {
                //get this month paid
                if ($item->bulanrekening == $month_now && $item->tahunrekening == $year_now) {
                    if ($item->statuslunas == 2) {
                        $status_paid_this_month = 1;
                    }
                }
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
                'status_paid_this_month' => $status_paid_this_month,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal Mengambil data',
                'err' => $ex,
            ]);
        }
    }

    public function ctmList($id)
    {
        try {
            $ctm = CtmGambarmetersms::selectRaw("gambarmetersms.*,gambarmeter.filegambar as filegambar")
                ->leftjoin('gambarmeter', 'gambarmeter.idgambar', '=', 'gambarmetersms.idgambar')
                ->where('gambarmetersms.nomorrekening', $id)
                ->where('gambarmetersms.tahunrekening', date('Y'))
                ->where('gambarmetersms.bulanrekening', '!=', date('n'))
                ->orderBy('gambarmetersms.bulanrekening', 'DESC')
                ->get();
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

    public function ctmListBAK($id)
    {
        try {
            // $ctm = CtmPembayaran::selectRaw("DISTINCT tblpembayaran.*,gambarmeter.filegambar")
            //     ->leftjoin('gambarmetersms', 'gambarmetersms.nomorrekening', '=', 'tblpembayaran.nomorrekening')
            //     ->leftjoin('gambarmeter', 'gambarmeter.idgambar', '=', 'gambarmetersms.idgambar')
            //     ->where('tblpembayaran.nomorrekening', $id)
            //     ->where('tblpembayaran.tahunrekening', date('Y'))
            //     ->orderBy('tblpembayaran.bulanrekening', 'DESC')
            //     ->get();
            $ctm = CtmPembayaran::selectRaw("DISTINCT tblpembayaran.*")
                ->where('tblpembayaran.nomorrekening', $id)
                ->where('tblpembayaran.tahunrekening', date('Y'))
                ->orderBy('tblpembayaran.bulanrekening', 'DESC')
                ->get();
            foreach ($ctm as $key => $ctm_row) {
                $year = (int) $ctm[$key]['tahunrekening'];
                $month = (int) $ctm[$key]['bulanrekening'];
                $month_prev = (int) date('m', strtotime($year . '-' . $month . ' -1 month', time()));
                $year_prev = date('Y', strtotime($year . '-' . $month . ' -1 month', time()));
                $gambarmeter = CtmGambarmetersms::selectRaw("gambarmeter.filegambar as filegambar")
                    ->leftjoin('gambarmeter', 'gambarmeter.idgambar', '=', 'gambarmetersms.idgambar')
                    ->where('gambarmetersms.nomorrekening', $id)
                    ->where('gambarmetersms.bulanrekening', $month_prev)
                    ->where('gambarmetersms.tahunrekening', $year_prev)
                    ->first();
                if (!empty($gambarmeter)) {
                    $ctm[$key]['filegambar'] = $gambarmeter->filegambar;
                } else {
                    $ctm[$key]['filegambar'] = '';
                }
            }
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

    public function ctmRequestHistory($id)
    {
        try {
            $ctmrequests = CtmRequest::with('customer')->where('norek', $id)
                ->orderBy('created_at', 'DESC')->get();

            return response()->json([
                'message' => 'Success',
                'data' => $ctmrequests,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal Mengambil data',
                'err' => $ex,
            ]);
        }
    }

    public function ctmRequest(Request $request)
    {
        //$data = $request->all();
        $data = json_decode($request->form);
        $var = [];
        foreach ($data as $key => $dat) {
            $var[$key] = $dat;
        }
        $var['datecatatf1'] = date("Y-m-d"); //2021-08-16
        $var['datecatatf2'] = date("F d, Y, G:i:s a"); //August 16, 2021, 15:23:37 pm
        $var['datecatatf3'] = date("Y-m-d G:i:s"); //2021-08-16 15:23:37
        //get prev
        $var['year'] = date("Y");
        $var['month'] = date("m");

        //get month year rekening
        $datecatatf1_arr = explode("-", $var['datecatatf1']);
        $month_catat = $datecatatf1_arr[1];
        $year_catat = $datecatatf1_arr[0];

        //img path
        $img_path = "/gambar-test";
        // $img_path = "/gambar";
        //$basepath = str_replace("laravel-simpletab", "public_html/pdam/", \base_path());
        $basepath = "/home/ptab-vps-storage/htdocs/www.ptab-vps-storage.com/pdam";
        //$path = $basepath . $img_path . "/" . $year_catat . $month_catat . "/"; //path nanti bisa dirubah disini mode 755
        $path = "/pdam" . $img_path . "/" . $year_catat . $month_catat . "/";
        if (!Storage::disk('sftp')->exists($path)) {
            //mkdir($path, 0777, true);
            Storage::disk('sftp')->makeDirectory($path, 0777, true);
        }
        $new_image_name = $var['norek'] . "_" . $year_catat . "_" . $month_catat . ".jpg"; //nama image dibuat sendiri
        //move_uploaded_file($_FILES['file']['tmp_name'], $path . $new_image_name);
        $img_name = $img_path . "/" . $new_image_name;
        $resourceImage = $request->file('image');
        //$resourceImage->move($path, $img_name);
        Storage::disk('sftp')->put($path, $request->file('image'));
        $path_img = "/" . "gambar/" . $year_catat . $month_catat . "/";
        $path_img1 = "D:/MyAMP/www/" . "gambar/" . $year_catat . $month_catat . "/";
        $var['img'] = $path_img . $new_image_name;
        $var['img1'] = $path_img1 . $new_image_name;

        //insert data
        //set data
        $data = array(
            'norek' => $var['norek'],
            'wmmeteran' => $var['wmmeteran'],
            'namastatus' => $var['namastatus'],
            'opp' => $var['opp'],
            'lat' => $var['lat'],
            'lng' => $var['lng'],
            'accuracy' => $var['accuracy'],
            'operator' => $var['operator'],
            'nomorpengirim' => $var['nomorpengirim'],
            'statusonoff' => $var['statusonoff'],
            'description' => $var['description'],
            'img' => $var['img'],
            'img1' => $var['img1'],
            'status' => 'pending',
            'datecatatf1' => $var['datecatatf1'],
            'datecatatf2' => $var['datecatatf2'],
            'datecatatf3' => $var['datecatatf3'],
            'year' => $var['year'],
            'month' => $var['month'],
        );

        //send notif to user
        $customer = Customer::where('nomorrekening', $var['norek'])->first();

        //  pesan baru start
        $waTemplate = WaTemplate::where('id', 50)->first();

        $jam = date('H');
        if ($jam > 0 && $jam < 11) {
            $waktu = "pagi";
        } else if ($jam > 10 && $jam < 15) {
            $waktu = "siang";
        } else if ($jam > 14 && $jam < 19) {
            $waktu = "sore";
        } else if ($jam > 18 && $jam < 23) {
            $waktu = "malam";
        } else {
            $waktu = "";
        }

        $message = $waTemplate->message;

        $message = str_replace("@nama", $customer->name, $message);
        $message = str_replace("@sbg", $customer->customer_id, $message);
        $message = str_replace("@alamat", $customer->adress, $message);
        $message = str_replace("@waktu", $waktu, $message);

        //pesan baru end 

        // $message = 'Terimakasih telah menggunakan Aplikasi SimpelTAB. Laporan Baca WM Mandiri anda telah kami terima. Kepedulian Anda merupakan Peningkatan Pelayanan Kami.';
        // //wa notif
        $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
        $wa_data_group = [];
        //get phone user
        $phone_no = $customer->phone;
        $wa_data = [
            'phone' => $this->gantiFormat($phone_no),
            'customer_id' => null,
            'message' => $message,
            'template_id' => $waTemplate->id,
            'status' => 'gagal',
            'ref_id' => $wa_code,
            'created_at' => date('Y-m-d h:i:sa'),
            'updated_at' => date('Y-m-d h:i:sa'),
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

        try {
            $ticket = CtmRequest::create($data);
            return response()->json([
                'message' => 'Baca Meter Mandiri Terkirim',
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    public function ctmPrev(Request $request)
    {
        //$data = $request->all();
        $data = json_decode($request->form);
        $var = [];
        foreach ($data as $key => $dat) {
            $var[$key] = $dat;
        }
        $var['datecatatf1'] = date("Y-m-d"); //2021-08-16
        $var['datecatatf2'] = date("F d, Y, G:i:s a"); //August 16, 2021, 15:23:37 pm
        $var['datecatatf3'] = date("Y-m-d G:i:s"); //2021-08-16 15:23:37
        //get prev
        $year = date("Y");
        $month = date("m");
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
        $img_path = "/gambar-test";
        //$basepath = str_replace("laravel-simpletab", "public_html/pdam/", \base_path());
        $basepath = "/home/ptab-vps-storage/htdocs/www.ptab-vps-storage.com/pdam";

        $path = $basepath . $img_path . "/" . $year_catat . $month_catat . "/"; //path nanti bisa dirubah disini mode 755
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $new_image_name = $var['norek'] . "_" . $var['tahunrekening'] . "_" . $month_catat . ".jpg"; //nama image dibuat sendiri
        //move_uploaded_file($_FILES['file']['tmp_name'], $path . $new_image_name);
        $img_name = $img_path . "/" . $new_image_name;
        $resourceImage = $request->file('image');
        $resourceImage->move($path, $img_name);
        $path_img = "/" . "gambar/" . $year_catat . $month_catat . "/";
        $path_img1 = "D:/MyAMP/www/" . "gambar/" . $year_catat . $month_catat . "/";
        $var['filegambar'] = $path_img . $new_image_name;
        $var['filegambar1'] = $path_img1 . $new_image_name;

        //get meterawal
        $getCtmMeterPrev = $this->getCtmMeterPrev($var['norek'], $var['bulanrekening'], $var['tahunrekening']);
        $meterawal = $var['pencatatanmeterprev'];

        if ((int) $var['namastatus'] == 111) {
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

        // $var['nomorrekening']=2;
        // $var['pemakaianair'] =63;
        // $tblpelanggan_arr = $this->getCtmJenispelanggan($var['nomorrekening']);
        // //hitung rp-tagihan
        // $tblpelanggan_arr['pemakaianair'] = $var['pemakaianair'];
        // $data = $this->getCtmTagihan($tblpelanggan_arr);

        // $var['nomorrekening']='60892';
        // $var['bulanrekening']='9';
        // $var['tahunrekening']='2021';
        // $var['statusonoff']='off';
        // $var['_synced']='0';

        // $data = $this->insupdCtmStatusonoff($var);

        // $var['bulanrekening']='9';
        // $var['tahunrekening']='2021';
        // $var['nomorrekening']='9997';
        // $var['namastatus']='114';
        // $var['operator']='EKA';
        // $var['_synced']='0';

        // $data = $this->insupdCtmStatussmpelanggan($var);

        // $var['bulanrekening']='8';
        // $var['pencatatanmeter']='6917';
        // $var['pemakaianair']='30';
        // $var['nomorrekening']='1';
        // $var['tahunrekening']='2022';
        // $var['datecatatf1']='2021-08-06';
        // $var['operator']='Sumardhana';
        // $var['_synced']='0';

        // $data = $this->insupdCtmPemakaianair($var);

        // $var['bulanrekening']='9';
        // $var['tahunrekening']='2021';
        // $var['nomorrekening']='38563';
        // $var['lat']='-8.5570138';
        // $var['lng']='115.10578';
        // $var['datecatatf3']='2021-08-19 10:54:19';
        // $var['accuracy']='2001';
        // $var['_synced']='0';

        // $data = $this->insupdCtmMapKunjungan($var);

        // $var['nomorpengirim']='+6282235454214';
        // $var['bulanrekening']='9';
        // $var['tahunrekening']='2021';
        // $var['datecatatf1']='2021-08-31';
        // $var['nomorrekening']='38563';
        // $var['pencatatanmeter']='2209';
        // $var['idgambar']='4107901';
        // $var['_synced']='0';

        // $data = $this->insupdCtmGambarmetersms($var);

        // $var['nomorpengirim']='+6282235454214';
        // $var['bulanrekening']='9';
        // $var['tahunrekening']='2021';
        // $var['datecatatf1']='2021-09-19';
        // $var['filegambar']='/gambar/202108/38563_2021_08.jpg';
        // $var['operator']='EKA';
        // $var['datecatatf2']='July 19, 2021, 10:54:19 am';
        // $var['filegambar1']='D:/MyAMP/www/gambar/202109/38563_2021_09.jpg';
        // $var['_synced']='0';

        // $data = $this->insupdCtmGambarmeter($var);

        $nomorrekening = '1';
        $month = '07';
        $year = '2021';
        // $data=$this->getCtmPrev($nomorrekening, $month, $year);
        // $data=$this->getCtmAvg($nomorrekening, $month, $year);
        // $data=$this->getCtmMeterPrev($nomorrekening, $month, $year);
        return response()->json([
            'message' => 'Berhasil',
            'data' => $data,
        ]);
    }
}
