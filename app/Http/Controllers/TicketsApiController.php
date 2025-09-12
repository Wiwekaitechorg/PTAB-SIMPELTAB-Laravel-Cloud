<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\CategoryApi;
use App\Customer;
use App\Http\Controllers\Controller;
use App\StaffApi;
use App\Subdapertement;
use App\TicketApi;
use App\Ticket_Image;
use App\Traits\TraitModel;
use App\Traits\WablasTrait;
use App\User;
use App\wa_history;
use App\WaTemplate;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OneSignal;

class TicketsApiController extends Controller
{
    use TraitModel;
    use WablasTrait;

    public function test()
    {
        $subdapertement_def = Subdapertement::where('def', '1')->get();
        // $dapertement_def_id = $subdapertement_def->dapertement_id;
        // $subdapertement_def_id = $subdapertement_def->id;
        return $subdapertement_def;
        // return $dapertement_def_id." - ".$subdapertement_def_id;
    }

    public function index($id)
    {
        try {
            $ticket = TicketApi::where('customer_id', $id)->with('ticket_image')->with('category')->with('customer')->with('action')->orderBy('id', 'DESC')->get();
            return response()->json([
                'message' => 'Data Ticket',
                'data' => $ticket,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal Mengambil data',
                'err' => $ex,
            ]);
        }
    }

    public function store(Request $request)
    {

        $last_code = $this->get_last_code('ticket');

        $code = acc_code_generate($last_code, 8, 3);
        $img_path = "/images/complaint";
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

                $resourceImage->move($basepath . $img_path, $img_name);

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

            $video_path = "/videos/complaint";
            $resource = $request->file('video');
            // $filename = $resource->getClientOriginalName();
            // $file_extVideo = $request->file('video')->extension();
            $video_name = $video_path . "/" . strtolower($code) . '-' . $dataForm->customer_id . '.mp4';

            $resource->move($basepath . $video_path, $video_name);
        }

        if (!isset($dataForm->title)) {
            $dataForm->title = 'Tiket Keluhan';
        }

        if (!isset($dataForm->category_id)) {
            $category = CategoryApi::orderBy('id', 'ASC')->first();
            $dataForm->category_id = $category->id;
        }

        //set SPK
        $dateNow = date('Y-m-d H:i:s');
        $subdapertement_def = Subdapertement::where('def', '1')->first();
        $dapertement_def_id = $subdapertement_def->dapertement_id;
        $subdapertement_def_id = $subdapertement_def->id;
        $arr['dapertement_id'] = $dapertement_def_id;
        $arr['month'] = date("m");
        $arr['year'] = date("Y");
        $last_spk = $this->get_last_code('spk-ticket', $arr);
        $spk = acc_code_generate($last_spk, 21, 17, 'Y');

        $data = array(
            'code' => $code,
            'type' => 'notice',
            'image' => '',
            'video' => $video_name,
            'description' => $dataForm->description,
            'title' => $dataForm->title,
            'category_id' => 83,
            'customer_id' => $dataForm->customer_id,
            'lat' => $dataForm->lat,
            'lng' => $dataForm->lng,
            'creator' => 'member',
        );

        try {
            $ticket = TicketApi::create($data);

            if ($ticket) {
                $upload_image = new Ticket_Image;
                $upload_image->image = str_replace("\/", "/", json_encode($dataImageName));
                $upload_image->ticket_id = $ticket->id;
                $upload_image->save();
            }

            //send notif to admin
            $admin_arr = User::where('dapertement_id', 0)->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->title;
                //wa notif
                $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                $wa_data_group = [];
                //get phone user
                if ($admin->staff_id > 0) {
                    $staff = StaffApi::where('id', $admin->staff_id)->first();
                    $phone_no = $staff->phone;
                } else {
                    $phone_no = $admin->phone;
                }
                $wa_data = [
                    'phone' => $this->gantiFormat($phone_no),
                    'customer_id' => null,
                    'message' => $message,
                    'template_id' => '',
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
                //onesignal notif
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

            //send notif to humas
            $admin_arr = User::where('subdapertement_id', $subdapertement_def_id)
                ->where('staff_id', 0)
                ->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                $message = 'Humas: Keluhan Baru Diterima : ' . $dataForm->title;
                //wa notif
                $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                $wa_data_group = [];
                //get phone user
                if ($admin->staff_id > 0) {
                    $staff = StaffApi::where('id', $admin->staff_id)->first();
                    $phone_no = $staff->phone;
                } else {
                    $phone_no = $admin->phone;
                }
                $wa_data = [
                    'phone' => $this->gantiFormat($phone_no),
                    'customer_id' => null,
                    'message' => $message,
                    'template_id' => '',
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
                //onesignal notif
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

            //send notif to user
            $customer = Customer::find($ticket->customer_id);

            //  pesan baru start
            $waTemplate = WaTemplate::where('id', 48)->first();

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

            // $message = 'Terimakasih telah menggunakan aplikasi SimpelTAB, Keluhan anda telah kami terima dan segera di Tindak Lanjuti. Mohon maaf atas ketidak nyamanannya.';
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

            return response()->json([
                'message' => 'Keluhan Terkirim',
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }
}
