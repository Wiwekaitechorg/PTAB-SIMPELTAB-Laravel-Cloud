<?php

namespace App\Http\Controllers\Admin;

use App\CategoryWa;
use App\Channel;
use App\CtmWilayah;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroywaHistoryRequest;
use App\LockAction;
use App\pelanggan_tiro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\WablasAreaTrait;
use App\wa_history;
use App\WaTemplate;
use GuzzleHttp\Promise\Create;
use App\Traits\TraitModel;
use App\User;
use App\wa_template_file;
use App\Traits\WablasTrait;
use GuzzleHttp\Psr7\Message;

class WhatsappBlastController extends Controller
{
    use TraitModel;

    //tampilan awal WA blast
    public function index(Request $request)
    {
        // WablasAreaTrait::sendFile();
        abort_unless(\Gate::allows('wablast_access'), 403);
        $categorys = CategoryWa::get();
        return view('admin.waBlast.index', compact('categorys'));
    }

    //Kategori Wa blast
    public function templateP(Request $request)
    {
        abort_unless(\Gate::allows('wablast_access'), 403);
        // dd($request->all());
        if (!$request->category) {
            return back()->withError('Pilih Kategori terlebih dahulu')->withInput();
        } else if ($request->category == "1") {
            return redirect()->route('admin.wablast.area', ['name' => $request->name]);
        } else if ($request->category == "2") {
            return redirect()->route('admin.wablast.categoryt', ['name' => $request->name]);
        } else {
            return redirect()->route('admin.wablast.template3', ['category' => $request->category, 'name' => $request->name]);
        }
    }

    //perbaikan
    public function area(Request $request)
    {
        abort_unless(\Gate::allows('wablast_access'), 403);
        $category = $request->name;
        $message = $request->message;
        $areas = CtmWilayah::select('id as code', 'NamaWilayah')->get();
        return view('admin.waBlast.maintenence', compact('areas', 'message', 'category'));
    }

    public function templatePer(Request $request)
    {
        abort_unless(\Gate::allows('wablast_access'), 403);

        if ($request->area) {
            $category = $request->category;
            $area = $request->area;
            $templates = WaTemplate::where('category_wa_id', 1)->get();
            return view('admin.waBlast.templatePerbaikan', compact('templates', 'category', 'area'));
        } else {
            return back()->withError('Pilih Area terlebih dahulu')->withInput();
        }
    }

    public function createMessagePer(Request $request)
    {
        abort_unless(\Gate::allows('wablast_access'), 403);
        // abort_unless(\Gate::allows('account_create'), 403);

        $area = $request->area;
        $data = [
            'template' => $request->template,
            'message' => $request->message,
            'status' => $request->status,
            'type' => $request->type,
            'category' => $request->category,
            'template_id' => $request->template_id,
        ];

        $qry = Customer::selectRaw('tblpelanggan.*')
            ->where('tblpelanggan.status', 1);


        $data1 = $request->area;
        if (count($data1) > 0) {

            for ($i = 0; $i < count($data1); $i++) {
                if ($i < 1) {

                    $qry->where('tblpelanggan.idareal', $data1[$i]);
                } else {
                    $qry->orWhere('tblpelanggan.idareal', $data1[$i]);
                }
            }
        }

        $qry->groupBy('tblpelanggan.nomorrekening');

        // $takeData = $request->takeData;
        // $takeFrom = $qry->get()->count();
        // if ($takeFrom % $takeData === 0) {
        //     $takeFrom = floor($takeFrom / $takeData);
        // } else {
        //     $takeFrom = floor($takeFrom / $takeData) + 1;
        // }
        // dd($request->takeData);
        return redirect()->route('admin.wablast.createmessageperview', ['data' => $data, 'area' => $area, 'takeData' => $request->takeData]);
    }

    public function createMessagePerView(Request $request)
    {
        // dd($request->all());
        $qry = Customer::selectRaw('tblpelanggan.*')
            ->where('tblpelanggan.status', 1);

        $data = $request->area;
        if (count($data) > 0) {

            for ($i = 0; $i < count($data); $i++) {
                if ($i < 1) {

                    $qry->where('tblpelanggan.idareal', $data[$i]);
                } else {
                    $qry->orWhere('tblpelanggan.idareal', $data[$i]);
                }
            }
        }

        $qry->groupBy('tblpelanggan.nomorrekening');

        ini_set("memory_limit", -1);
        set_time_limit(0);
        $code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
        $data2 = [];
        //ini test
        $customers = $qry->paginate($request->takeData);
        $data = $request->data;
        $takeD = $request->takeData;
        return view('admin.waBlast.createMessagePer', compact('customers', 'data', 'takeD'));
    }

