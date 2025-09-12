<?php

namespace App\Http\Controllers\Admin;

use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyCustomerRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\TestModel;
use App\Traits\TraitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Yaza\LaravelGoogleDriveStorage\Gdrive;
use App\ShiftPlannerStaffs;
use App\WorkUnit;
use App\AbsenceLog;
use App\Absence;

class TestController extends Controller
{
    use TraitModel;

    public function testabsen(Request $request)
    {
        $absence = AbsenceLog::selectRaw('absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absences.id as absence_id, absence_logs.id as id')
                ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->where('staff_id', $request->staff_id)
                ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                ->where('absence_logs.status', '=', 1)
                ->where('absence_categories.type', '=', 'presence')
                ->orderBy('absence_logs.start_date', 'ASC')
                ->first();

        $pengecekanApaAdaAbsenMasuk = AbsenceLog::selectRaw('absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absences.id as absence_id, absence_logs.id as id')
                ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->where('staff_id', $request->staff_id)
                ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                ->where('absence_logs.status', '=', 0)
                ->where('absence_logs.absence_category_id', '1')
                ->orderBy('absence_logs.start_date', 'DESC')
                ->first();
        
        $coordinat = WorkUnit::join('staffs', 'staffs.work_unit_id', '=', 'work_units.id')
            ->join('work_types', 'staffs.work_type_id', '=', 'work_types.id')
            ->where('staffs.id', $request->staff_id)->first();

        $c = ShiftPlannerStaffs::selectRaw('shift_planner_staffs.id as shift_planner_id, shift_planner_staffs.shift_group_id')
                        ->join('shift_groups', 'shift_planner_staffs.shift_group_id', '=', 'shift_groups.id')
                        ->leftJoin('absence_logs', 'shift_planner_staffs.id', '=', 'absence_logs.shift_planner_id')
                        ->where('shift_planner_staffs.staff_id', '=', $request->staff_id)
                        ->whereDate('shift_planner_staffs.start', '=', date('Y-m-d'))
                        ->where('absence_logs.id', '=', null)
                        ->orderBy('shift_groups.queue', 'ASC')
                        ->get();
        if (count($c) > 0) {
            foreach ($c as $item) {
                $data = [
                        'day_id'         => '2',
                        'shift_group_id' => $item->shift_group_id,
                        'staff_id'       => $request->staff_id,
                        'created_at'     => date('Y-m-d'),
                        ];
                //$absence      = Absence::create($data);
            }
        }
        return $data;
    }

    public function getGcsImage(Request $request)
    {
        $filename = $request->path;
        $disk = Storage::disk('gcs');
        $url = $disk->url($filename);
        //return $url;
        return response("https://storage.googleapis.com/ptab_gcstorage_bucket/test/1728213424_1005.jpg")->header('Content-Type', 'image/jpeg');
    }
    
    public function gcsbucketIndex()
    {
        $fileid='';
        return view('admin.tests.gdrive', compact('fileid'));
    }

    public function gcsbucketStore(Request $request)
    {
        try {
            $file = $request->file('image');
            $file_name = time() . '_' . $file->getClientOriginalName();
            $storeFile = $file->storeAs("test", $file_name, "gcs");
            $disk = Storage::disk('gcs');
            $fetchFile = $disk->url($storeFile);
        } catch(\UnableToWriteFile|UnableToSetVisibility $e) {
            throw_if($this->throwsExceptions(), $e);
        return false;
        }

        return response()->json([
                'data' => $fetchFile,
        ], 201);
    }

    public function getGdriveImage($filename)
    {
        $filename .= ".jpg";
        $contents = collect(Storage::disk('google')->listContents('', false));
        $file = $contents
            ->where('type', '=', 'file')
            ->where('name', '=', $filename)
            //->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
            ->first(); // there can be duplicate file names!
        $fileid = $file['path'];

        $fileImage = Storage::disk('google')->get($fileid);
        return response($fileImage)->header('Content-Type', 'image/jpeg');
    }

    public function OFFgetGdriveImage($path)
    {
        $filename = $path . ".jpg";
        $contents = collect(Storage::disk('google')->listContents('', false));
        $file = $contents
            ->where('type', '=', 'file')
            ->where('name', '=', $filename)
            //->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
            ->first(); // there can be duplicate file names!
        $fileid = $file['path'];

        //get url using id
        $urlid = "https://lh3.google.com/u/0/d/" . $fileid;

        return Response::stream(function () use ($urlid) {
            echo $urlid;
        }, 200, ['Content-type' => 'image/jpeg']);
        // return response()->stream(function() use ($urlid) {
        //     $im = imagecreatefromstring(base64_decode($urlid));
        //     try {
        //         imagejpeg($im);
        //     } finally {
        //         $im && imagedestroy($im);
        //         $im = null;
        //     }
        // }, 200, ['Content-type' => 'image/jpeg']);
        //return response($urlid)->header('Content-Type', 'image/jpeg');
        //return response()->file($urlid);
    }

