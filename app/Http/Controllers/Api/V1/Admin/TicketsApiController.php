<?php

namespace App\Http\Controllers\api\v1\admin;

use App\Action;
use App\CustomerApi;
use App\CustomerMaps;
use App\Http\Controllers\Controller;
use App\Subdapertement;
use App\TicketApi;
use App\Ticket_Image;
use App\Traits\TraitModel;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OneSignal;
use App\Traits\WablasTrait;
use App\wa_history;
use Illuminate\Support\Facades\DB;
use App\StaffApi;
use App\WaTemplate;
use Image;

class TicketsApiController extends Controller
{
    use TraitModel;
    use WablasTrait;

    public function tickets(Request $request)
    {
        $department = '';
        $subdepartment = 0;
        $staff = 0;
        if (isset($request->userid) && $request->userid != '') {
            $admin = User::with('roles')->find($request->userid);
            $role = $admin->roles[0];
            $role->load('permissions');
            $permission = json_decode($role->permissions->pluck('title'));
            if (!in_array("ticket_all_access", $permission)) {
                $department = $admin->dapertement_id;
                $subdepartment = $admin->subdapertement_id;
                $staff = $admin->staff_id;
            }
        }
        try {

            if ($subdepartment == 0) {
                $ticket = TicketApi::FilterStatus($request->status)
                    ->FilterSbg($request->search)
                    ->FilterDepartment($department)
                    ->orderBy(DB::raw("FIELD(tickets.status ,\"pending\", \"active\", \"close\" )"))
                    ->orderBy('id', 'DESC')
                    ->with('department')
                    ->with('customer')
                    ->with('category')
                    ->with('ticket_image')
                    ->with('action')
                    ->paginate(10, ['*'], 'page', $request->page);
            } else if ($subdepartment > 0 && $staff > 0) {
                $ticket = TicketApi::selectRaw('DISTINCT tickets.*')
                    ->join('actions', function ($join) use ($subdepartment) {
                        $join->on('actions.ticket_id', '=', 'tickets.id')
                            ->where('actions.subdapertement_id', '=', $subdepartment);
                    })
                    ->join('action_staff', function ($join) use ($staff) {
                        $join->on('action_staff.action_id', '=', 'actions.id')
                            ->where('action_staff.staff_id', '=', $staff);
                    })
                    ->FilterStatus($request->status)
                    ->FilterSbg($request->search)
                    ->with('department')
                    ->with('customer')
                    ->with('category')
                    ->with('ticket_image')
                    ->with('action')
                    ->orderBy(DB::raw("FIELD(tickets.status ,\"pending\", \"active\", \"close\" )"))
                    ->orderBy('created_at', 'DESC')
                    ->paginate(10, ['*'], 'page', $request->page);
            } else {
                $ticket = TicketApi::selectRaw('DISTINCT tickets.*')
                    ->join('actions', function ($join) use ($subdepartment) {
                        $join->on('actions.ticket_id', '=', 'tickets.id')
                            ->where('actions.subdapertement_id', '=', $subdepartment);
                    })
                    ->FilterStatus($request->status)
                    ->FilterSbg($request->search)
                    ->with('department')
                    ->with('customer')
                    ->with('category')
                    ->with('ticket_image')
                    ->with('action')
                    ->where('tickets.dapertement_id', $department)
                    ->orderBy(DB::raw("FIELD(tickets.status ,\"pending\", \"active\", \"close\" )"))
                    ->orderBy('created_at', 'DESC')
                    ->paginate(10, ['*'], 'page', $request->page);
            }

            return response()->json([
                'message' => 'success',
                'data' => $ticket,
                'page' => $request->page,
                'seacrh' => $request->search,
                'department' => $department,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'failed',
                'data' => $ex,
            ]);
        }
    }

