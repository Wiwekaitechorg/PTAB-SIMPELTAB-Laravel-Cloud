<?php
namespace App\Http\Controllers\Admin;

use App\Category;
use App\Complain;
use App\ComplainOpp;
use App\ComplainStatus;
use App\CtmOpp;
use App\CtmPbk;
use App\CtmPelanggan;
use App\CtmWilayah;
use App\Customer;
use App\CustomerMaps;
use App\Dapertement;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComplainRequest;
use App\Http\Requests\UpdateComplainRequest;
use App\StaffApi;
use App\Subdapertement;
use App\TicketApi;
use App\Ticket_Image;
use App\Traits\TraitModel;
use App\Traits\WablasTrait;
use App\User;
use App\wa_history;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Image;
use OneSignal;
use Yajra\DataTables\Facades\DataTables;

class ComplainsController extends Controller
{
    use WablasTrait;
    use TraitModel;

    // editt status tindakan pegawai
    public function complainOppDestroy($operator, $opp_id)
    {
        abort_unless(\Gate::allows('complain_delete'), 403);
        //return $operator.' '.$opp_id;
        $opp = ComplainOpp::where('id', $opp_id)->first();

        $opp->delete();
        return redirect()->route('admin.complain.complainsOpp', ['operator' => $operator]);
    }

    // list operator
    public function complainPbk()
    {
        abort_unless(\Gate::allows('complain_access'), 403);

        $pbks = CtmPbk::orderBy('Name', 'asc')->get();

        // $staffs = $pbk->staff;

        return view('admin.complains.pbk', compact('pbks'));
    }

    // list opp
    public function complainOpp(Request $request)
    {
        abort_unless(\Gate::allows('complain_access'), 403);

        $opps = ComplainOpp::where('operator', $request->operator)->with('pelanggan')->get();

        $operator = $request->operator;

        return view('admin.complains.opp', compact('opps', 'operator'));
    }

    public function complainOppCreate(Request $request)
    {
        abort_unless(\Gate::allows('complain_create'), 403);
        //*
        $opps         = CtmOpp::where('operator', $request->operator)->with('pelanggan')->get();
        $complainopps = ComplainOpp::where('operator', $request->operator)->get();
        $operator     = $request->operator;
        //return

        return view('admin.complains.oppCreate', compact('operator', 'opps', 'complainopps'));

        // dd($action_staffs_list);
        //*/
    }

    public function complainOppStore(Request $request)
    {
        abort_unless(\Gate::allows('complain_create'), 403);

        //set data
        $data = [
            'operator'      => $request->operator,
            'nomorrekening' => $request->nomorrekening,
        ];
        try {
            $ticket = ComplainOpp::create($data);
            return redirect()->route('admin.complain.complainsOpp', ['operator' => $request->operator]);
        } catch (QueryException $ex) {
            return back()->withErrors($ex->getMessage());
        }
    }