    public static function getFileInfo($file_path)
    {
        $path = str_replace('\\', '/', $file_path);
        $arr = explode('/', $path);
        $file_name = end($arr);
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);

        return (object) [
            'filename' => $file_name,
            'ext' => $ext,
            'path' => $path,
        ];
    }

    public function gdriveIndex()
    {
        $data = Gdrive::get('gambar/202401/9_2024_01.jpg');

        return response($data->file, 200)
            ->header('Content-Type', $data->ext);
    }

    public function OFFgdriveIndex()
    {
        // $filename = "gambar";
        // $contents = collect(Storage::disk('google')->listContents('', false));
        // $file = $contents
        //     ->where('type', '=', 'dir')
        //     ->where('name', '=', $filename)
        //     //->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
        //     ->first(); // there can be duplicate file names!
        // $fileid = $file['path'];
        // $filename = "202401";
        // $contents = collect(Storage::disk('google')->listContents($fileid, false));
        // $file = $contents
        //     ->where('type', '=', 'dir')
        //     ->where('name', '=', $filename)
        //     //->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
        //     ->first(); // there can be duplicate file names!
        // $fileid = $file['path'];
        // $filename = "test";
        // $contents = collect(Storage::disk('google')->listContents($fileid, false));
        // $file = $contents
        //     ->where('type', '=', 'dir')
        //     ->where('name', '=', $filename)
        //     //->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
        //     ->first(); // there can be duplicate file names!
        // $fileid = $file['path'];

        $file_path = "39478_2023_10.jpg";
        $fileinfo = self::getFileInfo($file_path);
        $rawData = Storage::disk('google')->get($fileinfo->path);
        return $rawData;
        //return view('admin.tests.gdrive', compact('fileid'));
    }

    public function gdriveStore(Request $request)
    {
        if ($request->file('image')) {
            $filename = "image_name_02.jpg";
            Storage::disk('google')->putFileAs('', $request->file('image'), $filename);
            $meta = Storage::disk("google")
                ->getAdapter()
                ->getMetadata($filename);
            //get url using id
            $urlid = Storage::disk('google')->url($meta['path']);
            return $urlid;
        }
    }

    public function getTest()
    {
        $arr['subdapertement_id'] = 32;
        $arr['month'] = date("m");
        $arr['year'] = date("Y");
        $last_scb = $this->get_last_code('scb-lock', $arr);
        $scb = acc_code_generate($last_scb, 16, 12, 'Y');
        return $scb;
    }

    public function index(Request $request)
    {
        abort_unless(\Gate::allows('customer_access'), 403);

        $qry = TestModel::Filter($request)->Order('id', 'desc')->skip(0)->take(10)->get();
        return $qry;
        if ($request->ajax()) {
            //set query
            $qry = TestModel::Filter($request)->Order('id', 'desc');

            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'customer_show';
                $editGate = 'customer_edit';
                $deleteGate = 'customer_delete';
                $crudRoutePart = 'customers';

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });
            $table->editColumn('code', function ($row) {
                return $row->code ? $row->code : "";
            });

            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : "";
            });

            $table->editColumn('email', function ($row) {
                return $row->email ? $row->email : "";
            });

            $table->editColumn('phone', function ($row) {
                return $row->phone ? $row->phone : "";
            });

            $table->editColumn('type', function ($row) {
                return $row->type ? ($row->type == 'public' ? 'Umum' : 'Pelanggan') : "";
            });

            $table->editColumn('address', function ($row) {
                return $row->address ? $row->address : "";
            });

            $table->editColumn('gender', function ($row) {
                return $row->gender ? ($row->gender == 'male' ? 'Laki-laki' : 'Perempuan') : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        //default view
        return view('admin.tests.index');

    }

    public function create()
    {
        $last_code = $this->get_last_code('customer');

        $code = acc_code_generate($last_code, 8, 3);

        abort_unless(\Gate::allows('customer_create'), 403);
        return view('admin.customers.create', compact('code'));
    }

    public function store(StoreCustomerRequest $request)
    {
        abort_unless(\Gate::allows('customer_create'), 403);
        // $customer = Customer::create($request->all());
        $data = [
            'name' => $request->name,
            'code' => $request->code,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'type' => $request->type,
            'gender' => $request->gender,
            'address' => $request->address,
        ];

        $customer = Customer::create($data);

        return redirect()->route('admin.customers.index');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('customer_edit'), 403);
        $customer = Customer::find($id);
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        abort_unless(\Gate::allows('customer_edit'), 403);
        $customer->update($request->all());
        return redirect()->route('admin.customers.index');
    }

    public function destroy(Customer $customer)
    {
        abort_unless(\Gate::allows('customer_delete'), 403);

        $customer->delete();

        return back();
    }

    public function massDestroy(MassDestroyCustomerRequest $request)
    {
        # code...
    }
}
