<?php

namespace App\Http\Controllers\Admin;

use App\Channel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\CustomWaImport;
use App\wa_history;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\WablasAreaTrait;
use App\Traits\WablasTrait;

class CustomWaController extends Controller
{
    public function index()
    {
        return view('admin.whatsapp.custom.index');
    }

    //untuk ganti format nomorHp
    public function gantiformat($nomorhp)
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

    public function import(Request $request)
    {
        // pilih channel start
        $deviceWa = [];
        $channel_list = Channel::get();
        foreach ($channel_list as $key => $value) {
            $data = WablasTrait::checkOnline($value->token);
            if (json_decode($data)->status) {
                $deviceWa[] = [$value->id];
            } else {
            }
        }

        // dd($deviceWa);

        $channel =   wa_history::selectRaw('SUM(CASE WHEN wa_histories.id=1 AND wa_histories.status="pending" THEN 1 ELSE 0 END) as total, channels.*')
            ->rightJoin('channels', 'channels.id', '=', 'wa_histories.channel_id')
            ->where('channels.type', '!=', 'reguler')
            ->whereIn('channels.id', $deviceWa)
            ->groupBy('channels.id')
            ->orderBy('total', 'asc')
            ->first();
        // dd($channel);
        // if ($channel) {
        //     $channel =   wa_history::selectRaw('count(wa_histories.id) as total, channels.*')
        //         ->join('channels', 'channels.id', '=', 'wa_histories.channel_id')
        //         ->where('wa_histories.status', 'pending')
        //         ->groupBy('channels.id')
        //         ->orderBy('total', 'asc')
        //         ->first();
        // }
        // dd($channel);
        $fileN = [];
        $imageN = [];
        $videoN = [];
        $img_path = "/images/image_wa";
        $file_path = "/files/pdf_wa";
        $video_path = "/videos/video_wa";

        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath  = base_path().'/public';

        // upload image
        if ($request->file('image')) {

            foreach ($request->file('image') as $key => $image) {
                $resourceImage = $image;
                $nameImage = $image->getClientOriginalName();
                $file_extImage = $image->extension();
                $nameImage = preg_replace("/[^a-zA-Z.-Z0-9]/", " ", $nameImage);
                $nameImage = str_replace(" ", "_", $nameImage);
                // $img_name = 'File' . date('Y-m-d h:i:s') . '.' . $image->extension();
                // dd($nameImage);
                $resourceImage->move($basepath . $img_path, "$nameImage");
                $imageN[] = ["https://simpletabadmin.ptab-vps-storage.com/images/image_wa/" . "$nameImage"];
            }
        }


        // upload video
        if ($request->file('video')) {

            foreach ($request->file('video') as $key => $video) {
                $resourceVideo = $video;
                $nameVideo = $video->getClientOriginalName();
                $file_extvideo = $video->extension();
                $nameVideo = preg_replace("/[^a-zA-Z.-Z0-9]/", " ", $nameVideo);
                $nameVideo = str_replace(" ", "_", $nameVideo);
                // $img_name = 'File' . date('Y-m-d h:i:s') . '.' . $video->extension();
                // dd($nameVideo);
                $resourceVideo->move($basepath . $video_path, "test.mp4");
                $videoN[] = ["https://simpletabadmin.ptab-vps-storage.com/videos/video_wa/" . "test.mp4"];
            }
        }

        // upload file
        if ($request->file('file')) {

            foreach ($request->file('file') as $key => $file) {
                $resourcefile = $file;
                $namefile = $file->getClientOriginalName();
                $file_extfile = $file->extension();
                $namefile = preg_replace("/[^a-zA-Z.-Z0-9]/", " ", $namefile);
                $namefile = str_replace(" ", "_", $namefile);
                // $img_name = 'File' . date('Y-m-d h:i:s') . '.' . $file->extension();

                $resourcefile->move($basepath . $file_path, $namefile);
                $fileN[] = ["https://simpletabadmin.ptab-vps-storage.com/files/pdf_wa/" . $namefile];
            }
        }


        $import = new CustomWaImport;
        $test =  Excel::import($import, $request->file('files'));

        $array = $import->getArray();

        abort_unless(\Gate::allows('wablast_access'), 403);
        $limit = env('LIMIT_SEND');
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
        $kumpulan_data = [];
        $date_now = date('Y-m-d');
        $date_comp = date('Y-m') . '-20';
        $last_4_month = date("Y-n-d", strtotime('-4 month', strtotime(date('Y-m-01'))));

        $customers = $import->getArray();

        ini_set("memory_limit", -1);
        set_time_limit(0);
        $countSend = 0;
        if ($request->message != "") {
            $code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $data2 = [];
            //ini test

            // dd($customers[2]['name']);
            for ($i = 0; $i < count($customers); $i++) {
                $message = str_replace("@nama", $customers[$i]['name'], $request->message);
                // $message = str_replace("@sbg", $customers[$i][''], $message);
                $message = str_replace("@alamat", $customers[$i]['adress'], $message);
                $message = str_replace("@waktu", $waktu, $message);


                //Terlebih dahulu kita trim dl
                $nomorhp = trim($customers[$i]['phone']);
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

                if ($nomorhp != "") {
                    $data = [
                        'phone' => $nomorhp,
                        // test
                        // 'phone' => 'x',
                        'customer_id' => '',
                        'message' => $message,
                        // 'id_wa' => 'empty',
                        // 'template_id' => $request[$i]->template_id,
                        'status' => 'gagal',
                        'ref_id' => $code . $customers[$i]['id']
                    ];

                    $kumpulan_data[] = $data;
                }
            }
            // dd($kumpulan_data);
            $i = 0;
            $array_merg = [];
            $temp = [];
            foreach (array_chunk($kumpulan_data, 5000) as $key => $smallerArray) {
                foreach ($smallerArray as $index => $value) {
                    // count($kumpulan_data/5000);
                    $temp[] = array_merge($value, ["created_at" => date('Y-m-d h:i:sa'), "updated_at" => date('Y-m-d h:i:sa'), "channel_id" => $channel->id]);
                    // $i = $i + 1;

                }

                // $array_merg = array_merge($temp, $array_merg);
                DB::table('wa_histories')->insert($temp);
                $temp = [];
            }

            // dd($temp);
            // dd($kumpulan_data);
            // dd($kumpulan_data[65892]);
            $data2 = [];

            $array_merg = [];
            // send WA
            foreach (array_chunk($kumpulan_data, $limit) as $key => $smallerArray) {
                foreach ($smallerArray as $index => $value) {
                    $temp[] = $value;
                }
                $test1 = WablasAreaTrait::sendText($temp,  $channel->token);
                $temp = [];
                // dd($test1);
                if (!empty(json_decode($test1)->data->messages)) {
                    $array_merg = array_merge(json_decode($test1)->data->messages, $array_merg);
                }
            }


            foreach ($array_merg as $key => $value) {
                if (!empty($value->ref_id)) {
                    wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
                    $countSend = $countSend + 1;
                }
            }
        }


        // jika ada file start
        if ($fileN) {
            if (count($fileN) > 0) {
                for ($fn = 0; $fn < count($fileN); $fn++) {
                    $code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');

                    for ($i = 0; $i < count($customers); $i++) {
                        $message = str_replace("@nama", $customers[$i]['name'], $request->message);
                        // $message = str_replace("@sbg", $customers[$i][''], $message);
                        $message = str_replace("@alamat", $customers[$i]['adress'], $message);
                        $message = str_replace("@waktu", $waktu, $message);
                        // dd($request->name[$i]);
                        //Terlebih dahulu kita trim dl
                        $nomorhp = trim($customers[$i]['phone']);
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
                        $data3 = [
                            'phone' => $nomorhp,
                            // test
                            // 'phone' => '6282147860693',
                            // 'phone' => '6281236815960',
                            // 'phone' => 'a',
                            'customer_id' => '',
                            // 'document' => 'https://simpletabadmin.ptab-vps-storage.com/images/pdf_wa/' . $f->file,
                            'document' => $fileN[$fn][0],
                            // 'caption' => 'tess',
                            'template_id' => '',
                            // 'id_wa' => $request->name[$i],
                            'status' => 'gagal',
                            'ref_id' => $code . $customers[$i]['id']
                        ];

                        $kumpulan_data3[] = $data3;
                    }
                    // dd($kumpulan_data3);
                    $i = 0;
                    $array_merg = [];
                    $temp = [];
                    foreach (array_chunk($kumpulan_data3, 5000) as $key => $smallerArray) {
                        $c = 0;
                        foreach ($smallerArray as $index => $value) {
                            // count($kumpulan_data/5000);
                            $temp[] = array_merge($value, ["created_at" => date('Y-m-d h:i:sa'), "updated_at" => date('Y-m-d h:i:sa'), "message" => "file", "channel_id" => $channel->id]);
                            // $i = $i + 1;
                            unset($temp[$c]["document"]);
                            $c++;
                        }

                        // $array_merg = array_merge($temp, $array_merg);
                        DB::table('wa_histories')->insert($temp);
                        $temp = [];
                    }
                    // dd($temp);
                    // dd($kumpulan_data);
                    // dd($kumpulan_data[65892]);
                    $data2 = [];

                    $array_merg = [];
                    // send WA
                    foreach (array_chunk($kumpulan_data3, $limit) as $key => $smallerArray) {
                        foreach ($smallerArray as $index => $value) {
                            $temp[] = $value;
                        }
                        // dd($temp);
                        $test1 = WablasAreaTrait::sendFile($temp, $channel->token);
                        $temp = [];
                        // dd($test1);
                        if (!empty(json_decode($test1)->data->messages)) {
                            $array_merg = array_merge(json_decode($test1)->data->messages, $array_merg);
                        }
                    }

                    // dd($array_merg);
                    foreach ($array_merg as $key => $value) {
                        if (!empty($value->ref_id)) {
                            wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
                            $countSend = $countSend + 1;
                        }
                    }
                    $kumpulan_data3 = [];
                }
            }
        }
        // jika ada image end


        // jika ada image start
        if ($imageN) {
            // dd($imageN);
            if (count($imageN) > 0) {
                for ($in = 0; $in < count($imageN); $in++) {
                    $code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');

                    for ($i = 0; $i < count($customers); $i++) {
                        $message = str_replace("@nama", $customers[$i]['name'], $request->message);
                        // $message = str_replace("@sbg", $customers[$i][''], $message);
                        $message = str_replace("@alamat", $customers[$i]['adress'], $message);
                        $message = str_replace("@waktu", $waktu, $message);

                        //Terlebih dahulu kita trim dl
                        $nomorhp = trim($customers[$i]['phone']);
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
                        // dd($request->name[$i]);
                        $data4 = [
                            'phone' => $nomorhp,
                            // test
                            // 'phone' => '6281236815960',
                            // 'phone' => '6282147860693',
                            // 'phone' => 'a',
                            'customer_id' => '',
                            // 'document' => 'https://simpletabadmin.ptab-vps-storage.com/images/pdf_wa/' . $f->file,
                            'image' => $imageN[$in][0],
                            'caption' => '',
                            'template_id' => '',
                            // 'id_wa' => $request->name[$i],
                            'status' => 'gagal',
                            'ref_id' => $code . $customers[$i]['id']
                        ];

                        $kumpulan_data4[] = $data4;
                    }
                    // dd($kumpulan_data4);
                    $i = 0;
                    $array_merg = [];
                    $temp = [];
                    foreach (array_chunk($kumpulan_data4, 5000) as $key => $smallerArray) {
                        $d = 0;
                        foreach ($smallerArray as $index => $value) {
                            // count($kumpulan_data/5000);
                            $temp[] = array_merge($value, ["created_at" => date('Y-m-d h:i:sa'), "updated_at" => date('Y-m-d h:i:sa'), "message" => "file", "channel_id" => $channel->id]);
                            // $i = $i + 1;
                            unset($temp[$d]["image"]);
                            unset($temp[$d]["caption"]);
                            $d++;
                        }

                        // $array_merg = array_merge($temp, $array_merg);
                        DB::table('wa_histories')->insert($temp);
                        $temp = [];
                    }
                    // dd($temp);
                    // dd($kumpulan_data4);
                    // dd($kumpulan_data[65892]);
                    $data2 = [];

                    $array_merg = [];
                    // send WA
                    foreach (array_chunk($kumpulan_data4, $limit) as $key => $smallerArray) {
                        foreach ($smallerArray as $index => $value) {
                            $temp[] = $value;
                        }
                        // dd($temp);
                        $test1 = WablasAreaTrait::sendImage($temp, $channel->token);
                        $temp = [];
                        // dd($test1);
                        if (!empty(json_decode($test1)->data->messages)) {
                            $array_merg = array_merge(json_decode($test1)->data->messages, $array_merg);
                        }
                    }

                    // dd($array_merg);
                    foreach ($array_merg as $key => $value) {
                        if (!empty($value->ref_id)) {
                            wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
                            $countSend = $countSend + 1;
                        }
                    }
                    $kumpulan_data4 = [];
                }
            }
        }

        // jika ada image end


        // jika ada video start
        if ($videoN) {
            // dd($videoN);
            if (count($videoN) > 0) {
                for ($vn = 0; $vn < count($videoN); $vn++) {
                    $code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');

                    for ($i = 0; $i < count($customers); $i++) {
                        $message = str_replace("@nama", $customers[$i]['name'], $request->message);
                        // $message = str_replace("@sbg", $customers[$i][''], $message);
                        $message = str_replace("@alamat", $customers[$i]['adress'], $message);
                        $message = str_replace("@waktu", $waktu, $message);

                        //Terlebih dahulu kita trim dl
                        $nomorhp = trim($customers[$i]['phone']);
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
                        // dd($request->name[$i]);
                        $data5 = [
                            'phone' => $nomorhp,
                            // test
                            // 'phone' => '6281236815960',
                            // 'phone' => '6282147860693',
                            // 'phone' => 'a',
                            'customer_id' => '',
                            // 'document' => 'https://simpletabadmin.ptab-vps-storage.com/videos/pdf_wa/' . $f->file,
                            'video' => $videoN[$vn][0],
                            'caption' => '',
                            'template_id' => '',
                            // 'id_wa' => $request->name[$i],
                            'status' => 'gagal',
                            'ref_id' => $code . $customers[$i]['id']
                        ];

                        $kumpulan_data5[] = $data5;
                    }
                    // dd($kumpulan_data5);
                    $i = 0;
                    $array_merg = [];
                    $temp = [];
                    foreach (array_chunk($kumpulan_data5, 5000) as $key => $smallerArray) {
                        $d = 0;
                        foreach ($smallerArray as $index => $value) {
                            // count($kumpulan_data/5000);
                            $temp[] = array_merge($value, ["created_at" => date('Y-m-d h:i:sa'), "updated_at" => date('Y-m-d h:i:sa'), "message" => "file", "channel_id" => $channel->id]);
                            // $i = $i + 1;
                            unset($temp[$d]["video"]);
                            unset($temp[$d]["caption"]);
                            $d++;
                        }

                        // $array_merg = array_merge($temp, $array_merg);
                        DB::table('wa_histories')->insert($temp);
                        $temp = [];
                    }
                    // dd($temp);
                    // dd($kumpulan_data4);
                    // dd($kumpulan_data[65892]);
                    $data2 = [];

                    $array_merg = [];
                    // send WA
                    foreach (array_chunk($kumpulan_data5, $limit) as $key => $smallerArray) {
                        foreach ($smallerArray as $index => $value) {
                            $temp[] = $value;
                        }
                        // dd($temp);
                        $test1 = WablasAreaTrait::sendFile($temp, $channel->token);
                        $temp = [];
                        // dd($test1);
                        if (!empty(json_decode($test1)->data->messages)) {
                            $array_merg = array_merge(json_decode($test1)->data->messages, $array_merg);
                        }
                    }

                    // dd($array_merg);
                    foreach ($array_merg as $key => $value) {
                        if (!empty($value->ref_id)) {
                            wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
                            $countSend = $countSend + 1;
                        }
                    }
                    $kumpulan_data5 = [];
                }
            }
        }

        // jika ada video end


        return redirect()->route('admin.historywa.index')->withInfo('Pesan Diproses Sebanyak ' . $countSend)->withInput();
    }
}