    public function ticketStore(Request $request)
    {
        abort_unless(\Gate::allows('ticket_create'), 403);
        $complain = Complain::where('id', $request->complain_id)->first();

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

            return redirect()->route('admin.complains.index');
        } catch (QueryException $ex) {
            return back()->withErrors($ex->getMessage());
            //return $ex->getMessage();
        }
    }

    public function ticketCreate(Request $request)
    {
        $last_code   = $this->get_last_code('ticket');
        $code        = acc_code_generate($last_code, 8, 3);
        $complain    = Complain::where('id', $request->complain_id)->first();
        $custumer    = CtmPelanggan::where('_def', '1')->first();
        $customer_id = $custumer->nomorrekening;

        abort_unless(\Gate::allows('ticket_create'), 403);
        $categories = Category::all();

        return view('admin.complains.ticketCreate', compact('categories', 'code', 'complain', 'customer_id'));
    }

    public function index(Request $request)
    {
        abort_unless(\Gate::allows('complain_access'), 403);
        ini_set('memory_limit', '-1');
        $departementlist    = Dapertement::all();
        $subdepartementlist = [];
        $complain           = Complain::all();
        $user_id            = Auth::check() ? Auth::user()->id : null;
        $department         = '';
        $subdepartment      = 0;
        $staff              = 0;
        $test_val           = 0;
        if (isset($user_id) && $user_id != '') {
            $admin = User::with('roles')->find($user_id);
            $role  = $admin->roles[0];
            $role->load('permissions');
            $permission = json_decode($role->permissions->pluck('title'));
            if (! in_array("complain_all_access", $permission)) {
                // $department      = $admin->dapertement_id;
                // $subdepartment   = $admin->subdapertement_id;
                // $staff           = $admin->staff_id;
                // $departementlist = Dapertement::where('id', $department);
                // ->get();
            }
        }
        $statusn = ['pending', 'active', 'close'];
        // set query
        if (Auth::user()->email == "pengamatmeter@ptab-vps-storage.com") {
            $test_val        = 1;
            $departementlist = Dapertement::all();
            $qry             = Complain::FilterStatus(request()->input('status'))
                ->FilterDate($request->from, $request->to)
                ->filterByGroupUnit($request->areas)
                //->FilterArea($request->areas)
                ->with('department')
                ->with('category')
                ->with('users')
            //->orderBy(DB::raw("FIELD(complains.status , \"pending\", \"active\", \"close\" )"))
                ->orderBy('created_at', 'DESC');
        } else {
            $test_val = 2;
            $qry      = Complain::selectRaw('DISTINCT complains.*')
                ->FilterStatus(request()->input('status'))
                ->FilterDate($request->from, $request->to)
                ->filterByGroupUnit($request->areas)
                //->FilterArea($request->areas)
                ->with('department')
                ->with('category')
                ->with('users')
            //->orderBy(DB::raw("FIELD(complains.status , \"pending\", \"active\", \"close\" )"))
                ->orderBy('created_at', 'DESC');
        }
        //dd($qry->get());
        if ($request->ajax()) {
            $table = Datatables::of($qry);
            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');
            $table->editColumn('actions', function ($row) {
                $viewGate      = 'complain_show';
                $editGate      = 'complain_edit'; //
                $actionGate    = 'action_access';
                $deleteGate    = 'complain_delete';
                $crudRoutePart = 'complains';
                $print         = true;
                return view('partials.datatablesComplainAction', compact(
                    'viewGate',
                    'editGate',
                    'actionGate',
                    'deleteGate',
                    'print',
                    'crudRoutePart',
                    'row'
                ));
            });
            $table->editColumn('code', function ($row) {
                return $row->code ? $row->code : "";
            });
            $table->editColumn('nomorrekening', function ($row) {
                return $row->customer_id;
            });
            $table->editColumn('created_at', function ($row) {
                // return $row->created_at ? strval($row->created_at) : "";
                return $row->created_at ? strval($row->created_at) : "";
            });
            $table->editColumn('dapertement', function ($row) {
                return $row->dapertement ? $row->dapertement->name : "";
            });
            $table->editColumn('title', function ($row) {
                return $row->title ? $row->title : "";
            });
            $table->editColumn('description', function ($row) {
                return $row->description ? $row->description : "";
            });
            $table->editColumn('address', function ($row) {
                return $row->address;
            });
            $table->editColumn('status', function ($row) {
                if ($row->print_report_status == "1" && $row->status == "close") {
                    return "close";
                } else if ($row->status == "pending") {
                    return "pending";
                } else if (Auth::user()->dapertement_id === 1 && $row->status == 'pending' && $row->dapertement_id > 1) {
                    return "pending";
                } else {
                    return $row->status ? $row->status : "pending";
                }
            });
            $table->editColumn('complainstatus', function ($row) {
                return $row->complainstatus ? $row->complainstatus->code : "RED";
            });
            $table->editColumn('area', function ($row) {
                return $row->area;
            });
            $table->editColumn('customer', function ($row) {
                return $row->customer ? $row->customer->name : "";
            });
            $table->editColumn('user_id', function ($row) {
                return $row->users ? $row->users->name : $row->pbk->Name;
                //return $row->area;
            });
            $table->rawColumns(['actions', 'placeholder']);
            $table->addIndexColumn();
            return $table->make(true);
        }
        //default view
        // echo $test_val;
        if ($department > 0) {
            $subdepartementlist = Subdapertement::where('dapertement_id', $department)->get();
        }
        $areas = CtmWilayah::select(
            'group_unit as code',
            DB::raw("CASE
            WHEN group_unit = 1 THEN 'Daerah Kota'
            WHEN group_unit = 2 THEN 'Kerambitan'
            WHEN group_unit = 3 THEN 'Selemadeg'
            WHEN group_unit = 4 THEN 'Penebel'
            WHEN group_unit = 5 THEN 'Baturiti'
            ELSE NamaWilayah
        END as NamaWilayah")
        )
            ->groupBy('group_unit')
            ->orderBy('group_unit')
            ->get();

        //return $subdepartementlist;
        return view('admin.complains.index', compact('departementlist', 'subdepartementlist', 'areas'));
    }

    public function create()
    {
        $last_code = $this->get_last_code('complain');
        $code      = acc_code_generate($last_code, 8, 3);
        // dd($code);
        abort_unless(\Gate::allows('complain_create'), 403);
        $areas          = CtmWilayah::get();
        $complainstatus = ComplainStatus::get();
        return view('admin.complains.create', compact('areas', 'code', 'complainstatus'));
    }

    public function store(StoreComplainRequest $request)
    {
        $last_code = $this->get_last_code('complain');
        $code      = acc_code_generate($last_code, 8, 3);
        abort_unless(\Gate::allows('complain_create'), 403);
        $img_path   = "/images/complainservice";
        $video_path = "/videos/complainservice";
        // //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath = base_path() . '/public';
        // upload image
        if ($request->file('image')) {
            foreach ($request->file('image') as $key => $image) {
                $nameImage     = strtolower($code);
                $file_extImage = $image->extension();
                $nameImage     = str_replace(" ", "-", $nameImage);
                $img_name      = $img_path . "/" . $nameImage . "-" . $code . $key . "." . $file_extImage;
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
            $video_path = "/videos/complainservice";
            $resource   = $request->file('video');
            $video_name = $video_path . "/" . strtolower($code) . '-' . $code . '.mp4';
            $resource->move($basepath . $video_path, $video_name);
        } else {
            $video_name = "";
        }
        // data
        $dapertement_id = Auth::check() ? Auth::user()->dapertement_id : 1;
        $user_id        = Auth::user()->id;
        if (! $dapertement_id) {
            $dapertement_id = 1;
        }
        //set SPK
        $arr['dapertement_id'] = $dapertement_id;
        $arr['month']          = date("m");
        $arr['year']           = date("Y");
        $last_spk              = $this->get_last_code('spk-complain', $arr);
        $spk                   = acc_code_generate($last_spk, 21, 17, 'Y');
        //set data
        $data = [
            'code'               => $code,
            'title'              => $request->title,
            'description'        => $request->description,
            'image'              => str_replace("\/", "/", json_encode($dataImageName)),
            'video'              => $video_name,
            'customer_id'        => $request->customer_id,
            'dapertement_id'     => $dapertement_id,
            'spk'                => $spk,
            'area'               => $request->area,
            'address'            => $request->address,
            'user_id'            => $user_id,
            'complain_status_id' => $request->complain_status_id,
            'customer_name'      => $request->customer_name,
        ];
        try {
            $complain = Complain::create($data);
            return redirect()->route('admin.complains.index');
        } catch (QueryException $ex) {
            return back()->withErrors($ex);
        }
        // dd(json_encode($dataImageName));
    }

    public function show(Complain $complain)
    {
        // dd($complain->customer);
        $subdapertement = [];
        $staffs         = [];
        return view('admin.complains.show', compact('complain', 'subdapertement', 'staffs'));
    }

    public function edit(Complain $complain)
    {
        abort_unless(\Gate::allows('complain_edit'), 403);
        $areas      = CtmWilayah::get();
        $user_id    = Auth::check() ? Auth::user()->id : null;
        $department = '';
        if (isset($user_id) && $user_id != '') {
            $admin = User::with('roles')->find($user_id);
            $role  = $admin->roles[0];
            $role->load('permissions');
            $permission = json_decode($role->permissions->pluck('title'));
            if (! in_array("complain_all_access", $permission)) {
                $department = $admin->dapertement_id;
            }
        }
        if ($department != '') {
            $dapertements = Dapertement::where('id', $department)->get();
        } else {
            $dapertements = Dapertement::all();
        }
        $complainstatus = ComplainStatus::get();

        return view('admin.complains.edit', compact('complain', 'areas', 'dapertements', 'complainstatus'));
    }

    public function update(UpdateComplainRequest $request, Complain $complain)
    {
        abort_unless(\Gate::allows('complain_edit'), 403);
        $img_path   = "/images/complainservice";
        $video_path = "/videos/complainservice";
        // //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath = base_path() . '/public';
        //old img
        $images_old = [];
        foreach (json_decode($complain->image, true) as $key => $image) {
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
                $nameImage     = str_replace(" ", "-", strtolower($request->code)) . '_' . date('YmdHis') . '_' . mt_rand(1000, 9999);
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
            $video    = $video_path . "/" . strtolower($request->code) . '-' . $request->code . '.mp4';
            $resource->move($basepath . $video_path, $video);
        } else {
            $video = $complain->video;
        }

        //up data
        $user_id = Auth::user()->id;
        $data    = [
            'code'               => $request->code,
            'title'              => $request->title,
            'description'        => $request->description,
            'image'              => str_replace("\/", "/", json_encode($images)),
            'video'              => $video,
            'customer_id'        => $request->customer_id,
            'dapertement_id'     => $request->dapertement_id,
            'area'               => $request->area,
            'address'            => $request->address,
            'user_id'            => $user_id,
            'complain_status_id' => $request->complain_status_id,
            'customer_name'      => $request->customer_name,
        ];
        if ($complain->dapertement_id != $request->dapertement_id) {
            //set SPK
            $arr['dapertement_id'] = $request->dapertement_id;
            $created_at            = date_create($complain->created_at);
            $arr['month']          = date_format($created_at, "m");
            $arr['year']           = date_format($created_at, "Y");
            $last_spk              = $this->get_last_code('spk-complain', $arr);
            $spk                   = acc_code_generate($last_spk, 21, 17, 'Y');
            $data                  = array_merge($data, ['spk' => $spk]);
        }
        if ($request->statusupdate != "") {
            $data = array_merge($data, ['status' => $request->statusupdate]);
        }
        // dd($data);
        $complain->update($data);
        return redirect()->route('admin.complains.index');
    }

    public function destroy(Complain $complain)
    {
        abort_unless(\Gate::allows('complain_delete'), 403);
        // dd($complain);

        try {
            //unlink img
            $basepath = base_path() . '/public';
            $complain = Complain::where('id', $complain->id)->first();
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
            return back();
        } catch (QueryException $e) {
            return back()->withErrors(['Mohon hapus dahulu data yang terkait']);
        }
    }

    public function massDestroy()
    {
        # code...
    }

    public function print($id)
    {
        $complain = Complain::findOrFail($id);
        // $newtime = strtotime($data->created_at);
        // $data->time = date('M d, Y',$newtime);
        return view('admin.complains.print', compact('complain'));
        // dd($complain);
    }

    public function printAction($id)
    {
        $complain = Complain::findOrFail($id);
        // $newtime = strtotime($data->created_at);
        // $data->time = date('M d, Y',$newtime);
        return view('admin.complains.printAction', compact('complain'));
        // dd($complain);
    }

    public function printservice($id)
    {
        $complain = Complain::with(['customer', 'dapertement', 'action', 'category', 'dapertementReceive'])->findOrFail($id);
        // dd($complain);
        Complain::where('id', $id)->update(['print_status' => 1]);
        return view('admin.complains.printservice', compact('complain'));
    }

    public function printspk($id)
    {
        $complain       = Complain::with(['customer', 'dapertement', 'action', 'category', 'dapertementReceive'])->findOrFail($id);
        $subdapertement = [];
        $staffs         = [];
        if (! empty($complain->action[0])) {
            $subdapertement = $complain->action[0]->subdapertement;
            $staffs         = $complain->action[0]->staff;
        }
        Complain::where('id', $id)->update(['print_spk_status' => 1]);
        return view('admin.complains.printspk', compact('complain', 'subdapertement', 'staffs'));
    }

    public function printReport($id)
    {
        $complain = Complain::with(['customer', 'dapertement', 'action', 'category', 'dapertementReceive'])->findOrFail($id);
        Complain::where('id', $id)->update(['print_report_status' => 1]);
        return view('admin.complains.printreport', compact('complain'));
    }
}
