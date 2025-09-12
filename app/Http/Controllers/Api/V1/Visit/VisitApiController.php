<?php

namespace App\Http\Controllers\Api\V1\Visit;

use App\CtmStatussmPelanggan;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Staff;
use App\Visit;
use App\VisitCategory;
use App\VisitImage;
use Illuminate\Http\Request;

class VisitApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $staff = Staff::where('id', $request->staff_id)->first();

            $data =
                CtmStatussmPelanggan::selectRaw('tblstatuswm.id as status_wm, tblpelanggan.nomorrekening,tblstatuswm.NamaStatus,tblstatussmpelanggan.nomorrekening, tblpelanggan.namapelanggan, tblpelanggan.idareal as idareal,tblpelanggan.alamat as alamat, tblstatussmpelanggan.bulan, tblopp.operator, tblstatussmpelanggan.tahun')
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblstatussmpelanggan.nomorrekening')
                ->join('tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
                ->join('tblstatuswm', 'tblstatuswm.id', '=', 'tblstatussmpelanggan.statussm')
                ->join('tblopp', 'tblopp.nomorrekening', '=', 'tblpelanggan.nomorrekening')
                ->whereIn('statussm', [107, 105])
                ->where('tblopp.operator', 'like', $staff->pbk)
                ->where('tahun', date('Y', strtotime('-1 month', strtotime(date('Y-m-d')))))
                ->where('bulan', date('m', strtotime('-1 month', strtotime(date('Y-m-d')))))
                ->FilterStatusWM($request->statussm)->FilterAreas($request->areas)
                ->FilterStatusWM($request->statussm)->FilterAreas($request->areas)
                ->FilterNomorrekening($request->customer_id)
                ->paginate(10, ['*'], 'page', $request->page);
            return response()->json([
                'message' => 'success',
                'data' => $data,
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

        // $last_code = $this->get_last_code('lock_action');

        // $code = acc_code_generate($last_code, 8, 3);
        // $img_path = "/images/segelMeter";
        // //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
            //$basepath  = base_path().'/public';

        try {
            $visit = Visit::create([
                'staff_id' => '404',
                'customer_id' => $request->customer_id,
                'status_wm' => $request->status_wm,
                'description' => $request->description,
                'visit_category_id' => null,
                'dapertement_id' => null,
                'lat' =>  $request->lat,
                'lng' =>  $request->lng,
            ]);

            if ($request->file('image')) {
                foreach ($request->file('image') as $img) {

                    if ($img) {
                        $nameImage = $img->getClientOriginalName();
                        // $file_extImage = $image->extension();
                        $folder_upload = 'images/Visit';
                        $img->move($folder_upload, $nameImage);
                    }


                    $data = [
                        'image' =>  $nameImage,
                        'visit_id' => $visit->id
                    ];
                    $data = VisitImage::create($data);
                    // }
                }
            }


            return response()->json([
                'message' => 'Segel Meter Terkirim',
                'data' => $visit,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }
    public function show($id)
    {
        $customer = Customer::selectRaw('tblpelanggan.*, map_koordinatpelanggan.lat, map_koordinatpelanggan.lng')
            ->leftJoin('map_koordinatpelanggan', 'map_koordinatpelanggan.nomorrekening', '=', 'tblpelanggan.nomorrekening')
            ->where('tblpelanggan.nomorrekening', $id)
            ->first();
        return response()->json([
            'message' => 'Show',
            'data' => $customer,
        ]);
    }
    public function getDataCbox()
    {
        $cat = [];
        $visit_category = VisitCategory::get();
        foreach ($visit_category as $data) {
            $cat[] = ['id' => $data->id, 'name' => $data->name, 'checked' => false];
        }

        return response()->json([
            'message' => 'success',
            'cat' => $cat,
        ]);
    }

    public function getDataStaff(Request $request)
    {

        $staffs = Staff::selectRaw('staffs.*,work_units.name as work_unit_name')
            ->join('work_units', 'staffs.work_unit_id', '=', 'work_units.id')
            ->FilterName($request->name)
            ->paginate(10, ['*'], 'page', $request->page);


        return response()->json([
            'message' => 'success',
            'data' => $staffs,
        ]);
    }

    public function getHistory(Request $request)
    {

        $history = Visit::FilterDate($request->from, $request->to)
            ->FilterCustomer($request->nomorrekening)
            ->where('staff_id', $request->staff_id)
            ->with('visitCategory')
            ->with('visitImages')
            ->with('customer')
            ->orderBy('created_at', 'DESC')
            ->paginate(10, ['*'], 'page', $request->page);


        return response()->json([
            'message' => 'success',
            'data' => $history,
        ]);
    }

    public function storeEtc(Request $request)
    {

        // $last_code = $this->get_last_code('lock_action');

        // $code = acc_code_generate($last_code, 8, 3);
        // $img_path = "/images/segelMeter";
        // //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
            //$basepath  = base_path().'/public';

        try {
            $staffs =  $request->staff_id;
            $parent_visit = null;
            for ($i = 0; $i < count($staffs); $i++) {
                $visit = Visit::create([
                    'staff_id' => $staffs[$i],
                    'visit_category_id' => $request->visit_category_id,
                    'description' => $request->description,
                    'dapertement_id' => null,
                    'group_id' => $parent_visit,
                    'lat' =>  $request->lat,
                    'lng' =>  $request->lng,
                    'absence_request_id' => $request->absence_request_id
                ]);
                if ($i === 0) {
                    $parent_visit = $visit->id;


                    if ($request->file('image')) {
                        foreach ($request->file('image') as $img) {

                            if ($img) {
                                $nameImage = $img->getClientOriginalName();
                                $folder_upload = 'images/Visit';
                                $img->move($folder_upload, $nameImage);
                            }


                            $data = [
                                'image' =>  $nameImage,
                                'visit_id' => $visit->id
                            ];
                            $data = VisitImage::create($data);
                            // }
                        }
                    }
                }
            }



            return response()->json([
                'message' => 'Segel Meter Terkirim',
                'data' => $visit,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }
}
