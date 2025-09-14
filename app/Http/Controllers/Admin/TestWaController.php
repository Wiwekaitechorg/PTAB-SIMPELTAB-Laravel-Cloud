<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\TraitModel;
use App\Traits\WablasTrait;
use App\wa_history;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestWaController extends Controller
{
    use TraitModel;
    public function index(Request $request)
    {
        $limit = env('LIMIT_SEND');

        // data dummy / sementara sebelum ada request
        // data yang diperlukan
        $name = 'adi';
        $phone = '082147860693';
        $customer_id = '101';
        $adress = 'tabanan';
        $requestMessage = 'Selamat @waktu, Nama anda @nama, id anda @sbg, alamat anda @alamat';

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
        $cek = [];
        $kumpulan_data = [];
        $data = [];
        $code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');

        // digunakan jika ada data yang dikirim

        // $message = str_replace("@nama", $request->name, $request->message);
        // $message = str_replace("@sbg", $request->customer_id, $message);
        // $message = str_replace("@alamat", $request->adress, $message);

        // digunakan untuk test

        $message = str_replace("@nama", $name, $requestMessage);
        $message = str_replace("@sbg", $customer_id, $message);
        $message = str_replace("@alamat", $adress, $message);
        $message = str_replace("@waktu", $waktu, $message);

        $data = [
            'phone' => $this->gantiformat($phone),
            'customer_id' => $customer_id,
            'message' => $message,
            'template_id' => 'test1',
            'status' => 'gagal',
            'ref_id' => $code . $customer_id
        ];

        $kumpulan_data[] = $data;

        $i = 0;
        $array_merg = [];
        $temp = [];

        $temp = array_merge($data, ["created_at" => date('Y-m-d h:i:sa'), "updated_at" => date('Y-m-d h:i:sa')]);
        //    dd($temp);
        DB::table('wa_histories')->insert($temp);
        // dd($data);
        $test1 = WablasTrait::sendText($kumpulan_data);
        // dd($test1);
        if (!empty(json_decode($test1)->data->messages)) {
            $array_merg = array_merge(json_decode($test1)->data->messages, $array_merg);
        }

        foreach ($array_merg as $key => $value) {
            if (!empty($value->ref_id)) {
                wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
            }
        }
        dd($test1);
    }

    // untuk ganti nomor ke 62
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
}