    public function storeMessagePer(Request $request)
    {
        abort_unless(\Gate::allows('wablast_access'), 403);
        // dd($request->all());
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

        $channel = wa_history::selectRaw('SUM(CASE WHEN wa_histories.id=1 AND wa_histories.status="pending" THEN 1 ELSE 0 END) as total, channels.*')
            ->rightJoin('channels', 'channels.id', '=', 'wa_histories.channel_id')
            ->where('channels.type', '!=', 'reguler')
            ->whereIn('channels.id', $deviceWa)
            ->groupBy('channels.id')
            ->orderBy('total', 'asc')
            ->first();
        // dd($channel);
        // pilih channe end

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


        // ini_set("memory_limit", -1);
        // ini_set('max_input_vars', '5000');
        // set_time_limit(0);
        $code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');

        // dd($request->all());

        if (!$request->phone) {
            return back()->withError('Pilih Nomor minimal 1')->withInput();
        } else {
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
            $data = [];
            $code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            for ($i = 0; $i < count($request->phone); $i++) {
                $message = str_replace("@nama", $request->name[$i], $request->message);
                $message = str_replace("@sbg", $request->customer_id[$i], $message);
                $message = str_replace("@alamat", $request->adress[$i], $message);
                $message = str_replace("@waktu", $waktu, $message);

                $data = [
                    'phone' => $this->gantiformat($request->phone[$i]),
                    // test
                    // 'phone' => '6281236815960',
                    // 'phone' => 'a',
                    'customer_id' => $request->customer_id[$i],
                    'message' => $message,
                    'template_id' => $request->template_id,
                    // 'id_wa' => $request->name[$i],
                    'status' => 'gagal',
                    'ref_id' => $code . $request->customer_id[$i]
                ];

                $kumpulan_data[] = $data;
            }
            // dd($kumpulan_data);
            $i = 0;
            $array_merg = [];
            $temp = [];
            foreach (array_chunk($kumpulan_data, 5000) as $key => $smallerArray) {
                foreach ($smallerArray as $index => $value) {
                    // count($kumpulan_data/5000);
                    $temp[] = array_merge($value, ["created_at" => date('Y-m-d h:i:sa'), "updated_at" => date('Y-m-d h:i:sa')]);
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
                $test1 = WablasAreaTrait::sendText($temp, $channel->token);
                $temp = [];
                // dd($test1);
                if (!empty(json_decode($test1)->data->messages)) {
                    $array_merg = array_merge(json_decode($test1)->data->messages, $array_merg);
                }
            }

            $countSend = 0;
            foreach ($array_merg as $key => $value) {
                if (!empty($value->ref_id)) {
                    wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
                    $countSend = $countSend + 1;
                }
            }

            // jika ada file start

            $fileList = wa_template_file::where('template_id', $request->template_id)->get();

            if (count($fileList) > 0) {

                $code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                for ($i = 0; $i < count($request->phone); $i++) {
                    $message = str_replace("@nama", $request->name[$i], $request->message);
                    $message = str_replace("@sbg", $request->customer_id[$i], $message);
                    $message = str_replace("@alamat", $request->adress[$i], $message);
                    $message = str_replace("@waktu", $waktu, $message);

                    $data = [
                        'phone' => $this->gantiformat($request->phone[$i]),
                        // test
                        // 'phone' => '6281236815960',
                        // 'phone' => 'a',
                        'customer_id' => $request->customer_id[$i],
                        'document' => $filename,
                        'template_id' => $request->template_id,
                        // 'id_wa' => $request->name[$i],
                        'status' => 'gagal',
                        'ref_id' => $code . $request->customer_id[$i]
                    ];

                    $kumpulan_data[] = $data;
                }
                // dd($kumpulan_data);
                $i = 0;
                $array_merg = [];
                $temp = [];
                foreach (array_chunk($kumpulan_data, 5000) as $key => $smallerArray) {
                    foreach ($smallerArray as $index => $value) {
                        // count($kumpulan_data/5000);
                        $temp[] = array_merge($value, ["created_at" => date('Y-m-d h:i:sa'), "updated_at" => date('Y-m-d h:i:sa')]);
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
                    $test1 = WablasAreaTrait::sendFile($temp);
                    $temp = [];
                    // dd($test1);
                    if (!empty(json_decode($test1)->data->messages)) {
                        $array_merg = array_merge(json_decode($test1)->data->messages, $array_merg);
                    }
                }

                foreach ($array_merg as $key => $value) {
                    if (!empty($value->ref_id)) {
                        wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
                    }
                }
            }

            // jika ada file end
            return back()->withInfo('Pesan Diproses Sebanyak ' . $countSend . ' diharapkan memberi jeda untuk pengiriman selanjutnya guna meminimalisir pemblokiran ')->withInput();
        }
    }

    //perbaikan end


    // tunggakan
    public function categoryT(Request $request)
    {
        abort_unless(\Gate::allows('wablast_access'), 403);
        $category = $request->name;
        return view('admin.waBlast.categoryT', compact('category'));
    }

    public function templateT(Request $request)
    {
        abort_unless(\Gate::allows('wablast_access'), 403);
        if ($request->type) {
            $category = $request->category;
            $type = $request->type;
            $status = $request->status;
            $templates = WaTemplate::where('category_wa_id', 2)->get();
            return view('admin.waBlast.templateTunggakan', compact('templates', 'type', 'status', 'category'));
        } else {
            return back()->withError('Pilih kategori terlebih dahulu')->withInput();
        }
    }

    //pembuatan surat(tunggakan)
    public function createMessageTP(Request $request)
    {
        abort_unless(\Gate::allows('wablast_access'), 403);
        if ($request->type == "notice") {
            $type = "Penyampaian Surat";
        } else if ($request->type == "lock") {
            $type = "Penyegelan";
        } else if ($request->type == "notice2") {
            $type = "Kunjungan";
        } else if ($request->type == "unplug") {
            $type = "Pencabutan";
        } else {
            $type = "";
        }

        return redirect()->route('admin.wablast.createmessaget', ['template' => $request->template, 'message' => $request->message, 'status' => $request->status, 'type' => $type, 'category' => $request->category, 'takeData' => $request->takeData]);
    }
    public function createMessageT(Request $request)
    {
        abort_unless(\Gate::allows('wablast_access'), 403);
        $takeData = $request->takeData;
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
        // abort_unless(\Gate::allows('account_create'), 403);
        $data = [
            'template' => $request->template,
            'message' => $request->message,
            'status' => $request->status,
            'type' => $request->type,
            'category' => $request->category,
            'template_id' => $request->template_id,
        ];

        $date_now = date('Y-m-d');
        $date_comp = date('Y-m') . '-20';
        $last_4_month = date("Y-n-d", strtotime('-4 month', strtotime(date('Y-m-01'))));
        ini_set("memory_limit", -1);

        $code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
        // dd($request->status);
        // if ($request->status == "3") {
        //     if ($request->type == "Penyegelan") {
        //         $qry = Customer::selectRaw('locks.id, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
        //             ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
        //             ->leftJoin('simpletab.locks', 'tblpelanggan.nomorrekening', '=', 'locks.customer_id')
        //             ->where('locks.id', null)
        //             ->where('tblpelanggan.status', 1);
        //         // dd($qry);
        //     } else {
        //         $qry = Customer::selectRaw('locks.id, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
        //             ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
        //             ->leftJoin('simpletab.locks', 'tblpelanggan.nomorrekening', '=', 'locks.customer_id')
        //             ->where('locks.id', '!=', null)
        //             ->where('tblpelanggan.status', 1);
        //         // dd($qry);
        //     }
        // } else {
        $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
            ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
            ->where('tblpelanggan.status', 1);
        // }
        // dd($qry);
        if ($date_now > $date_comp) {
            $qry->having('jumlahtunggakan', '>', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->groupBy('tblpembayaran.nomorrekening')
                ->where('tblpelanggan.status', 1);
        } else {
            $qry->having('jumlahtunggakan', '>', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->groupBy('tblpembayaran.nomorrekening')
                ->where('tblpelanggan.status', 1);
        }
        $data2 = [];
        $customers = $qry->paginate($request->takeData);
        // dd($customers);
        // if ($request->status == '3') {
        //     foreach ($customers as $value) {
        //         $test = LockAction::where('customer_id', $value->nomorrekening)->where('type', 'lock')->first();
        //         if ($request->type == 'Penyegelan') {
        //             if (!$test) {
        //                 $message = str_replace("@nama", $value->name, $request->message);
        //                 $message = str_replace("@sbg", $value->nomorrekening, $message);
        //                 $message = str_replace("@alamat", $value->alamat, $message);
        //                 $message = str_replace("@waktu", $waktu, $message);
        //                 $data = [
        //                     // 'phone' => $this->gantiformat($value->phone),
        //                     // test
        //                     'phone' => 'x',
        //                     'customer_id' => $value->nomorrekening,
        //                     'message' => $message,
        //                     // 'id_wa' => 'empty',
        //                     // 'template_id' => $request->template_id,
        //                     'status' => 'gagal',
        //                     'ref_id' => $code . $value->nomorrekening
        //                 ];
        //                 $kumpulan_data[] = $data;
        //             }
        //         } else {
        //             if ($test) {
        //                 $message = str_replace("@nama", $value->name, $request->message);
        //                 $message = str_replace("@sbg", $value->nomorrekening, $message);
        //                 $message = str_replace("@alamat", $value->alamat, $message);
        //                 $message = str_replace("@waktu", $waktu, $message);
        //                 $data = [
        //                     // 'phone' => $this->gantiformat($value->phone),
        //                     // test
        //                     'phone' => 'x',
        //                     'customer_id' => $value->nomorrekening,
        //                     'message' => $message,
        //                     // 'id_wa' => 'empty',
        //                     // 'template_id' => $request->template_id,
        //                     'status' => 'gagal',
        //                     'ref_id' => $code . $value->nomorrekening
        //                 ];
        //                 $kumpulan_data[] = $data;
        //             }
        //         }
        //     }
        // } 
        // else {
        //     foreach ($customers as $value) {
        //         $message = str_replace("@nama", $value->name, $request->message);
        //         $message = str_replace("@sbg", '(No.SBG ' . $value->nomorrekening . ')', $message);
        //         $message = str_replace("@alamat", $value->alamat, $message);
        //         $message = str_replace("@waktu", $waktu, $message);
        //         $data = [
        //             // 'phone' => $this->gantiformat($value->phone),
        //             // test
        //             'phone' => 'x',
        //             'customer_id' => $value->nomorrekening,
        //             'message' => $message,
        //             // 'id_wa' => 'empty',
        //             // 'template_id' => $request->template_id,
        //             'status' => 'gagal',
        //             'ref_id' => $code . $value->nomorrekening
        //         ];
        //         $kumpulan_data[] = $data;
        //     }
        // }
        // dd($kumpulan_data);

        // dd($file);
        // $file = json_encode($file);
        // dd($file);

        return view('admin.waBlast.createMessage', compact('data', 'customers', 'takeData'));
    }

    //menyimpan data dari (tunggakan)
    public function storeMessageT(Request $request)
    {
        abort_unless(\Gate::allows('wablast_access'), 403);
        // pilih channel start
        $deviceWa = [];
        $channel_list = Channel::where('type', 'tunggakan')->get();
        foreach ($channel_list as $key => $value) {
            $data = WablasTrait::checkOnline($value->token);
            if (json_decode($data)->status) {
                $deviceWa[] = [$value->id];
            } else {
            }
        }

        $channel = wa_history::selectRaw('SUM(CASE WHEN wa_histories.id=1 AND wa_histories.status="pending" THEN 1 ELSE 0 END) as total, channels.*')
            ->rightJoin('channels', 'channels.id', '=', 'wa_histories.channel_id')
            ->where('channels.type', '!=', 'reguler')
            ->whereIn('channels.id', $deviceWa)
            ->groupBy('channels.id')
            ->orderBy('total', 'asc')
            ->first();
        // dd($channel);
        // pilih channe end
        // dd($request->all());
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


        // ini_set("memory_limit", -1);
        // ini_set('max_input_vars', '5000');
        // set_time_limit(0);
        $code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');

        // dd($request->all());

        if (!$request->phone) {
            return back()->withError('Pilih Nomor minimal 1')->withInput();
        } else {
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
            $data = [];
            $code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            for ($i = 0; $i < count($request->phone); $i++) {
                $message = str_replace("@nama", $request->name[$i], $request->message);
                $message = str_replace("@sbg", $request->customer_id[$i], $message);
                $message = str_replace("@alamat", $request->adress[$i], $message);
                $message = str_replace("@waktu", $waktu, $message);

                $data = [
                    'phone' => $this->gantiformat($request->phone[$i]),
                    // test
                    // 'phone' => '6281236815960',
                    // 'phone' => 'a',
                    'customer_id' => $request->customer_id[$i],
                    'message' => $message,
                    'template_id' => $request->template_id,
                    // 'id_wa' => $request->name[$i],
                    'status' => 'gagal',
                    'ref_id' => $code . $request->customer_id[$i]
                ];

                $kumpulan_data[] = $data;
            }
            // dd($kumpulan_data);
            $i = 0;
            $array_merg = [];
            $temp = [];
            foreach (array_chunk($kumpulan_data, 5000) as $key => $smallerArray) {
                foreach ($smallerArray as $index => $value) {
                    // count($kumpulan_data/5000);
                    $temp[] = array_merge($value, ["created_at" => date('Y-m-d h:i:sa'), "updated_at" => date('Y-m-d h:i:sa')]);
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
                $test1 = WablasAreaTrait::sendText($temp, $channel->token);
                $temp = [];
                // dd($test1);
                if (!empty(json_decode($test1)->data->messages)) {
                    $array_merg = array_merge(json_decode($test1)->data->messages, $array_merg);
                }
            }

            $countSend = 0;
            foreach ($array_merg as $key => $value) {
                if (!empty($value->ref_id)) {
                    wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
                    $countSend = $countSend + 1;
                }
            }
            return back()->withInfo('Pesan Diproses Sebanyak ' . $countSend . ' diharapkan memberi jeda untuk pengiriman selanjutnya guna meminimalisir pemblokiran ')->withInput();
        }
    }

    // tunggakan end



    public function template3(Request $request)
    {
        abort_unless(\Gate::allows('wablast_access'), 403);
        $category = $request->name;
        $templates = WaTemplate::where('category_wa_id', $request->category)->get();
        return view('admin.waBlast.template', compact('templates', 'category'));
    }

    //pembuatan surat(kategori selain tunggakan dan perbaikan)
    public function create(Request $request)
    {
        // dd($request->all());
        abort_unless(\Gate::allows('wablast_access'), 403);
        // abort_unless(\Gate::allows('account_create'), 403);
        $waTemplate = WaTemplate::get();
        $data = [
            'template' => $request->template,
            'message' => $request->message,
            'status' => $request->status,
            'type' => $request->type,
            'template_id' => $request->template_id,

        ];
        $file = [
            ['image' => 'image'],
            ['image' => 'file']
        ];
        $fileN = [];
        $imageN = [];
        $img_path = "/images/image_wa";
        $file_path = "/files/pdf_wa";

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
                $resourceImage->move($basepath . $img_path, $nameImage);
                $imageN[] = ["https://simpletabadmin.ptab-vps-storage.com/images/image_wa/" . $nameImage];
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

        // dd($fileN, $imageN);
        return redirect()->route('admin.wablast.creater', ['message' => $request->message, 'template_id' => $request->template_id, 'takeData' => $request->takeData, 'file' => $fileN, 'image' => $imageN]);
    }

    //untuk menyimpan data hasil dari post create
    public function creater(Request $request)
    {
        // dd($request->pag);
        abort_unless(\Gate::allows('wablast_access'), 403);
        $waTemplate = WaTemplate::get();
        $customers = Customer::FilterWilayah($request->area)->where('tblpelanggan.status', 1)->FilterNomorrekening($request->nomorrekening)->paginate($request->takeData, ['*'], 'page', $request->page);
        $areas = CtmWilayah::select('id as code', 'NamaWilayah')->get();
        $takeFrom = Customer::count();
        $takeData = $request->takeData;
        // dd($takeFrom);
        $data = [
            'template' => $request->template,
            'message' => $request->message,
            'status' => $request->status,
            'type' => $request->type,
            'template_id' => $request->template_id,
        ];
        $file = json_encode($request->file);
        $image = json_encode($request->image);
        // dd($file);
        // $file = json_encode($file);
        // dd($file);
        return view('admin.waBlast.create', compact('customers', 'data', 'areas', 'takeData', 'file', 'image'));
    }

    //untuk ganti format nomorHp
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




    //simpan data dari katefori selain tunggakan dan perbaikan
    public function store(Request $request)
    {
        // $result = User::select('email')->orderBy('email')->get();
        // echo "<pre>";
        // print_r($result);
        // dd(User::orderBy('email')->get());
        // dd(json_decode($request->file));

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

        $channel = wa_history::selectRaw('SUM(CASE WHEN wa_histories.id=1 AND wa_histories.status="pending" THEN 1 ELSE 0 END) as total, channels.*')
            ->rightJoin('channels', 'channels.id', '=', 'wa_histories.channel_id')
            ->where('channels.type', '!=', 'reguler')
            ->whereIn('channels.id', $deviceWa)
            ->groupBy('channels.id')
            ->orderBy('total', 'asc')
            ->first();
        // dd($channel);
        // pilih channe end

        // dd('sssss', $channel);
        $fileN = json_decode($request->file);
        $imageN = json_decode($request->image);
        // dd($fileN, $imageN[0][0]);
        $limit = env('LIMIT_SEND');
        $kumpulan_data = [];

        if ($request->filter) {
            return redirect()->route('admin.wablast.creater', ['area' => $request->area, 'nomorrekening' => $request->nomorrekening, 'takeData' => $request->takeData, 'message' => $request->message, 'file' => $fileN, 'image' => $imageN]);
        }
        // selected
        else {
            if (!$request->phone) {
                return back()->withError('Pilih Nomor minimal 1')->withInput();
            } else {
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
                $data = [];
                $countSend = 0;
                // jika ada pesan
                if ($request->message != "") {
                    $code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                    // dd($request->phone);
                    for ($i = 0; $i < count($request->phone); $i++) {
                        $message = str_replace("@nama", $request->name[$i], $request->message);
                        $message = str_replace("@sbg", $request->customer_id[$i], $message);
                        $message = str_replace("@alamat", $request->adress[$i], $message);
                        $message = str_replace("@waktu", $waktu, $message);

                        $data = [
                            'phone' => $this->gantiformat($request->phone[$i]),
                            // test
                            // 'phone' => '6281236815960',
                            // 'phone' => 'a',
                            'customer_id' => $request->customer_id[$i],
                            'message' => $message,
                            'template_id' => $request->template_id,
                            // 'id_wa' => $request->name[$i],
                            'status' => 'gagal',
                            'ref_id' => $code . $request->customer_id[$i]
                        ];

                        $kumpulan_data[] = $data;
                    }
                    // dd($kumpulan_data);
                    $i = 0;
                    $array_merg = [];
                    $temp = [];
                    foreach (array_chunk($kumpulan_data, 5000) as $key => $smallerArray) {
                        foreach ($smallerArray as $index => $value) {
                            // count($kumpulan_data/5000);
                            $temp[] = array_merge($value, ["created_at" => date('Y-m-d h:i:sa'), "updated_at" => date('Y-m-d h:i:sa'), "channel_id" => $channel ? $channel->id : null]);
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
                    // // send WA
                    foreach (array_chunk($kumpulan_data, $limit) as $key => $smallerArray) {
                        foreach ($smallerArray as $index => $value) {
                            $temp[] = $value;
                        }
                        $test1 = WablasAreaTrait::sendText($temp, $channel->token);
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

                // dd(json_decode($request->file));
                // jika ada file start
                // dd('tes');
                if ($fileN) {
                    if (count($fileN) > 0) {
                        for ($fn = 0; $fn < count($fileN); $fn++) {
                            $code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');

                            for ($i = 0; $i < count($request->phone); $i++) {
                                $message = str_replace("@nama", $request->name[$i], $request->message);
                                $message = str_replace("@sbg", $request->customer_id[$i], $message);
                                $message = str_replace("@alamat", $request->adress[$i], $message);
                                $message = str_replace("@waktu", $waktu, $message);
                                // dd($request->name[$i]);
                                $data3 = [
                                    'phone' => $this->gantiformat($request->phone[$i]),
                                    // test
                                    // 'phone' => '6282147860693',
                                    // 'phone' => '6281236815960',
                                    // 'phone' => 'a',
                                    'customer_id' => $request->customer_id[$i],
                                    // 'document' => 'https://simpletabadmin.ptab-vps-storage.com/images/pdf_wa/' . $f->file,
                                    'document' => $fileN[$fn][0],
                                    // 'caption' => 'tess',
                                    'template_id' => "0",
                                    // 'id_wa' => $request->name[$i],
                                    'status' => 'gagal',
                                    'ref_id' => $code . $request->customer_id[$i]
                                ];

                                $kumpulan_data3[] = $data3;
                            }
                            // dd($kumpulan_data3);
                            $i = 0;
                            $array_merg = [];
                            $temp = [];
                            foreach (array_chunk($kumpulan_data3, 1) as $key => $smallerArray) {
                                $c = 0;
                                foreach ($smallerArray as $index => $value) {
                                    // dd($value);
                                    // count($kumpulan_data/5000);
                                    $temp[] = array_merge($value, ["created_at" => date('Y-m-d h:i:sa'), "updated_at" => date('Y-m-d h:i:sa'), "message" => $value['document'], "channel_id" => $channel->id]);
                                    // $i = $i + 1;
                                    unset($temp[$c]["document"]);
                                    $c++;
                                }



                                // dd($temp);

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

                            for ($i = 0; $i < count($request->phone); $i++) {
                                $message = str_replace("@nama", $request->name[$i], $request->message);
                                $message = str_replace("@sbg", $request->customer_id[$i], $message);
                                $message = str_replace("@alamat", $request->adress[$i], $message);
                                $message = str_replace("@waktu", $waktu, $message);
                                // dd($request->name[$i]);
                                $data4 = [
                                    'phone' => $this->gantiformat($request->phone[$i]),
                                    // test
                                    // 'phone' => '6281236815960',
                                    // 'phone' => '6282147860693',
                                    // 'phone' => 'a',
                                    'customer_id' => $request->customer_id[$i],
                                    // 'document' => 'https://simpletabadmin.ptab-vps-storage.com/images/pdf_wa/' . $f->file,
                                    'image' => $imageN[$in][0],
                                    'caption' => '',
                                    'template_id' => "0",
                                    // 'id_wa' => $request->name[$i],
                                    'status' => 'gagal',
                                    'ref_id' => $code . $request->customer_id[$i]
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
                                    $temp[] = array_merge($value, ["created_at" => date('Y-m-d h:i:sa'), "updated_at" => date('Y-m-d h:i:sa'), "message" => $value["image"], "channel_id" => $channel ? $channel->id : null]);
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


                return back()->withInfo('Pesan Diproses Sebanyak ' . $countSend . ' diharapkan memberi jeda untuk pengiriman selanjutnya guna meminimalisir pemblokiran')->withInput();
            }
        }
    }

    //cek history
    public function history(Request $request)
    {
        abort_unless(\Gate::allows('wablast_access'), 403);
        $wa_historys = wa_history::FilterStatus($request->status)->FilterDate($request->from, $request->to)->get();

        //default view
        return view('admin.waBlast.history', compact('wa_historys'));
    }

    public function destroy($id)
    {
        wa_history::where('id', $id)->delete();
        return redirect()->back();
    }
    public function massDestroy(MassDestroywaHistoryRequest $request)
    {
        wa_history::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }


    //Dari Api
    public function callback()
    {
        $content = json_decode(file_get_contents('php://input'), true);

        $id = $content['id'];
        $status = $content['status'];
        $phone = $content['phone'];
        $note = $content['note'];
        $sender = $content['sender'];
        $deviceId = $content['deviceId'];

        $data = [
            'status' => $status,
        ];
        wa_history::where('id_wa', $id)->update($data);
    }
    public function checkOnline()
    {
        $d = WablasAreaTrait::checkOnline();
        $data = json_decode($d)->data[0];
    }
}
