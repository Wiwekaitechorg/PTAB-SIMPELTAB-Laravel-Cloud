<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use App\Customer;
use App\Dapertement;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Ticket;
use App\Ticket_Image;
use App\Traits\TraitModel;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Subdapertement;

class TicketsController extends Controller
{

    use TraitModel;

    public function index(Request $request)
    {

        abort_unless(\Gate::allows('ticket_access'), 403);

        $departementlist = Dapertement::all();
        $subdepartementlist = array();

        $ticket = Ticket::all();

        $user_id = Auth::check() ? Auth::user()->id : null;

        $department = '';

        $subdepartment = 0;

        $staff = 0;
        $test_val = 0;

        if (isset($user_id) && $user_id != '') {

            $admin = User::with('roles')->find($user_id);

            $role = $admin->roles[0];

            $role->load('permissions');

            $permission = json_decode($role->permissions->pluck('title'));

            if (!in_array("ticket_all_access", $permission)) {

                $department = $admin->dapertement_id;

                $subdepartment = $admin->subdapertement_id;

                $staff = $admin->staff_id;

                $departementlist = Dapertement::where('id', $department)->get();

                // ->get();

            }
        }

        if (Auth::user()->email == "pengamatmeter@ptab-vps-storage.com") {

            $department = $request->departement;
        }

        if ($request->departement != "") {

            $department = $request->departement;
        }

        if ($request->subdepartement != "") {

            $subdepartement = $request->subdepartement;
        } else {
            $subdepartement = "";
        }

        $statusn = ['pending', 'active', 'close'];

        // set query

        if (Auth::user()->email == "pengamatmeter@ptab-vps-storage.com") {

            $test_val = 1;
            $departementlist = Dapertement::all();

            $qry = Ticket::FilterStatus(request()->input('status'))

                ->FilterDate($request->from, $request->to)
                ->FilterDepartment($department)

                ->with('department')

                ->with('customer')

                ->with('category')

                ->with('ticket_image')

                ->with('action')

                ->orderBy(DB::raw("FIELD(tickets.status , \"pending\", \"active\", \"close\" )"))

                ->orderBy('created_at', 'DESC');
        } else {
            $test_val = 2;
            $qry = Ticket::selectRaw('DISTINCT tickets.*')
                ->FilterStatus(request()->input('status'))

                ->FilterDate($request->from, $request->to)

                ->FilterDepartment($department)
                // ->FilterStaff($staff)
                ->FilterSubDepartment($subdepartement)

                ->with('department')

                ->with('customer')

                ->with('category')

                ->with('ticket_image')

                ->with('action')

                ->orderBy(DB::raw("FIELD(tickets.status , \"pending\", \"active\", \"close\" )"))

                ->orderBy('created_at', 'DESC');
        }

        // dd($qry->get());

        if ($request->ajax()) {

            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');

            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {

                $viewGate = 'ticket_show';

                $editGate = 'ticket_edit';

                $actionGate = 'action_access';

                $deleteGate = 'ticket_delete';

                $crudRoutePart = 'tickets';

                $print = true;

                return view('partials.datatablesActions', compact(

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

                return $row->customer->nomorrekening ? $row->customer->nomorrekening : "";
            });

            $table->editColumn('created_at', function ($row) {

                return $row->created_at ? strval($row->created_at) : "";
            });

            $table->editColumn('dapertement', function ($row) {

                return $row->dapertement->name ? $row->dapertement->name : "";
            });

            $table->editColumn('title', function ($row) {

                return $row->title ? $row->title : "";
            });

            $table->editColumn('description', function ($row) {

                return $row->description ? $row->description : "";
            });

            $table->editColumn('address', function ($row) {

                if ($row->address != "") {

                    return $row->address != "" ? $row->address : "";
                } else {

                    return $row->customer->alamat;
                }
            });

            $table->editColumn('status', function ($row) {

                if ($row->print_report_status == "1") {

                    return "close2";
                } else if ($row->status == "pending" && count($row->action) > 0) {

                    return "pending2";
                } else if (Auth::user()->dapertement_id === 1 && $row->status == 'pending' && $row->dapertement_id > 1 && count($row->action) < 1) {

                    return "pending2";
                } else {

                    return $row->status ? $row->status : "pending";
                }
            });

            $table->editColumn('category', function ($row) {

                return $row->category ? $row->category->name : "";
            });

            $table->editColumn('customer', function ($row) {

                return $row->customer ? $row->customer->name : "";
            });

            $table->editColumn('creator', function ($row) {

                return $row->creator ? $row->creator : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();

            return $table->make(true);
        }

        //default view
        echo $test_val;
        if ($department > 0) {
            $subdepartementlist = Subdapertement::where('dapertement_id', $department)->get();
        }
        //return $subdepartementlist;
        return view('admin.tickets.index', compact('departementlist', 'subdepartementlist'));
    }

    public function create()
    {

        $last_code = $this->get_last_code('ticket');

        $code = acc_code_generate($last_code, 8, 3);

        abort_unless(\Gate::allows('ticket_create'), 403);

        $categories = Category::all();

        //$customers = Customer::all();

        return view('admin.tickets.create', compact('categories', 'code'));
    }

    public function store(StoreTicketRequest $request)
    {

        abort_unless(\Gate::allows('ticket_create'), 403);

        $customer_id = Customer::where('nomorrekening', $request->customer_id)->get();

        // dd($customer_id);

        if (count($customer_id) < 1) {

            return back()->withError('No.SBG tidak ada !')->withInput();
        } else {

            $img_path = "/images/complaint";

            $video_path = "/videos/complaint";

            //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
            $basepath  = base_path().'/public';

            // upload image

            if ($request->file('image')) {

                foreach ($request->file('image') as $key => $image) {

                    $resourceImage = $image;

                    $nameImage = strtolower($request->code);

                    $file_extImage = $image->extension();

                    $nameImage = str_replace(" ", "-", $nameImage);

                    $img_name = $img_path . "/" . $nameImage . "-" . $request->customer_id . $key . "." . $file_extImage;

                    $resourceImage->move($basepath . $img_path, $img_name);

                    $dataImageName[] = $img_name;
                }
            }

            // video

            if ($request->file('video')) {

                $video_path = "/videos/complaint";

                $resource = $request->file('video');

                $video_name = $video_path . "/" . strtolower($request->code) . '-' . $request->customer_id . '.mp4';

                $resource->move($basepath . $video_path, $video_name);
            } else {

                $video_name = "";
            }

            // data

            $dapertement_id = Auth::check() ? Auth::user()->dapertement_id : 1;

            //set SPK

            $arr['dapertement_id'] = $dapertement_id;

            $arr['month'] = date("m");

            $arr['year'] = date("Y");

            $last_spk = $this->get_last_code('spk-ticket', $arr);

            $spk = acc_code_generate($last_spk, 21, 17, 'Y');

            //set data

            $data = array(

                'code' => $request->code,

                'title' => $request->title,

                'category_id' => $request->category_id,

                'description' => $request->description,

                'image' => '',

                'video' => $video_name,

                'customer_id' => $request->customer_id,

                'dapertement_id' => $dapertement_id,

                'spk' => $spk,

            );

            try {

                $ticket = Ticket::create($data);

                if ($ticket) {

                    $upload_image = new Ticket_Image;

                    $upload_image->image = str_replace("\/", "/", json_encode($dataImageName));

                    $upload_image->ticket_id = $ticket->id;

                    $upload_image->save();
                }

                return redirect()->route('admin.tickets.index');
            } catch (QueryException $ex) {

                return back()->withErrors($ex);
            }
        }

        // dd(json_encode($dataImageName));

    }

    public function show(Ticket $ticket)
    {

        // dd($ticket->customer);

        $subdapertement = [];

        $staffs = [];

        if (!empty($ticket->action[0])) {

            $subdapertement = $ticket->action[0]->subdapertement;

            $staffs = $ticket->action[0]->staff;
        }

        return view('admin.tickets.show', compact('ticket', 'subdapertement', 'staffs'));
    }

    public function edit(Ticket $ticket)
    {

        abort_unless(\Gate::allows('ticket_edit'), 403);

        $categories = Category::all();

        $user_id = Auth::check() ? Auth::user()->id : null;

        $department = '';

        if (isset($user_id) && $user_id != '') {

            $admin = User::with('roles')->find($user_id);

            $role = $admin->roles[0];

            $role->load('permissions');

            $permission = json_decode($role->permissions->pluck('title'));

            if (!in_array("ticket_all_access", $permission)) {

                $department = $admin->dapertement_id;
            }
        }

        if ($department != '') {

            $dapertements = Dapertement::where('id', $department)->get();
        } else {

            $dapertements = Dapertement::all();
        }

        return view('admin.tickets.edit', compact('ticket', 'categories', 'dapertements'));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {

        // dd($request->all());

        abort_unless(\Gate::allows('ticket_edit'), 403);

        $customer_id = Customer::where('nomorrekening', $request->customer_id)->get();

        // dd($customer_id);

        if (count($customer_id) < 1) {

            return back()->withError('No.SBG tidak ada !')->withInput();
        } else {

            $data = $request->all();

            if ($ticket->dapertement_id != $request->dapertement_id) {

                //set SPK

                $arr['dapertement_id'] = $request->dapertement_id;

                $created_at = date_create($ticket->created_at);

                $arr['month'] = date_format($created_at, "m");

                $arr['year'] = date_format($created_at, "Y");

                $last_spk = $this->get_last_code('spk-ticket', $arr);

                $spk = acc_code_generate($last_spk, 21, 17, 'Y');

                //merge data

                $data = array_merge($data, ['spk' => $spk]);
            }

            $ticket->update($data);

            return redirect()->route('admin.tickets.index');
        }
    }

    public function destroy(Ticket $ticket)
    {

        abort_unless(\Gate::allows('ticket_delete'), 403);

        // dd($ticket);

        try {

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

            return back();
        } catch (QueryException $e) {

            return back()->withErrors(['Mohon hapus dahulu data yang terkait']);
        }
    }

    public function massDestroy()
    {

        # code...

    }

    function print($id)
    {

        $ticket = Ticket::findOrFail($id);

        // $newtime = strtotime($data->created_at);

        // $data->time = date('M d, Y',$newtime);

        return view('admin.tickets.print', compact('ticket'));

        // dd($ticket);

    }

    public function printAction($id)
    {

        $ticket = Ticket::findOrFail($id);

        // $newtime = strtotime($data->created_at);

        // $data->time = date('M d, Y',$newtime);

        return view('admin.tickets.printAction', compact('ticket'));

        // dd($ticket);

    }

    public function printservice($id)
    {

        $ticket = Ticket::with(['customer', 'dapertement', 'action', 'category', 'dapertementReceive'])->findOrFail($id);

        // dd($ticket);

        Ticket::where('id', $id)->update(['print_status' => 1]);

        return view('admin.tickets.printservice', compact('ticket'));
    }

    public function printspk($id)
    {

        $ticket = Ticket::with(['customer', 'dapertement', 'action', 'category', 'dapertementReceive'])->findOrFail($id);

        $subdapertement = [];

        $staffs = [];

        if (!empty($ticket->action[0])) {

            $subdapertement = $ticket->action[0]->subdapertement;

            $staffs = $ticket->action[0]->staff;
        }

        Ticket::where('id', $id)->update(['print_spk_status' => 1]);

        return view('admin.tickets.printspk', compact('ticket', 'subdapertement', 'staffs'));
    }

    public function printReport($id)
    {

        $ticket = Ticket::with(['customer', 'dapertement', 'action', 'category', 'dapertementReceive'])->findOrFail($id);

        Ticket::where('id', $id)->update(['print_report_status' => 1]);

        return view('admin.tickets.printreport', compact('ticket'));
    }
}