    public function index()
    {
        // ini_set("memory_limit", -1);
        // set_time_limit(0);
        try {
            $ticket = TicketApi::orderBy('id', 'DESC')->with('department')->with('customer')->with('category')->with('ticket_image')->limit(100)->get();
            return response()->json([
                'message' => 'Data Ticket',
                'data' => $ticket,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal Mengambil data',
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $last_code = $this->get_last_code('ticket');

        $code = acc_code_generate($last_code, 8, 3);
        $img_path = "/images/complaint";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath  = base_path().'/public';
        $dataForm = json_decode($request->form);
        $responseImage = '';

        $customer_code = CustomerApi::WhereMaps('id', $dataForm->customer_id)->first();

        if (!$customer_code) {
            return response()->json([
                'message' => 'Code Pelanggan tidak ditemukan',
            ]);
        }

        $dataQtyImage = json_decode($request->qtyImage);
        for ($i = 1; $i <= $dataQtyImage; $i++) {
            if ($request->file('image' . $i)) {
                $image = $request->file('image' . $i);
                $nameImage = strtolower($code);
                $file_extImage = $request->file('image' . $i)->extension();
                $nameImage = str_replace(" ", "-", $nameImage);

                $img_name = $img_path . "/" . $nameImage . "-" . $dataForm->customer_id . $i . "." . $file_extImage;


                // $nameImage = strtolower($action->id);
                // $file_extImage = $image->extension();
                // $nameImage = str_replace(" ", "-", $nameImage);
                // $img_name = $img_path . "/" . $nameImage . "-" . $dataForm->action_id . $i . "." . $file_extImage;
                // $image = $image;

                $imgFile = Image::make($image->getRealPath())->orientate();;

                // dd($imgFile->insert($basepath . "/images/Logo.png", 'bottom-right', 10, 10));
                $lt = $dataForm->lat ? $dataForm->lat : '';
                $lng = $dataForm->lng ? $dataForm->lng : '';
                // $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $dataForm->lat . ' lng : ' . $dataForm->lng, 10, 10, function ($font) {
                $imgFile->text('' . Date('Y-m-d H:i:s'), 10, 10, function ($font) {
                    // $font->file(str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path()) . '/font/Titania-Regular.ttf');
                    $font->size(14);
                    $font->color('#000000');
                    $font->valign('top');
                })->save($basepath . $img_name);
                // $resourceImage->move($basepath . $img_path, $img_name);

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

        $video_name = '';
        if ($request->file('video')) {

            $video_path = "/videos/complaint";
            $resource = $request->file('video');
            // $filename = $resource->getClientOriginalName();
            // $file_extVideo = $request->file('video')->extension();
            $video_name = $video_path . "/" . strtolower($code) . '-' . $dataForm->customer_id . '.mp4';

            $resource->move($basepath . $video_path, $video_name);
        }

        //def subdap
        $dateNow = date('Y-m-d H:i:s');
        $subdapertement_def = Subdapertement::where('def', '1')->first();
        $dapertement_def_id = $subdapertement_def->dapertement_id;
        $subdapertement_def_id = $subdapertement_def->id;
        if (!isset($dataForm->dapertement_id) || $dataForm->dapertement_id == '' || $dataForm->dapertement_id <= 0) {
            $dapertement_id = $dapertement_def_id;
        } else {
            $dapertement_id = $dataForm->dapertement_id;
        }

        //set SPK
        $arr['dapertement_id'] = $dapertement_id;
        $arr['month'] = date("m");
        $arr['year'] = date("Y");
        $last_spk = $this->get_last_code('spk-ticket', $arr);
        $spk = acc_code_generate($last_spk, 21, 17, 'Y');

        //get lat lng customer
        $customermaps = CustomerMaps::where('nomorrekening', $dataForm->customer_id)->first();
        if (!empty($customermaps)) {
            if (!empty($customermaps->lat)) {
                $dataForm->lat = $customermaps->lat;
            }
            if (!empty($customermaps->lng)) {
                $dataForm->lng = $customermaps->lng;
            }
        }
        //set address
        if (!isset($dataForm->address) || $dataForm->address == '') {
            $address_value = '';
        } else {
            $address_value = $dataForm->address;
        }
        //set data
        $data = array(
            'code' => $code,
            'title' => $dataForm->title,
            'category_id' => $dataForm->category_id,
            'description' => $dataForm->description,
            'image' => '',
            'video' => $video_name,
            'customer_id' => $dataForm->customer_id,
            'lat' => $dataForm->lat,
            'lng' => $dataForm->lng,
            'dapertement_id' => $dapertement_id,
            'spk' => $spk,
            'dapertement_receive_id' => $dapertement_id,
            'address' => $address_value,
        );

        if ($dapertement_def_id != $dataForm->dapertement_id) {
            $data['delegated_at'] = $dateNow = date('Y-m-d H:i:s');
        }

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
                $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->description;
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
                    'updated_at' => date('Y-m-d h:i:sa')
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
                $message = 'Humas: Keluhan Baru Diterima : ' . $dataForm->description;
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
                    'updated_at' => date('Y-m-d h:i:sa')
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

            //send notif to departement terkait
            $admin_arr = User::where('dapertement_id', $dapertement_id)
                ->where('subdapertement_id', 0)
                ->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                $message = 'Bagian: Keluhan Baru Diterima : ' . $dataForm->description;
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
                    'updated_at' => date('Y-m-d h:i:sa')
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

            return response()->json([
                'message' => 'Keluhan Diterima',
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal',
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(Request $request, TicketApi $ticket)
    {

        $rules = array(
            // 'email' => 'email|unique:customers,email',
            // 'code' => 'unique:customers,code',
            'title' => 'required',
            'category_id' => 'required',
            'description' => 'required',
        );

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->messages();
            $errors = $messages->all();
            return response()->json([
                'message' => $errors,
                'data' => $request->all(),
            ]);
        }

        $data = $request->all();
        $dateNow = date('Y-m-d H:i:s');
        //if dapertement_id is differ with prev
        if ($ticket->dapertement_id != $request->dapertement_id) {
            //set SPK
            $arr['dapertement_id'] = $request->dapertement_id;
            $created_at = date_create($ticket->created_at);
            $arr['month'] = date_format($created_at, "m");
            $arr['year'] = date_format($created_at, "Y");
            $last_spk = $this->get_last_code('spk-ticket', $arr);
            $spk = acc_code_generate($last_spk, 21, 17, 'Y');
            //merge data
            $data = array_merge($data, ['spk' => $spk, 'delegated_at' => $dateNow]);
        }

        $ticket->update($data);

        //send notif to departement terkait
        $admin_arr = User::where('dapertement_id', $request->dapertement_id)->where('subdapertement_id', 0)->get();
        foreach ($admin_arr as $key => $admin) {
            $id_onesignal = $admin->_id_onesignal;
            $message = 'Bagian: Keluhan Baru Dideligasikan : ' . $request->description;
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
                'updated_at' => date('Y-m-d h:i:sa')
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

        return response()->json([
            'message' => 'Data Ticket update Success',
            'data' => $ticket,
        ]);
    }

    public function close(Request $request)
    {
        //update ticket status
        $ticket = TicketApi::find($request->id);
        $ticket->status = 'close';
        $ticket->save();
        //if close send notif to user
        $customer = CustomerApi::find($ticket->customer_id);
        $id_onesignal = $customer->_id_onesignal;
        $waTemplate = WaTemplate::where('id', 49)->first();
        $message = $waTemplate->message;
        // $message = 'Customer: Keluahan Ditutup Karena Sudah Ada Keluhan yang Sama/Terkait Sebelumnya  : ' . $ticket->code;
        // //wa notif                
        $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
        $wa_data_group = [];
        //get phone user
        $phone_no = $customer->phone;

        //  pesan baru start
        $waTemplate = WaTemplate::where('id', 49)->first();

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

        $wa_data = [
            'phone' => $this->gantiFormat($phone_no),
            'customer_id' => null,
            'message' => $message,
            'template_id' => '',
            'status' => 'gagal',
            'ref_id' => $wa_code,
            'created_at' => date('Y-m-d h:i:sa'),
            'updated_at' => date('Y-m-d h:i:sa')
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

    public function test($id)
    {
        $data = "";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin", \base_path());
        $basepath  = base_path().'/public';
        $ticket_images = Ticket_Image::where('ticket_id', $id)->get();
        foreach ($ticket_images as $ticket_image) {
            $img = $ticket_image->image;
            $img = str_replace('"', '', $img);
            $img = str_replace('[', '', $img);
            $img = str_replace(']', '', $img);
            $img_arr = explode(",", $img);
            foreach ($img_arr as $img_name) {
                $file_path = $basepath . $img_name;
                $data .= $file_path;
                //unlink($file_path);
            }
        }
        return response()->json([
            'message' => 'Data Berhasil Di Hapus',
            'data' => $data,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(TicketApi $ticket)
    {
        try {
            $action = Action::where('ticket_id', '=', $ticket->id)->first();
            if ($action === null) {

                //unlink
                //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin", \base_path());
                $basepath  = base_path().'/public';
                $ticket_images = Ticket_Image::where('ticket_id', $ticket->id)->get();
                foreach ($ticket_images as $ticket_image) {
                    $img = $ticket_image->image;
                    $img = str_replace('"', '', $img);
                    $img = str_replace('[', '', $img);
                    $img = str_replace(']', '', $img);
                    $img_arr = explode(",", $img);
                    foreach ($img_arr as $img_name) {
                        $file_path = $basepath . $img_name;
                        if (trim($img_name) != '' && file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                }
                $ticket_image = Ticket_Image::where('ticket_id', $ticket->id)->delete();
                $ticket->delete();

                return response()->json([
                    'message' => 'Data Berhasil Di Hapus',
                    'data' => $ticket_image,
                ]);
                // user doesn't exist
            } else {
                return response()->json([
                    'message' => 'Data Masih Terkait dengan data yang lain',
                    'data' => $action,
                ]);
            }
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Data Masih Terkait dengan data yang lain',
                'data' => $e,
            ]);
        }
    }

    public function detailTicket(Request $request)
    {
        $department = '';
        $subdepartment = 0;
        $staff = 0;
        if (isset($request->userid) && $request->userid != '') {
            $admin = User::with('roles')->find($request->userid);
            $role = $admin->roles[0];
            $role->load('permissions');
            $permission = json_decode($role->permissions->pluck('title'));
            if (!in_array("ticket_all_access", $permission)) {
                $department = $admin->dapertement_id;
                $subdepartment = $admin->subdapertement_id;
                $staff = $admin->staff_id;
            }
        }
        try {

            if ($subdepartment == 0) {
                $ticket = TicketApi::FilterDepartment($department)
                    ->orderBy('id', 'DESC')
                    ->with('department')
                    ->with('customer')
                    ->with('category')
                    ->with('ticket_image')
                    ->with('action')
                    ->where('tickets.id', $request->id)
                    ->first();
            } else if ($subdepartment > 0 && $staff > 0) {
                $ticket = TicketApi::selectRaw('DISTINCT tickets.*')

                    ->with('department')
                    ->with('customer')
                    ->with('category')
                    ->with('ticket_image')
                    ->with('action')
                    ->orderBy('created_at', 'DESC')
                    ->where('tickets.id', $request->id)
                    ->first();
            } else {
                $ticket = TicketApi::selectRaw('DISTINCT tickets.*')
                    ->leftJoin('actions', function ($join) use ($subdepartment) {
                        $join->on('actions.ticket_id', '=', 'tickets.id')
                            ->where('actions.subdapertement_id', '=', $subdepartment);
                    })
                    ->with('department')
                    ->with('customer')
                    ->with('category')
                    ->with('ticket_image')
                    ->with('action')
                    ->orderBy('created_at', 'DESC')
                    ->where('tickets.id', $request->id)
                    ->first();
            }
            if (!empty($ticket->action) && count($ticket->action) > 0) {
                $n = count($ticket->action) - 1;
            } else {
                $n = 0;
            }

            return response()->json([
                'message' => 'success',
                'data' => $ticket,
                'fotokeluhan' => !empty($ticket->ticket_image) && count($ticket->ticket_image) > 0 ? json_decode($ticket->ticket_image[0]->image) : null,
                'fotoalat' => !empty($ticket->action) && count($ticket->action) > 0 ? json_decode($ticket->action[$n]->image_tools) : null,
                'fotosebelum' => !empty($ticket->action) && count($ticket->action) > 0 ? $ticket->action[$n]->image_prework : null,
                'fotopengerjaan' => !empty($ticket->action) && count($ticket->action) > 0 ? json_decode($ticket->action[$n]->image) : null,
                'fotoselesai' => !empty($ticket->action) && count($ticket->action) > 0 ? json_decode($ticket->action[$n]->image_done) : null,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'failed',
                'data' => $ex,
            ]);
        }
    }
}
