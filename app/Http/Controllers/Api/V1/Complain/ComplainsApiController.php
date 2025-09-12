<?php
namespace App\Http\Controllers\Api\V1\Complain;

use App\Absence;
use App\AbsenceRequest;
use App\AbsenceRequestLogs;
use App\Complain;
use App\ComplainAction;
use App\ComplainCheck;
use App\ComplainStatus;
use App\CtmPbkNew;
use App\CtmWilayah;
use App\CustomerApi;
use App\CustomerMaps;
use App\Http\Controllers\Controller;
use App\Requests;
use App\ShiftChange;
use App\StaffApi;
use App\Subdapertement;
use App\TicketApi;
use App\Ticket_Image;
use App\Traits\TraitModel;
use App\Traits\WablasTrait;
use App\User;
use App\wa_history;
use App\WorkUnit;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Image;
use OneSignal;
use App\ComplainOpp;

class ComplainsApiController extends Controller
{
    use WablasTrait;
    use TraitModel;

    // list opp
    public function complainOpp(Request $request)
    {
        try {
            $opps = ComplainOpp::where('operator', $request->operator)->with('pelanggan')->get();

            return response()->json([
                'message' => 'success',
                'data'    => $opps,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'failed',
                'data'    => $ex,
            ]);
        }
    }

    public function loginNew(Request $request)
    {
        try {
            $admin = User::where('email', request('email'))->with('roles')->with('dapertement')->first();
            if (empty($admin)) {
                //check on pbk
                $pbk = CtmPbkNew::where('Name', request('email'))->first();
                //if not in pbk
                if (empty($pbk)) {
                    return response()->json([
                        'success' => false,
                        'message' => ' Email/UserName Yang Di masukkan Salah',
                    ]);
                } else {
                    $isMd5  = preg_match('/^[a-f0-9]{32}$/', $request->password);
                    $hashed = $isMd5 ? $request->password : md5($request->password);
                    if ($hashed === $pbk->Password) {

                        $permission = '';
                        Auth::login($pbk);
                        $token = Auth::user()->createToken('authToken')->accessToken;

                        return response()->json([
                            'success'    => true,
                            'message'    => 'success login',
                            'token'      => $token,
                            'data'       => $pbk,
                            'password'   => $request->password,
                            'permission' => $permission,
                        ]);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => ' Password Yang Di masukkan Salah md5',
                        ]);
                    }
                }
            }
            $role        = $admin->roles[0];
            $credentials = $request->validate([
                'email'    => ['required'],
                'password' => ['required'],
            ]);

