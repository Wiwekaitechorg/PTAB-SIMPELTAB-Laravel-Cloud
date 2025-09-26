<?php
namespace App\Http\Controllers\Admin;

use App\CtmPbk;
use App\CtmPelanggan;
use App\CtmWilayah;
use App\Customer;
use App\Exports\CustomersExport;
use App\Exports\OppExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyCustomerRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Imports\CustomerImport;
use App\Traits\TraitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class CustomersController extends Controller
{
    use TraitModel;

    public function oppviewReport()
    {
        abort_unless(\Gate::allows('customer_opp_access'), 403);

        $staff = CtmPbk::all();
        return view('admin.customers.oppreport', compact('staff'));
    }

    public function oppreportExcel(Request $request)
    {

        abort_unless(\Gate::allows('customer_opp_access'), 403);
        ini_set("memory_limit", -1);
        set_time_limit(0);
        $data = [];
        //set query
        $qry = CtmPelanggan::selectRaw('tblpelanggan.*, map_koordinatpelanggan.lat as lat, map_koordinatpelanggan.lng as lng')
            ->join('map_koordinatpelanggan', 'tblpelanggan.nomorrekening', '=', 'map_koordinatpelanggan.nomorrekening')
            ->join('tblopp', 'tblpelanggan.nomorrekening', '=', 'tblopp.nomorrekening')
            ->FilterDate($request->from, $request->to)
            ->FilterStaff($request->staff)
            ->where('tblpelanggan.status', '1')
            ->orderBy('tblpelanggan.nomorrekening', 'ASC')->get();

        //*
        $qry_arr = [];
        foreach ($qry as $qry_ext) {
            //init status
            $status_list = false;
            $path        = "/pdam";

            //*
            //if sudah
            if ($request->status == 'sudah') {
                if (Storage::disk('sftp')->exists($path . $qry_ext->_filegambar) && Storage::disk('sftp')->exists($path . $qry_ext->_filektp) && Storage::disk('sftp')->exists($path . $qry_ext->_filegambar) && trim($qry_ext->_filegambar) != '' && trim($qry_ext->_filektp) != '' && trim($qry_ext->_filewm) != '') {
                    $status_list = true;
                }
                $qry_ext->status = 'Lengkap';
            }
            //if belum
            else if ($request->status == 'belum') {
                if (! Storage::disk('sftp')->exists($path . $qry_ext->_filegambar) || ! Storage::disk('sftp')->exists($path . $qry_ext->_filektp) || ! Storage::disk('sftp')->exists($path . $qry_ext->_filegambar) || trim($qry_ext->_filegambar) == '' || trim($qry_ext->_filektp) == '' || trim($qry_ext->_filewm) == '') {
                    $status_list = true;
                }
                $qry_ext->status = 'Kurang';
            }
            //if semua
            else {
                if (Storage::disk('sftp')->exists($path . $qry_ext->_filegambar) && Storage::disk('sftp')->exists($path . $qry_ext->_filektp) && Storage::disk('sftp')->exists($path . $qry_ext->_filegambar) && trim($qry_ext->_filegambar) != '' && trim($qry_ext->_filektp) != '' && trim($qry_ext->_filewm) != '') {
                    $qry_ext->status = 'Lengkap';
                }else{
                    $qry_ext->status = 'Kurang';
                }
                $status_list = true;
            }

            if ($status_list) {
                $qry_arr[] = $qry_ext;
            }
        }        

        foreach ($qry_arr as $d) {

            if(trim($d->nomorhp=='')){
            $phone_number = $d->telp;
            }else{
                $phone_number = $d->nomorhp;
            }
            
            $data[] = [
                "nomorrekening" => $d->nomorrekening,
                "namapelanggan" => $d->namapelanggan,
                "email"        => $d->_email,
                "alamat"        => $d->alamat,
                "gender"       => $d->_gender == 'male' ? 'Laki-laki' : 'Perempuan',
                "last_update"   => $d->last_update,
                "desctype"     => $d->_desctype,
                "desc"         => $d->_desc,
                "lat"           => $d->lat,
                "lng"           => $d->lng,
                "noktp"         => $d->noktp,
                "nama_sesuai_ktp"         => $d->_namaktp,
                "telp"         => $phone_number,
                "status"        => $d->status,
                "foto_rumah"    => "https://ptab-vps-storage.com/pdam" . $d->_filegambar,
                "foto_ktp"      => "https://ptab-vps-storage.com/pdam" . $d->_filektp,
                "foto_wm"       => "https://ptab-vps-storage.com/pdam" . $d->_filewm,
            ];
        }

        return Excel::download(new OppExport($data), 'opp_customers.xlsx');
    }

    public function editOpp(Request $request)
    {
        abort_unless(\Gate::allows('customeropp_edit'), 403);
        $customer = Customer::WhereMaps('id', $request->nomorrekening)->first();
        return view('admin.customers.oppedit', compact('customer'));
    }

    public function updateOpp(Request $request)
    {
        abort_unless(\Gate::allows('customeropp_edit'), 403);
        $customer          = Customer::where('nomorrekening', $request->code)->first();
        $customer->phone   = $request->phone;
        $customer->noktp   = $request->noktp;
        $customer->_synced = 0;
        $customer->save();

        return redirect()->route('admin.opp.index');
    }

    public function showOpp(Request $request)
    {
        $customer = Customer::find($request->nomorrekening);
        return view('admin.customers.oppshow', compact('customer'));
    }

    public function indexOpp(Request $request)
    {
        ini_set("memory_limit", -1);
        set_time_limit(0);
        abort_unless(\Gate::allows('customer_opp_access'), 403);  

        if ($request->ajax()) {
            //set query
            $qry = CtmPelanggan::selectRaw('tblpelanggan.*, map_koordinatpelanggan.lat as lat, map_koordinatpelanggan.lng as lng')
                ->join('map_koordinatpelanggan', 'tblpelanggan.nomorrekening', '=', 'map_koordinatpelanggan.nomorrekening')
                ->join('tblopp', 'tblpelanggan.nomorrekening', '=', 'tblopp.nomorrekening')
                ->FilterDate($request->from, $request->to)
                ->FilterStaff($request->staff)
                ->orderBy('tblpelanggan.nomorrekening', 'ASC')->get();

            //*->offset(0)->limit(10)
            $qry_arr = [];
            foreach ($qry as $qry_ext) {
                //init status
                $status_list = false;
                $path        = "/pdam";

                //*
                //if sudah
                if ($request->status == 'sudah') {
                    if (Storage::disk('sftp')->exists($path . $qry_ext->_filegambar) && Storage::disk('sftp')->exists($path . $qry_ext->_filektp) && Storage::disk('sftp')->exists($path . $qry_ext->_filegambar) && trim($qry_ext->_filegambar) != '' && trim($qry_ext->_filektp) != '' && trim($qry_ext->_filewm) != '') {
                        $status_list = true;
                    }
                }
                //if belum
                else if ($request->status == 'belum') {
                    if (! Storage::disk('sftp')->exists($path . $qry_ext->_filegambar) || ! Storage::disk('sftp')->exists($path . $qry_ext->_filektp) || ! Storage::disk('sftp')->exists($path . $qry_ext->_filegambar) || trim($qry_ext->_filegambar) == '' || trim($qry_ext->_filektp) == '' || trim($qry_ext->_filewm) == '') {
                        $status_list = true;
                    }
                }
                //if semua
                else {
                    $status_list = true;
                }

                if ($status_list) {
                    $qry_arr[] = $qry_ext;
                }
            }
            //$qry = json_encode($qry_arr);
            //return $qry_arr;
            //*/

            $table = Datatables::of($qry_arr);
            //$table = Datatables::of($qry);

            $table->addColumn('placeholder', '');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate      = 'customer_show';
                $editGate      = 'customeropp_edit';
                $deleteGate    = 'customeroff_delete';
                $crudRoutePart = 'opp';

                return view('partials.datatablesActionsOpp', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('nomorrekening', function ($row) {
                return $row->nomorrekening ? $row->nomorrekening : "";
            });

            $table->editColumn('namapelanggan', function ($row) {
                return $row->namapelanggan ? $row->namapelanggan : "";
            });

            $table->editColumn('_email', function ($row) {
                return $row->_email ? $row->_email : "";
            });

            $table->editColumn('last_update', function ($row) {
                return $row->last_update ? $row->last_update : "";
            });

            $table->editColumn('alamat', function ($row) {
                return $row->alamat ? $row->alamat : "";
            });

            $table->editColumn('_gender', function ($row) {
                return $row->_gender ? ($row->_gender == 'male' ? 'Laki-laki' : 'Perempuan') : "";
            });

            $table->editColumn('_desctype', function ($row) {
                return $row->_desctype ? $row->_desctype : "";
            });

            $table->editColumn('_filegambar', function ($row) {
                return $row->_filegambar ? $row->_filegambar : "";
            });

            $table->editColumn('_filektp', function ($row) {
                return $row->_filektp ? $row->_filektp : "";
            });

            $table->editColumn('_filektp', function ($row) {
                return $row->_filektp ? $row->_filektp : "";
            });

            $table->editColumn('_filewm', function ($row) {
                return $row->_filewm ? $row->_filewm : "";
            });

            $table->editColumn('_filelain', function ($row) {
                return $row->_filelain ? $row->_filelain : "";
            });

            $table->editColumn('_desc', function ($row) {
                return $row->_desc ? $row->_desc : "";
            });

            $table->editColumn('lat', function ($row) {
                return $row->lat ? $row->lat : "";
            });

            $table->editColumn('lng', function ($row) {
                return $row->lng ? $row->lng : "";
            });

            $table->editColumn('noktp', function ($row) {
                //return $row->noktp ? $row->noktp : "";
                return $row->noktp ? "'" . $row->noktp : "";
            });
            
            $table->editColumn('_namaktp', function ($row) {
                return $row->_namaktp ? $row->_namaktp : "";
            });

            $table->editColumn('status', function ($row) {
                //$basepath = str_replace("laravel-simpletab", "public_html/pdam/", \base_path());
                $basepath = "/home/ptab-vps-storage/htdocs/www.ptab-vps-storage.com/pdam";
                return file_exists($basepath . $row->_filegambar) && file_exists($basepath . $row->_filektp) && file_exists($basepath . $row->_filewm) && file_exists($basepath . $row->_filelain) ? "Lengkap" : "Kurang";
            });

            $table->rawColumns(['actions', 'placeholder', 'noktp']);

            $table->addIndexColumn();
            return $table->make(true);
            //*/
        }
        $staff = CtmPbk::all();
        //return $staff;
        //default view
        return view('admin.customers.opp', compact('staff'));
        
    }

    public function index(Request $request)
    {
        abort_unless(\Gate::allows('customer_access'), 403);

        if ($request->ajax()) {
            //set query
            $qry = Customer::FilterMaps($request);

            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate      = 'customer_show';
                $editGate      = 'customer_edit';
                $deleteGate    = 'customer_delete';
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

            $table->editColumn('_desctype', function ($row) {
                return $row->_desctype ? $row->_desctype : "";
            });

            $table->editColumn('_filegambar', function ($row) {
                return $row->_filegambar ? $row->_filegambar : "";
            });

            $table->editColumn('_filektp', function ($row) {
                return $row->_filektp ? $row->_filektp : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        //default view
        return view('admin.customers.index');
    }

    public function create()
    {
        $last_code = $this->get_last_code('public');
        $code      = acc_code_generate($last_code, 8, 3);

        abort_unless(\Gate::allows('customer_create'), 403);
        return view('admin.customers.create', compact('code'));
    }

    public function store(StoreCustomerRequest $request)
    {
        abort_unless(\Gate::allows('customer_create'), 403);

        $customer           = new Customer;
        $customer->name     = $request->name;
        $customer->code     = $request->code;
        $customer->email    = $request->email;
        $customer->password = bcrypt($request->password);
        $customer->phone    = $request->phone;
        $customer->type     = $request->type;
        $customer->gender   = $request->gender;
        $customer->address  = $request->address;
        $customer->_synced  = 99;
        $customer->save();

        return redirect()->route('admin.customers.index');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('customer_edit'), 403);
        $customer = Customer::WhereMaps('id', $id)->first();
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request)
    {
        abort_unless(\Gate::allows('customer_edit'), 403);
        // dd("tess");
        $password_val = '';
        if (trim($request->password) != '') {
            if (trim($request->password) != trim($request->repassword)) {
                return back()->withError('Password dan Re-Password harus sama!');
            } else {
                $password_val = bcrypt($request->password);
            }
        }

        //synced
        $synced = 99;
        if ($request->type == 'customer') {
            $synced = 0;
        }

        $customer          = Customer::find($request->code);
        $customer->email   = $request->email;
        $customer->phone   = $request->phone;
        $customer->_synced = $synced;
        if ($password_val != '') {
            $customer->password = $password_val;
        }
        $customer->save();

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

    public function editImport()
    {
        abort_unless(\Gate::allows('customer_edit'), 403);
        return view('admin.customers.editImport');
    }

    public function updateImport(Request $request)
    {
        abort_unless(\Gate::allows('customer_edit'), 403);
        $import = new CustomerImport;
        $test   = Excel::import($import, $request->file('file'));
        // dd($test);
        $array = $import->getArray();
        // dd($array);
        abort_unless(\Gate::allows('wablast_access'), 403);

        $customers = $import->getArray();

        // dd($customers);
        ini_set("memory_limit", -1);
        set_time_limit(0);
        //ini test

        // dd($customers[2]['name']);
        for ($i = 0; $i < count($customers) - 1; $i++) {
            if ($customers[$i]['phone'] != null && $customers[$i]['nomorrekening'] != null) {
                $customer          = Customer::find($customers[$i]['nomorrekening']);
                $customer->phone   = $customers[$i]['phone'];
                $customer->_synced = 0;
                $customer->save();
            }
        }

        return redirect()->route('admin.customers.index');
    }

    public function viewReport()
    {
        abort_unless(\Gate::allows('customer_edit'), 403);

        $areas = CtmWilayah::select('id as code', 'NamaWilayah')->get();
        return view('admin.customers.report', compact('areas'));
    }

    public function reportExcel(Request $request)
    {

        abort_unless(\Gate::allows('customer_edit'), 403);
        ini_set("memory_limit", -1);
        set_time_limit(0);
        $data    = [];
        $idareal = $request->idareal;
        $bulan   = $request->month;
        $year    = date("Y");

        $offset = 0;
        $limit  = 70000;
        // $qry = Customer::join('tblpemakaianair', 'tblpemakaianair.nomorrekening', '=', 'tblpelanggan.nomorrekening')->join('map_koordinatpelanggan', 'map_koordinatpelanggan.nomorrekening', '=', 'tblpelanggan.nomorrekening')->where('idareal', $idareal)->where('tahunrekening', '2024')->get();

        $qry = Customer::selectRaw('tblpelanggan.*,tblpemakaianair.*,map_koordinatpelanggan.*, gambarmeter.filegambar as filegambar')->join('tblpemakaianair', 'tblpemakaianair.nomorrekening', '=', 'tblpelanggan.nomorrekening')->join('map_koordinatpelanggan', 'map_koordinatpelanggan.nomorrekening', '=', 'tblpelanggan.nomorrekening')->join('gambarmetersms', 'gambarmetersms.nomorrekening', '=', 'tblpelanggan.nomorrekening')->leftJoin('gambarmeter', 'gambarmeter.idgambar', '=', 'gambarmetersms.idgambar')->where('gambarmetersms.tahunrekening', $year)->where('gambarmetersms.bulanrekening', $bulan)->offset($offset)->limit($limit)->get();

        foreach ($qry as $d) {
            $pemakaian_air = "";
            switch ($bulan) {
                case '1':
                    $pemakaian_air = $d->pemakaianair1;
                    break;
                case '2':
                    $pemakaian_air = $d->pemakaianair2;
                    break;
                case '3':
                    $pemakaian_air = $d->pemakaianair3;
                    break;
                case '4':
                    $pemakaian_air = $d->pemakaianair4;
                    break;
                case '5':
                    $pemakaian_air = $d->pemakaianair5;
                    break;
                case '6':
                    $pemakaian_air = $d->pemakaianair6;
                    break;
                case '7':
                    $pemakaian_air = $d->pemakaianair7;
                    break;
                case '8':
                    $pemakaian_air = $d->pemakaianair8;
                    break;
                case '9':
                    $pemakaian_air = $d->pemakaianair9;
                    break;
                case '10':
                    $pemakaian_air = $d->pemakaianair10;
                    break;
                case '11':
                    $pemakaian_air = $d->pemakaianair11;
                    break;
                case '12':
                    $pemakaian_air = $d->pemakaianair12;
                    break;
                default:
                    # code...
                    break;
            }

            $data[] = [
                "nomorrekening"   => $d->nomorrekening,
                "namapelanggan"   => $d->namapelanggan,
                "alamat"          => $d->alamat,
                "dusun"           => $d->dusun,
                "desa"            => $d->desa,
                "kecamatan"       => $d->kecamatan,
                "idgol"           => $d->idgol,
                "idareal"         => $d->idareal,
                "tglterdaftar"    => $d->tglterdaftar,
                "tgltersambung"   => $d->tgltersambung,
                "status"          => $d->status,
                "idurut"          => $d->idurut,
                "idurutcode"      => $d->idurutcode,
                "tipe"            => $d->tipe,
                "idbiro"          => $d->idbiro,
                "idstatusdenda"   => $d->idstatusdenda,
                "nomorhp"         => $d->nomorhp,
                "nomorsurat"      => $d->nomorsurat,
                "tmplahir"        => $d->tmplahir,
                "tgllahir"        => $d->tgllahir,
                "alamat_detail"   => $d->alamat_detail,
                "alamat_ktp"      => $d->alamat_ktp,
                "telp"            => $d->telp,
                "pekerjaan"       => $d->pekerjaan,
                "flagpendaftaran" => $d->flagpendaftaran,
                "tglentry"        => $d->tglentry,
                "tglrab"          => $d->tglrab,
                "norab"           => $d->norab,
                "tglpanggil"      => $d->tglpanggil,
                "biayainstalasi"  => $d->biayainstalasi,
                "cicilan"         => $d->cicilan,
                "flaginstalasi"   => $d->flaginstalasi,
                "tglbap"          => $d->tglbap,
                "nobap"           => $d->nobap,
                "tglberlakubap"   => $d->tglberlakubap,
                "wmnomor"         => $d->wmnomor,
                "wmmerek"         => $d->wmmerek,
                "wmukuran"        => $d->wmukuran,
                "wmstandmeter"    => $d->wmstandmeter,
                "wmpetugas"       => $d->wmpetugas,
                "totalbayar"      => $d->totalbayar,
                "status_posting"  => $d->status_posting,
                "flag_bayar"      => $d->flag_bayar,
                "sono"            => $d->sono,
                "sms"             => $d->sms,
                "rupiah_meter"    => $d->rupiah_meter,
                "last_opp"        => $d->last_opp,
                "last_update"     => $d->last_update,
                "file_wm"         => $d->file_wm,
                "file_denah"      => $d->file_denah,
                "_lat"            => $d->lat,
                "_lng"            => $d->lng,
                "_tahun_rekening" => $d->tahunrekening,
                "_pemakaian air"  => $pemakaian_air,
                "foto_rumah"      => "https://ptab-vps-storage.com/pdam" . $d->_filegambar,
                "foto_ktp"        => "https://ptab-vps-storage.com/pdam" . $d->_filektp,
                "foto_wm"         => "https://ptab-vps-storage.com/pdam" . $d->filegambar, //filegambar _filewm
                "foto_lain"       => "https://ptab-vps-storage.com/pdam" . $d->_filelain,
            ];
        }

        return Excel::download(new CustomersExport($data), 'customers.xlsx');
    }
}