            if (Hash::check($request->password, $admin->password)) {
                //  $this->smsApi($admin->phone, $request->OTP);

                $role->load('permissions');
                $permission = $role->permissions->pluck('title');
                Auth::login($admin);
                $token = Auth::user()->createToken('authToken')->accessToken;

                return response()->json([
                    'success'    => true,
                    'message'    => 'success login',
                    'token'      => $token,
                    'data'       => $admin,
                    'password'   => $request->password,
                    'permission' => $permission,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => ' Password Yang Di masukkan Salah Hash',
                ]);
            }
        } catch (QueryException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function login(Request $request)
    {
        try {
            $admin = User::where('email', request('email'))->with('roles')->with('dapertement')->first();
            if (empty($admin)) {
                return response()->json([
                    'success' => false,
                    'message' => ' Email Yang Di masukkan Salah',
                ]);
            }
            $role        = $admin->roles[0];
            $credentials = $request->validate([
                'email'    => ['required'],
                'password' => ['required'],
            ]);

            if (Hash::check($request->password, $admin->password)) {
                //  $this->smsApi($admin->phone, $request->OTP);

                $role->load('permissions');
                $permission = $role->permissions->pluck('title');
                Auth::login($admin);
                $token = Auth::user()->createToken('authToken')->accessToken;

                return response()->json([
                    'success'    => true,
                    'message'    => 'success login',
                    'token'      => $token,
                    'data'       => $admin,
                    'password'   => $request->password,
                    'permission' => $permission,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => ' Password Yang Di masukkan Salah',
                ]);
            }
        } catch (QueryException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function close(Request $request)
    {
        //complain
        $complain = Complain::where('id', $request->complain_id)->first();

        try {
            $complain->status     = 'close';
            $complain->close_type = $request->close_type;
            $complain->ticket_id  = $request->ticket_id;
            $complain->save();
            return response()->json([
                'message' => 'Laporan Ditutup',
                'data'    => $complain,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => $ex,
                'data'    => '',
            ]);
        }
    }

    public function ticketStore(Request $request)
    {
        $complain = Complain::where('id', $request->complain_id)->first();
        // return response()->json([
        //         'data' => $complain,
        //         'message' => 'Keluhan Diterima',
        //     ]);

        $last_code = $this->get_last_code('ticket');
        $code      = acc_code_generate($last_code, 8, 3);
        $img_path  = "/images/complaint";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath      = base_path() . '/public';
        $dataForm      = $request;
        $responseImage = '';

        //set images
        $dataImageName = [];
        foreach (json_decode($complain->image, true) as $key => $image) {
            //copy image
            $sourcePath      = $basepath . $image;
            $desti_image     = str_replace("/complainservice", "/complaint", $image);
            $destinationPath = $basepath . $desti_image;
            copy($sourcePath, $destinationPath);
            $dataImageName[] = $desti_image;
        }

        //set video
        $video_name = '';
        if ($complain->video) {
            //copy video
            $sourcePath      = $basepath . $complain->video;
            $desti_video     = str_replace("/complainservice", "/complaint", $complain->video);
            $destinationPath = $basepath . $desti_video;
            copy($sourcePath, $destinationPath);
            $video_name = $complain->video;
        }

        //def subdap
        $dateNow               = date('Y-m-d H:i:s');
        $subdapertement_def    = Subdapertement::where('def', '1')->first();
        $dapertement_def_id    = $subdapertement_def->dapertement_id;
        $subdapertement_def_id = $subdapertement_def->id;
        if (! isset($dataForm->dapertement_id) || $dataForm->dapertement_id == '' || $dataForm->dapertement_id <= 0) {
            $dapertement_id = $dapertement_def_id;
        } else {
            $dapertement_id = $dataForm->dapertement_id;
        }

        //set SPK
        $arr['dapertement_id'] = $dapertement_id;
        $arr['month']          = date("m");
        $arr['year']           = date("Y");
        $last_spk              = $this->get_last_code('spk-ticket', $arr);
        $spk                   = acc_code_generate($last_spk, 21, 17, 'Y');

        //get lat lng customer
        $customermaps = CustomerMaps::where('nomorrekening', $dataForm->customer_id)->first();
        if (! empty($customermaps)) {
            if (! empty($customermaps->lat)) {
                $dataForm->lat = $customermaps->lat;
            }
            if (! empty($customermaps->lng)) {
                $dataForm->lng = $customermaps->lng;
            }
        }
        //set address
        if (! isset($dataForm->address) || $dataForm->address == '') {
            $address_value = '';
        } else {
            $address_value = $dataForm->address;
        }
        //set data
        $data = [
            'code'                   => $code,
            'title'                  => $dataForm->title,
            'category_id'            => $dataForm->category_id,
            'description'            => $dataForm->description,
            'image'                  => '',
            'video'                  => $video_name,
            'customer_id'            => $dataForm->customer_id,
            'lat'                    => $dataForm->lat,
            'lng'                    => $dataForm->lng,
            'dapertement_id'         => $dapertement_id,
            'spk'                    => $spk,
            'dapertement_receive_id' => $dapertement_id,
            'address'                => $address_value,
        ];

        if ($dapertement_def_id != $dataForm->dapertement_id) {
            $data['delegated_at'] = $dateNow = date('Y-m-d H:i:s');
        }

        try {
            $ticket = TicketApi::create($data);
            if ($ticket) {
                //upd complain
                $complain->status    = 'active';
                $complain->ticket_id = $ticket->id;
                $complain->save();
                //upd ticket image
                $upload_image            = new Ticket_Image;
                $upload_image->image     = str_replace("\/", "/", json_encode($dataImageName));
                $upload_image->ticket_id = $ticket->id;
                $upload_image->save();
            }

            //send notif to admin
            $admin_arr = User::where('dapertement_id', 0)->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                $message      = 'Admin: Keluhan Baru Diterima : ' . $dataForm->description;
                //wa notif
                $wa_code       = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                $wa_data_group = [];
                //get phone user
                if ($admin->staff_id > 0) {
                    $staff    = StaffApi::where('id', $admin->staff_id)->first();
                    $phone_no = $staff->phone;
                } else {
                    $phone_no = $admin->phone;
                }
                $wa_data = [
                    'phone'       => $this->gantiFormat($phone_no),
                    'customer_id' => null,
                    'message'     => $message,
                    'template_id' => '',
                    'status'      => 'gagal',
                    'ref_id'      => $wa_code,
                    'created_at'  => date('Y-m-d h:i:sa'),
                    'updated_at'  => date('Y-m-d h:i:sa'),
                ];
                $wa_data_group[] = $wa_data;
                DB::table('wa_histories')->insert($wa_data);
                $wa_sent    = WablasTrait::sendText($wa_data_group);
                $array_merg = [];
                if (! empty(json_decode($wa_sent)->data->messages)) {
                    $array_merg = array_merge(json_decode($wa_sent)->data->messages, $array_merg);
                }
                foreach ($array_merg as $key => $value) {
                    if (! empty($value->ref_id)) {
                        wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
                    }
                }
                //onesignal notif
                if (! empty($id_onesignal)) {
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
                $message      = 'Humas: Keluhan Baru Diterima : ' . $dataForm->description;
                //wa notif
                $wa_code       = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                $wa_data_group = [];
                //get phone user
                if ($admin->staff_id > 0) {
                    $staff    = StaffApi::where('id', $admin->staff_id)->first();
                    $phone_no = $staff->phone;
                } else {
                    $phone_no = $admin->phone;
                }
                $wa_data = [
                    'phone'       => $this->gantiFormat($phone_no),
                    'customer_id' => null,
                    'message'     => $message,
                    'template_id' => '',
                    'status'      => 'gagal',
                    'ref_id'      => $wa_code,
                    'created_at'  => date('Y-m-d h:i:sa'),
                    'updated_at'  => date('Y-m-d h:i:sa'),
                ];
                $wa_data_group[] = $wa_data;
                DB::table('wa_histories')->insert($wa_data);
                $wa_sent    = WablasTrait::sendText($wa_data_group);
                $array_merg = [];
                if (! empty(json_decode($wa_sent)->data->messages)) {
                    $array_merg = array_merge(json_decode($wa_sent)->data->messages, $array_merg);
                }
                foreach ($array_merg as $key => $value) {
                    if (! empty($value->ref_id)) {
                        wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
                    }
                }
                //onesignal notif
                if (! empty($id_onesignal)) {
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

            //send notif to departement terkait
            $admin_arr = User::where('dapertement_id', $dapertement_id)
                ->where('subdapertement_id', 0)
                ->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                $message      = 'Bagian: Keluhan Baru Diterima : ' . $dataForm->description;
                //wa notif
                $wa_code       = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                $wa_data_group = [];
                //get phone user
                if ($admin->staff_id > 0) {
                    $staff    = StaffApi::where('id', $admin->staff_id)->first();
                    $phone_no = $staff->phone;
                } else {
                    $phone_no = $admin->phone;
                }
                $wa_data = [
                    'phone'       => $this->gantiFormat($phone_no),
                    'customer_id' => null,
                    'message'     => $message,
                    'template_id' => '',
                    'status'      => 'gagal',
                    'ref_id'      => $wa_code,
                    'created_at'  => date('Y-m-d h:i:sa'),
                    'updated_at'  => date('Y-m-d h:i:sa'),
                ];
                $wa_data_group[] = $wa_data;
                DB::table('wa_histories')->insert($wa_data);
                $wa_sent    = WablasTrait::sendText($wa_data_group);
                $array_merg = [];
                if (! empty(json_decode($wa_sent)->data->messages)) {
                    $array_merg = array_merge(json_decode($wa_sent)->data->messages, $array_merg);
                }
                foreach ($array_merg as $key => $value) {
                    if (! empty($value->ref_id)) {
                        wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
                    }
                }
                //onesignal notif
                if (! empty($id_onesignal)) {
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

            return response()->json([
                'message' => 'Keluhan Diterima',
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal',
            ]);
        }
    }

    public function checkDestroy(Request $request)
    {
        $action = ComplainCheck::find($request->action_id);
        // dd($complain);
        try {
            //unlink img
            $basepath = base_path() . '/public';
            if (trim($action->image) != '') {
                foreach (json_decode($action->image, true) as $key => $complain_image) {
                    $img     = $complain_image;
                    $img     = str_replace('"', '', $img);
                    $img     = str_replace('[', '', $img);
                    $img     = str_replace(']', '', $img);
                    $img_arr = explode(",", $img);
                    foreach ($img_arr as $img_name) {
                        $file_path = $basepath . $img_name;
                        if (trim($img_name) != '' && file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                }
            }
            //unlink video
            $file_path = $basepath . $action->video;
            if (trim($action->video) != '' && trim(file_exists($file_path))) {
                unlink($file_path);
            }
            $action->delete();
            return response()->json([
                'message' => 'Data Berhasil Di Hapus',
                'data'    => $action,
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Data Masih Terkait dengan data yang lain',
                'data'    => $e,
            ]);
        }
    }

    public function checkstore(Request $request)
    {
        //complain
        $complain = Complain::where('id', $request->complain_id)->first();
        //user
        $user_row      = User::where('staff_id', $request->user_id)->first();
        $user_id       = $user_row->id;
        $department    = '';
        $subdepartment = 0;
        $staff         = 0;
        if (isset($user_id) && $user_id != '') {
            $admin = User::with('roles')->find($user_id);
            $role  = $admin->roles[0];
            $role->load('permissions');
            $permission = json_decode($role->permissions->pluck('title'));
            if (! in_array("complain_all_access", $permission)) {
                $department    = $admin->dapertement_id;
                $subdepartment = $admin->subdapertement_id;
                $staff         = $admin->staff_id;
            }
        }

        $img_path   = "/images/complaincheck";
        $video_path = "/videos/complaincheck";
        // //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath      = base_path() . '/public';
        $dataImageName = [];
        // upload image
        if ($request->file('images')) {
            foreach ($request->file('images') as $key => $image) {
                $nameImage     = strtolower($complain->code);
                $file_extImage = $image->extension();
                $nameImage     = str_replace(" ", "-", $nameImage);
                $img_name      = $img_path . "/" . $nameImage . "-" . $complain->code . $key . "." . $file_extImage;
                $image         = $image;
                $imgFile       = Image::make($image->getRealPath());
                // dd($imgFile->insert($basepath . "/images/Logo.png", 'bottom-right', 10, 10));
                $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $request->lat . ' lng : ' . $request->lng, 10, 10, function ($font) {
                    // $font->file(str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path()) . '/font/Titania-Regular.ttf');
                    $font->size(14);
                    $font->color('#000000');
                    $font->valign('top');
                })->save($basepath . '/' . $img_name);
                $dataImageName[] = $img_name;
            }
        }
        // video
        if ($request->file('video')) {
            $video_path = "/videos/complaincheck";
            $resource   = $request->file('video');
            $video_name = $video_path . "/" . strtolower($complain->code) . '-' . $complain->code . '.mp4';
            $resource->move($basepath . $video_path, $video_name);
        } else {
            $video_name = "";
        }

        $dateNow = date('Y-m-d H:i:s');

        $data = [
            'description'        => $request->description,
            'memo'               => $request->memo,
            'status'             => 'pending',
            'dapertement_id'     => $department,
            'complain_id'        => $request->complain_id,
            'start'              => $dateNow,
            'subdapertement_id'  => $subdepartment,
            'staff_id'           => $request->user_id,
            'image'              => str_replace("\/", "/", json_encode($dataImageName)),
            'video'              => $video_name,
            'complain_status_id' => $request->complain_status_id,
        ];

        try {
            $action = ComplainCheck::create($data);
            return response()->json([
                'message' => 'Tindakan Terkirim',
                'data'    => $action,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => $ex,
                'data'    => '',
            ]);
        }
    }

    public function checklist(Request $request)
    {
        $complain_id = $request->complain_id;

        $user_row      = User::where('staff_id', $request->userid)->first();
        $user_id       = $user_row->id;
        $department    = '';
        $subdepartment = 0;
        $staff         = 0;
        if (isset($user_id) && $user_id != '') {
            $admin = User::with('roles')->find($user_id);
            $role  = $admin->roles[0];
            $role->load('permissions');
            $permission = json_decode($role->permissions->pluck('title'));
            if (! in_array("complain_all_access", $permission)) {
                $department    = $admin->dapertement_id;
                $subdepartment = $admin->subdapertement_id;
                $staff         = $admin->staff_id;
            }
        }

        try {
            if ($subdepartment > 0 && $staff > 0) {
                $complain_action = ComplainCheck::with('staff')
                    ->with('dapertement')
                    ->with('subdapertement')
                    ->with('complain')
                    ->with('complainstatus')
                // ->orderBy('dapertements.group', 'desc')
                    ->where('complain_id', $complain_id)
                    ->orderBy('start', 'desc')
                    ->get();
            } else {
                $complain_action = ComplainCheck::with('staff')
                    ->with('dapertement')
                    ->with('subdapertement')
                    ->with('complain')
                    ->with('complainstatus')
                // ->orderBy('dapertements.group', 'desc')
                    ->where('complain_id', $complain_id)
                    ->orderBy('start', 'desc')
                    ->get();
            }
            return response()->json([
                'message' => 'Data Complain',
                'data'    => $complain_action,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal Mengambil data',
                'data'    => $ex,
            ]);
        }
    }

    public function checkdetail(Request $request)
    {
        try {

            $complaincheck = ComplainCheck::with('complain')
                ->with('staff')
                ->with('dapertement')
                ->where('id', $request->id)
                ->first();

            return response()->json([
                'message' => 'success',
                'data'    => $complaincheck,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'failed',
                'data'    => $ex,
            ]);
        }
    }

    public function checkupdate(Request $request)
    {
        $img_path   = "/images/complaincheck";
        $video_path = "/videos/complaincheck";
        // //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath = base_path() . '/public';

        //complaincheck
        $complaincheck = ComplainCheck::where('id', $request->complaincheck_id)->with('complain')->with('staff')->first();

        //old img
        $images_old = [];
        foreach (json_decode($complaincheck->image, true) as $key => $image) {
            $images_old[] = $image;
        }
        $deletePath = $request->delete_images;
        $images_fil = $images_old;
        //unlink img deleted
        if ($request->delete_images) {
            foreach ($request->delete_images as $key => $delimage) {
                $file_path = $basepath . $delimage;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            //deleted img
            $filtered = array_filter($images_old, function ($item) use ($deletePath) {
                return ! in_array($item, $deletePath);
            });
            $images_fil = [];
            foreach ($filtered as $key => $image) {
                $images_fil[] = $image;
            }
        }

        //new img
        $images_new = [];
        if ($request->file('image')) {
            foreach ($request->file('image') as $key => $image) {
                $file_extImage = $image->extension();
                $nameImage     = str_replace(" ", "-", strtolower($complaincheck->complain->code)) . 'ACT_' . date('YmdHis') . '_' . mt_rand(1000, 9999);
                $img_name      = $img_path . "/" . $nameImage . "." . $file_extImage;
                $imgFile       = Image::make($image->getRealPath());
                $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $request->lat . ' lng : ' . $request->lng, 10, 10, function ($font) {
                    $font->size(14);
                    $font->color('#000000');
                    $font->valign('top');
                })->save($basepath . '/' . $img_name);
                $images_new[] = $img_name;
            }
        }
        //merge img
        $images = array_merge($images_fil, $images_new);

        // video
        if ($request->file('video')) {
            //unlink old
            $file_path = $basepath . $request->video;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            //up new
            $resource = $request->file('video');
            $video    = $video_path . "/" . strtolower($complaincheck->complain->code) . 'ACT-' . $request->code . '.mp4';
            $resource->move($basepath . $video_path, $video);
        } else {
            $video = $complaincheck->video;
        }

        //up data
        $user_id      = Auth::user()->id;
        $statusAction = $request->status;
        $dateNow      = date('Y-m-d H:i:s');
        //check if current status is close
        $date_end = $dateNow;
        if ($complaincheck->status == 'close') {
            $date_end = $complaincheck->end;
        }
        $data = [
            'description'        => $request->description,
            'memo'               => $request->memo,
            'dapertement_id'     => $request->dapertement_id,
            'complain_id'        => $request->complain_id,
            'start'              => $dateNow,
            'subdapertement_id'  => $request->subdapertement_id,
            'staff_id'           => $request->staff_id,
            'image'              => str_replace("\/", "/", json_encode($images)),
            'video'              => $video,
            'complain_status_id' => $request->complain_status_id,
        ];
        try {
            $complaincheck->update($data);
            return response()->json([
                'message' => 'Tindakan Terupdate',
                'data'    => $complaincheck,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => $ex,
                'data'    => '',
            ]);
        }
    }

    public function actionDestroy(Request $request)
    {
        $action = ComplainAction::find($request->action_id);
        // dd($complain);
        try {
            //unlink img
            $basepath = base_path() . '/public';
            if (trim($action->image) != '') {
                foreach (json_decode($action->image, true) as $key => $complain_image) {
                    $img     = $complain_image;
                    $img     = str_replace('"', '', $img);
                    $img     = str_replace('[', '', $img);
                    $img     = str_replace(']', '', $img);
                    $img_arr = explode(",", $img);
                    foreach ($img_arr as $img_name) {
                        $file_path = $basepath . $img_name;
                        if (trim($img_name) != '' && file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                }
            }
            //unlink video
            $file_path = $basepath . $action->video;
            if (trim($action->video) != '' && trim(file_exists($file_path))) {
                unlink($file_path);
            }
            $action->delete();
            return response()->json([
                'message' => 'Data Berhasil Di Hapus',
                'data'    => $action,
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Data Masih Terkait dengan data yang lain',
                'data'    => $e,
            ]);
        }
    }

    public function actiondetail(Request $request)
    {
        try {

            $complainaction = ComplainAction::with('complain')
                ->with('staff')
                ->with('dapertement')
                ->where('id', $request->id)
                ->first();

            return response()->json([
                'message' => 'success',
                'data'    => $complainaction,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'failed',
                'data'    => $ex,
            ]);
        }
    }

    public function detailComplain(Request $request)
    {
        try {

            $complain = Complain::with('department')
                ->with('action')
                ->where('complains.id', $request->id)
                ->first();

            return response()->json([
                'message' => 'success',
                'data'    => $complain,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'failed',
                'data'    => $ex,
            ]);
        }
    }

    public function actionstore(Request $request)
    {
        //complain
        $complain = Complain::where('id', $request->complain_id)->first();
        //user
        $user_row      = User::where('staff_id', $request->user_id)->first();
        $user_id       = $user_row->id;
        $department    = '';
        $subdepartment = 0;
        $staff         = 0;
        if (isset($user_id) && $user_id != '') {
            $admin = User::with('roles')->find($user_id);
            $role  = $admin->roles[0];
            $role->load('permissions');
            $permission = json_decode($role->permissions->pluck('title'));
            if (! in_array("complain_all_access", $permission)) {
                $department    = $admin->dapertement_id;
                $subdepartment = $admin->subdapertement_id;
                $staff         = $admin->staff_id;
            }
        }

        $img_path   = "/images/complainaction";
        $video_path = "/videos/complainaction";
        // //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath      = base_path() . '/public';
        $dataImageName = [];
        // upload image
        if ($request->file('images')) {
            foreach ($request->file('images') as $key => $image) {
                $nameImage     = strtolower($complain->code);
                $file_extImage = $image->extension();
                $nameImage     = str_replace(" ", "-", $nameImage);
                $img_name      = $img_path . "/" . $nameImage . "-" . $complain->code . $key . "." . $file_extImage;
                $image         = $image;
                $imgFile       = Image::make($image->getRealPath());
                // dd($imgFile->insert($basepath . "/images/Logo.png", 'bottom-right', 10, 10));
                $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $request->lat . ' lng : ' . $request->lng, 10, 10, function ($font) {
                    // $font->file(str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path()) . '/font/Titania-Regular.ttf');
                    $font->size(14);
                    $font->color('#000000');
                    $font->valign('top');
                })->save($basepath . '/' . $img_name);
                $dataImageName[] = $img_name;
            }
        }
        // video
        if ($request->file('video')) {
            $video_path = "/videos/complainaction";
            $resource   = $request->file('video');
            $video_name = $video_path . "/" . strtolower($complain->code) . '-' . $complain->code . '.mp4';
            $resource->move($basepath . $video_path, $video_name);
        } else {
            $video_name = "";
        }

        $dateNow = date('Y-m-d H:i:s');

        $data = [
            'description'        => $request->description,
            'memo'               => $request->memo,
            'status'             => 'pending',
            'dapertement_id'     => $department,
            'complain_id'        => $request->complain_id,
            'start'              => $dateNow,
            'subdapertement_id'  => $subdepartment,
            'staff_id'           => $request->user_id,
            'image'              => str_replace("\/", "/", json_encode($dataImageName)),
            'video'              => $video_name,
            'complain_status_id' => $request->complain_status_id,
        ];

        try {
            $action = ComplainAction::create($data);
            return response()->json([
                'message' => 'Tindakan Terkirim',
                'data'    => $action,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => $ex,
                'data'    => '',
            ]);
        }
    }

    public function actionlist(Request $request)
    {
        $complain_id = $request->complain_id;

        $user_row      = User::where('staff_id', $request->userid)->first();
        $user_id       = $user_row->id;
        $department    = '';
        $subdepartment = 0;
        $staff         = 0;
        if (isset($user_id) && $user_id != '') {
            $admin = User::with('roles')->find($user_id);
            $role  = $admin->roles[0];
            $role->load('permissions');
            $permission = json_decode($role->permissions->pluck('title'));
            if (! in_array("complain_all_access", $permission)) {
                $department    = $admin->dapertement_id;
                $subdepartment = $admin->subdapertement_id;
                $staff         = $admin->staff_id;
            }
        }

        try {
            if ($subdepartment > 0 && $staff > 0) {
                $complain_action = ComplainAction::with('staff')
                    ->with('dapertement')
                    ->with('subdapertement')
                    ->with('complain')
                    ->with('complainstatus')
                // ->orderBy('dapertements.group', 'desc')
                    ->where('complain_id', $complain_id)
                    ->orderBy('start', 'desc')
                    ->get();
            } else {
                $complain_action = ComplainAction::with('staff')
                    ->with('dapertement')
                    ->with('subdapertement')
                    ->with('complain')
                    ->with('complainstatus')
                // ->orderBy('dapertements.group', 'desc')
                    ->where('complain_id', $complain_id)
                    ->orderBy('start', 'desc')
                    ->get();
            }
            return response()->json([
                'message' => 'Data Complain',
                'data'    => $complain_action,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal Mengambil data',
                'data'    => $ex,
            ]);
        }
    }

    public function areas()
    {
        return response()->json(CtmWilayah::select('*')->get());
    }

    public function customer($code)
    {
        //return response()->json(CustomerApi::select('*')->where('nomorrekening', $code)->get());
        return response()->json(
            CustomerApi::select('tblpelanggan.*', 'map_koordinatpelanggan.lat as lat', 'map_koordinatpelanggan.lng as lng')
                ->join('map_koordinatpelanggan', 'tblpelanggan.nomorrekening', '=', 'map_koordinatpelanggan.nomorrekening') // adjust column names as needed
                ->where('tblpelanggan.nomorrekening', $code)
                ->get()
        );

    }

    public function complainstatus()
    {
        return response()->json(ComplainStatus::select('*')->get());
    }

    public function list(Request $request)
    {
        try {
            $complains = Complain::FilterComplainStatus()
                ->FilterStatus()
                ->FilterArea($request->search)
                ->when(isset($request->user_id) && $request->user_id > 0, function ($query) use ($request) {
                    return $query->FilterUser($request->user_id);
                }, function ($query) use ($request) {
                    return $query->FilterPbk($request->pbk);
                })
                ->with('complainstatus')
                ->with('department')
                ->with('category')
                ->with('users')
                ->with('areas')
                ->orderBy('created_at', 'DESC')
            //->orderBy(DB::raw("FIELD(complains.status , \"pending\", \"active\", \"close\" )"))
                ->get();

            return response()->json([
                'message' => 'success',
                'data'    => $complains,
                'page'    => $request->page,
                'seacrh'  => $request->search,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'failed',
                'data'    => $ex,
            ]);
        }
    }

    public function destroy(Request $request)
    {
        $complainaction = ComplainAction::where('complain_id', $request->id)->first();
        if (! $complainaction) {
            try {
                //unlink img
                $basepath = base_path() . '/public';
                $complain = Complain::where('id', $request->id)->first();
                if (trim($complain->image) != '') {
                    foreach (json_decode($complain->image, true) as $key => $complain_image) {
                        $img     = $complain_image;
                        $img     = str_replace('"', '', $img);
                        $img     = str_replace('[', '', $img);
                        $img     = str_replace(']', '', $img);
                        $img_arr = explode(",", $img);
                        foreach ($img_arr as $img_name) {
                            $file_path = $basepath . $img_name;
                            if (trim($img_name) != '' && file_exists($file_path)) {
                                unlink($file_path);
                            }
                        }
                    }
                }
                //unlink video
                $file_path = $basepath . $complain->video;
                if (trim($complain->video) != '' && trim(file_exists($file_path))) {
                    unlink($file_path);
                }

                $complain->delete();
                return response()->json([
                    'message' => 'Data Berhasil Di Hapus',
                    'data'    => $complain,
                ]);
            } catch (QueryException $e) {
                return response()->json([
                    'message' => 'Data Masih Terkait dengan data yang lain',
                    'data'    => $e,
                ]);
            }
        } else {
            return response()->json([
                'message' => 'Data Masih Terkait dengan data yang lain',
                'data'    => [],
            ]);
        }
    }

    public function index(Request $request)
    {
        $workPermit = Absence::where('user_id', $request->id)->where('register', $request->date)->get();
        $absenOut   = Absence::where('user_id', $request->id)->where('absen_category_id', $request->absen_category_id)->get();
        $wP         = '0';
        $aO         = '0';
        if (count($workPermit) > 0) {
            $wP = '0';
        } else {
            $wP = '1';
        }
        if (count($absenOut) > 0) {
            $aO = '1';
        } else {
            $wP = '0';
        }
        return response()->json([
            'message'    => 'Pengajuan Terkirim',
            'absenOut'   => $aO,
            'workPermit' => $wP,
        ]);
    }

    public function store(Request $request)
    {
        $last_code  = $this->get_last_code('complain');
        $code       = acc_code_generate($last_code, 8, 3);
        $img_path   = "/images/complainservice";
        $video_path = "/videos/complainservice";
        $basepath   = base_path() . '/public';
        // upload image
        $dataImageName = [];
        if ($request->file('images')) {
            foreach ($request->file('images') as $key => $image) {
                $nameImage     = strtolower($code);
                $file_extImage = $image->extension();
                $nameImage     = str_replace(" ", "-", $nameImage);
                $img_name      = $img_path . "/" . $nameImage . "-" . $code . $key . "." . $file_extImage;
                $image         = $image;
                $imgFile       = Image::make($image->getRealPath());
                $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $request->lat . ' lng : ' . $request->lng, 10, 10, function ($font) {
                    $font->size(14);
                    $font->color('#000000');
                    $font->valign('top');
                })->save($basepath . '/' . $img_name);
                $dataImageName[] = $img_name;
            }
        }
        // video
        if ($request->file('video')) {
            $video_path = "/videos/complainservice";
            $resource   = $request->file('video');
            $video_name = $video_path . "/" . strtolower($code) . '-' . $code . '.mp4';
            $resource->move($basepath . $video_path, $video_name);
        } else {
            $video_name = "";
        }
        // data
        $user_id        = 0;
        $dapertement_id = 2;
        if($request->user_id > 0){
            $user_row = User::where('staff_id', $request->user_id)->first();
            $user_id        = $user_row->id;
            //$dapertement_id = $user_row ? $user_row->dapertement_id : 1;
            }        
        
        if (! $dapertement_id) {
            $dapertement_id = 2;
        }
        //set SPK
        $arr['dapertement_id'] = $dapertement_id;
        $arr['month']          = date("m");
        $arr['year']           = date("Y");
        $last_spk              = $this->get_last_code('spk-complain', $arr);
        $spk                   = acc_code_generate($last_spk, 21, 17, 'Y');
        //set status
        $status = 'pending';
        if ($request->complain_status_id == 3 || $request->complain_status_id == 4) {
            $status = 'close';
        }
        //set customer_id
        $customer_id = '99900001';
        if (isset($request->customer_id) && trim($request->customer_id) != '') {
            //check if custemer is exist
            $absenceadjs_exist = CustomerApi::where('nomorrekening', '=', trim($request->customer_id))->first();
            if ($absenceadjs_exist) {
                $customer_id = trim($request->customer_id);
            }
        }
        //set data
        $data = [
            'code'                   => $code,
            'title'                  => $request->title,
            'description'            => $request->description,
            'image'                  => str_replace("\/", "/", json_encode($dataImageName)),
            'video'                  => $video_name,
            'customer_id'            => $customer_id,
            'dapertement_id'         => $dapertement_id,
            'spk'                    => $spk,
            'area'                   => $request->area,
            'address'                => $request->address,
            'user_id'                => $user_id,
            'complain_status_id'     => $request->complain_status_id,
            'customer_name'          => $request->customer_name,
            'dapertement_receive_id' => $dapertement_id,
            'lat'                    => $request->lat,
            'lng'                    => $request->lng,
            'status'                 => $status,
            'lat_loc'                => $request->lat_loc ? $request->lat_loc : '',
            'lng_loc'                => $request->lng_loc ? $request->lng_loc : '',
            'pbk_id'                 => $request->pbk_id?$request->pbk_id:0,
        ];
        try {
            $complain = Complain::create($data);
            //check if title == 'air mati' directly to ticket
            if ($request->title == 'air mati') {
                $data = array_merge($data, [
                    'complain_id' => $complain->id,
                    'category_id' => 111,
                ]);
                $newRequest = Request::create('/dummy-url', 'POST', $data);
                app()->call([$this, 'ticketStore'], ['request' => $newRequest]);
            }

            return response()->json([
                'message' => 'Laporan Terkirim',
                'data'    => $complain,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => $ex,
                'data'    => '',
            ]);
        }
    }

    public function update(Request $request)
    {

        // $last_code = $this->get_last_code('lock_action');
        // $code = acc_code_generate($last_code, 8, 3);
        $dataForm = json_decode($request->form);

        if ($request->file('imageP')) {
            $image         = $request->file('imageP');
            $resourceImage = $image;
            $nameImage     = 'imageP' . date('Y-m-d h:i:s') . '.' . $image->extension();
            $file_extImage = $image->extension();
            $folder_upload = 'images/RequestFile';
            $resourceImage->move($folder_upload, $nameImage);

            // dd($request->file('old_image')->move($folder_upload, $img_name));

            // if ($actionWm->old_image != '') {
            //     foreach (json_decode($actionWm->old_image) as $n) {
            //         if (file_exists($n)) {

            //             unlink($basepath . $n);
            //         }
            //     }
            // }
            $data = [
                'image'              => $nameImage,
                'absence_request_id' => $dataForm->id,
                'type'               => 'approve',
            ];
            $data = AbsenceRequestLogs::create($data);
        }

        if ($request->file('imagePng')) {
            $image         = $request->file('imagePng');
            $resourceImage = $image;
            $nameImage     = 'imagePng' . date('Y-m-d h:i:s') . '.' . $image->extension();
            $file_extImage = $image->extension();
            $folder_upload = 'images/RequestFile';
            $resourceImage->move($folder_upload, $nameImage);

            $data = [
                'image'              => $nameImage,
                'absence_request_id' => $dataForm->id,
                'type'               => 'request',
            ];
            $data = AbsenceRequestLogs::create($data);
        }

        return response()->json([
            'message' => 'Pengajuan Terkirim',
        ]);
    }

    public function history(Request $request)
    {
        $requests = AbsenceRequest::where('staff_id', $request->staff_id)
            ->FilterDate($request->from, $request->to)
            ->orderBy('created_at', 'DESC')
            ->paginate(3, ['*'], 'page', $request->page);
        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'data'    => $requests,
        ]);
    }

    public function imageDelete($id)
    {
        $requests = AbsenceRequestLogs::where('id', $id)->delete();
        return response()->json([
            'message' => 'Bukti Dihapus',
            'id'      => $id,
            'data'    => $requests,
        ]);
    }

    public function getPermissionCat(Request $request)
    {
        $cat = [
            ['id' => 'sick', 'name' => 'sakit', 'checked' => false],
            ['id' => 'other', 'name' => 'Lain-Lain', 'checked' => false],
        ];
        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'data'    => $cat,
        ]);
    }

    public function listFile(Request $request)
    {
        $file = AbsenceRequestLogs::selectRaw('image, id')->where('absence_request_id', $request->id)->get();
        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'data'    => $file,
            '$s'      => $request->id,
        ]);
    }

    // mungkin tidak dipakai
    public function absenceList(Request $request)
    {
        $duty   = Requests::where('category', 'duty')->whereDate('date', '=', date('Y-m-d'))->where('user_id', $request->user_id)->where('status', 'approve')->get();
        $extra  = Requests::where('category', 'extra')->whereDate('date', '=', date('Y-m-d'))->where('user_id', $request->user_id)->where('status', 'approve')->get();
        $permit = Requests::where('category', 'permit')->whereDate('date', '=', date('Y-m-d'))->where('user_id', $request->user_id)->where('status', 'approve')->get();

        return response()->json([
            'message' => 'Succes',
            'duty'    => $duty,
            'extra'   => $extra,
            'permit'  => $permit,
        ]);
    }

    // untuk admin start

    public function menuAdmin(Request $request)
    {
        $user    = User::where('id', $request->id)->first();
        $checker = [];
        $users   = user::with(['roles'])
            ->where('id', $request->id)
            ->first();
        foreach ($users->roles as $data) {
            foreach ($data->permissions as $data2) {
                $checker[] = $data2->title;
            }
        }
        if (in_array('absence_all_access', $checker)) {
            $absence_request_count = AbsenceRequest::selectRaw('COUNT(CASE WHEN category = "visit" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS visit_count')
                ->selectRaw('COUNT(CASE WHEN category = "duty" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS duty_count')
                ->selectRaw('COUNT(CASE WHEN category = "excuse" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS excuse_count')
                ->selectRaw('COUNT(CASE WHEN category = "extra" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS extra_count')
                ->selectRaw('COUNT(CASE WHEN category = "leave" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS leave_count')
                ->selectRaw('COUNT(CASE WHEN category = "geolocation_off" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS geolocation_off_count')
                ->selectRaw('COUNT(CASE WHEN category = "permission" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS permission_count')
                ->selectRaw('COUNT(CASE WHEN category = "location" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS location_count')
                ->selectRaw('COUNT(CASE WHEN category = "forget" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS forget_count')
                ->selectRaw('COUNT(CASE WHEN category = "AdditionalTime" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS AdditionalTime_count')
                ->selectRaw('COUNT(CASE WHEN category = "dispense" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS dispense_count')
                ->first();

            $shift_change = ShiftChange::selectRaw('count(shift_changes.id) as total')
                ->join('shift_planner_staffs as s1', 's1.id', '=', 'shift_changes.shift_id')
                ->join('staffs as st1', 'st1.id', '=', 'shift_changes.staff_id')
                ->join('shift_groups as sh1', 'sh1.id', '=', 's1.shift_group_id')
                ->join('shift_planner_staffs as s2', 's2.id', '=', 'shift_changes.shift_change_id')
                ->join('staffs as st2', 'st2.id', '=', 'shift_changes.staff_change_id')
                ->join('shift_groups as sh2', 'sh2.id', '=', 's2.shift_group_id')
                ->whereDate('shift_changes.created_at', '>=', date('Y-m-d'))
                ->whereDate('s1.start', '>=', date('Y-m-d'))
                ->whereDate('s2.start', '>=', date('Y-m-d'))
                ->where('shift_changes.status', 'pending')
                ->orderBy('shift_changes.created_at', 'ASC')
                ->first();
        } else {
            $absence_request_count = AbsenceRequest::selectRaw('COUNT(CASE WHEN category = "visit" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS visit_count')
                ->selectRaw('COUNT(CASE WHEN category = "duty" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS duty_count')
                ->selectRaw('COUNT(CASE WHEN category = "excuse" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS excuse_count')
                ->selectRaw('COUNT(CASE WHEN category = "extra" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS extra_count')
                ->selectRaw('COUNT(CASE WHEN category = "leave" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS leave_count')
                ->selectRaw('COUNT(CASE WHEN category = "geolocation_off" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS geolocation_off_count')
                ->selectRaw('COUNT(CASE WHEN category = "permission" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS permission_count')
                ->selectRaw('COUNT(CASE WHEN category = "location" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS location_count')
                ->selectRaw('COUNT(CASE WHEN category = "forget" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS forget_count')
                ->selectRaw('COUNT(CASE WHEN category = "AdditionalTime" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS AdditionalTime_count')
                ->selectRaw('COUNT(CASE WHEN category = "dispense" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS dispense_count')
                ->join('staffs', 'staffs.id', '=', 'absence_requests.staff_id')
                ->where('dapertement_id', $user->dapertement_id)
                ->first();

            $shift_change = ShiftChange::selectRaw('count(shift_changes.id) as total')
                ->join('shift_planner_staffs as s1', 's1.id', '=', 'shift_changes.shift_id')
                ->join('staffs as st1', 'st1.id', '=', 'shift_changes.staff_id')
                ->join('shift_groups as sh1', 'sh1.id', '=', 's1.shift_group_id')
                ->join('shift_planner_staffs as s2', 's2.id', '=', 'shift_changes.shift_change_id')
                ->join('staffs as st2', 'st2.id', '=', 'shift_changes.staff_change_id')
                ->join('shift_groups as sh2', 'sh2.id', '=', 's2.shift_group_id')
                ->FilterDapertement($user->dapertement_id)
            // ->whereDate('shift_changes.created_at', '>=', date('Y-m-d'))
                ->whereDate('s1.start', '>=', date('Y-m-d'))
                ->whereDate('s2.start', '>=', date('Y-m-d'))
                ->where('shift_changes.status', 'pending')
                ->orderBy('shift_changes.created_at', 'ASC')
                ->first();
        }

        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'data'    => $absence_request_count->setAttribute('change_shift', $shift_change->total),
            // 'change_shift' =>
        ]);
    }

    public function requestApprove(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        // return response()->json([
        //     'message' => 'Pengajuan Terkirim',
        //     'data' =>  $user,
        // ]);
        // AbsenceRequest::selectRaw('COUNT(CASE WHEN category = "visit" THEN 1 END) AS visit_count')
        //     ->selectRaw('COUNT(CASE WHEN category = "duty" THEN 1 END) AS duty_count')
        //     ->selectRaw('COUNT(CASE WHEN category = "excuse" THEN 1 END) AS excuse_count')
        //     ->selectRaw('COUNT(CASE WHEN category = "extra" THEN 1 END) AS extra_count')
        //     ->selectRaw('COUNT(CASE WHEN category = "leave" THEN 1 END) AS leave_count')
        //     ->selectRaw('COUNT(CASE WHEN category = "geolocaation_off" THEN 1 END) AS geolocaation_off_count')
        //     ->selectRaw('COUNT(CASE WHEN category = "permission" THEN 1 END) AS permission_count');
        $checker = [];
        $users   = user::with(['roles'])
            ->where('id', $request->id)
            ->first();
        foreach ($users->roles as $data) {
            foreach ($data->permissions as $data2) {
                $checker[] = $data2->title;
            }
        }
        if (in_array('absence_all_access', $checker)) {
            $requests = AbsenceRequest::select('absence_requests.*', 'staffs.name as staff_name')->join('staffs', 'absence_requests.staff_id', '=', 'staffs.id')
            // ->FilterDapertement($user->dapertement_id)
                ->FilterDate($request->from, $request->to)
                ->where('category', $request->category)
                ->orderBy('created_at', 'DESC')
                ->paginate(3, ['*'], 'page', $request->page);
            return response()->json([
                'message' => 'Pengajuan Terkirim',
                'data'    => $requests,
            ]);
        } else {
            //$request->category !='geolocation_off' && $request->category !='location'
            if ($request->category != 'geolocation_off') {
                $requests = AbsenceRequest::select('absence_requests.*', 'staffs.name as staff_name')->join('staffs', 'absence_requests.staff_id', '=', 'staffs.id')
                    ->FilterDapertement($user->dapertement_id)
                    ->FilterDate($request->from, $request->to)
                    ->where('category', $request->category)
                    ->orderBy('created_at', 'DESC')
                    ->paginate(3, ['*'], 'page', $request->page);
                return response()->json([
                    'message' => 'Pengajuan Terkirim',
                    'data'    => $requests,
                ]);
            } else {
                $requests = AbsenceRequest::select('absence_requests.*')
                    ->where('category', 'nodata')
                    ->paginate(3, ['*'], 'page', $request->page);
                return response()->json([
                    'message' => 'Pengajuan Terkirim',
                    'data'    => $requests,
                ]);
            }
        }
    }

    public function show(Request $request)
    {

        $requests = AbsenceRequest::selectRaw('absence_requests.*,work_types.id as work_type_id, work_types.type as type, staffs.id as staff_id')
            ->join('staffs', 'absence_requests.staff_id', '=', 'staffs.id')
            ->join('work_types', 'work_types.id', '=', 'staffs.work_type_id')
            ->where('absence_requests.id', $request->id)->first();

        $request_file = AbsenceRequestLogs::where('absence_request_id', $request->id)->get();

        return response()->json([
            'message' => 'Bukti Dihapus',
            'data'    => $requests,
            'data2'   => $request_file,
        ]);
    }

    public function getLocation()
    {
        $data  = [];
        $datas = WorkUnit::get();
        foreach ($datas as $key => $value) {
            # code...
            $data[] = ['id' => $value->id, 'name' => $value->name, 'checked' => false];
        }
        // $data = [
        //     ['id' => 'sick', 'name' => 'sakit', 'checked' => false],
        //     ['id' => 'other', 'name' => 'Lain-Lain', 'checked' => false],
        // ];
        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'data'    => $data,
        ]);
    }

    public function closeLocation(Request $request)
    {
        $d = AbsenceRequest::where('id', $request->id)
            ->update(['status' => 'close']);
        // $data = [
        //     ['id' => 'sick', 'name' => 'sakit', 'checked' => false],
        //     ['id' => 'other', 'name' => 'Lain-Lain', 'checked' => false],
        // ];
        return response()->json([
            'message' => 'Pindah lokasi ditutup',
        ]);
    }
}
