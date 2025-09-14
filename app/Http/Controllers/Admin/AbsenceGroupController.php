<?php

namespace App\Http\Controllers\Admin;

use App\Absence;
use App\Absence_categories;
use App\AbsenceRequest;
use App\Dapertement;
use App\Http\Controllers\Controller;
use App\Staff;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class AbsenceGroupController extends Controller
{
    public function index(Request $request)
    {
        // $absence_request_count =  AbsenceRequest::selectRaw('COUNT(CASE WHEN category = "visit" THEN 1 END) AS visit_count')
        //     ->selectRaw('COUNT(CASE WHEN category = "duty" THEN 1 END) AS duty_count')
        //     ->selectRaw('COUNT(CASE WHEN category = "excuse" THEN 1 END) AS excuse_count')
        //     ->selectRaw('COUNT(CASE WHEN category = "extra" THEN 1 END) AS extra_count')
        //     ->selectRaw('COUNT(CASE WHEN category = "leave" THEN 1 END) AS leave_count')
        //     ->selectRaw('COUNT(CASE WHEN category = "geolocation_off" THEN 1 END) AS geolocation_off_count')
        //     ->selectRaw('COUNT(CASE WHEN category = "permission" THEN 1 END) AS permission_count')
        //     ->first();
        // dd($absence_request_count);



        $qry = Absence::selectRaw('staffs.*,absences.*')
            // ->where('status_active', '!=', '')

            ->join('staffs', 'staffs.id', '=', 'absences.staff_id')
            ->FilterAbsence($request->id);
        // ->get();
        // dd($qry->get());

        if ($request->ajax()) {
            //set query
            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                // $approveGate = 'absence_edit';
                // $deleteGate = '';
                $viewGate = '';
                $editGate = 'absence_edit';
                $deleteGate = 'absence_delete';
                $crudRoutePart = 'absencegroup';

                return view('partials.datatablesActions', compact(
                    // 'approveGate',
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });
            $table->editColumn('id', function ($row) {
                return $row->id ? $row->id : "";
            });

            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : "";
            });

            $table->editColumn('NIK', function ($row) {
                return $row->NIK ? $row->NIK : "";
            });

            $table->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at : "";
            });

            $table->editColumn('status_active', function ($row) {
                if ($row->status_active == "") {
                    return "Masuk";
                } else if ($row->status_active == "1") {
                    return "Fingerprint Bermasalah";
                } else if ($row->status_active == "2") {
                    return "Lembur Mendesak";
                } else if ($row->status_active == "3") {
                    return "Permisi Tidak Kembail";
                } else if ($row->status_active == "4") {
                    return "Dianggap Tidak Hadir";
                }
                return $row->status_active ? $row->status_active : "";
            });



            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        // default view
        // return view('admin.schedule.index');
        $staffs = Staff::orderBy('name', 'ASC')->get();
        $dapertements = Dapertement::get();
        $absence_categories = Absence_categories::get();

        return view('admin.absenceGroup.index', compact('staffs', 'dapertements', 'absence_categories', 'request'));
    }
    public function approve(Request $request)
    {
        // dd("hjk");
        $absence = Absence::where('id', $request->id)->first();
        // dd($absence);
        $absence->update([
            'status_active' => ''
        ]);
        return back();
    }
    public function edit($id)
    {
        abort_unless(\Gate::allows('absence_edit'), 403);
        $absence = Absence::where('id', $id)->first();
        // dd($absence);
        return view('admin.absenceGroup.edit', compact('absence'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(\Gate::allows('absence_edit'), 403);
        $absence = Absence::where('id', $id)->first();

        $absence->update($request->all());

        return redirect()->route('admin.absencegroup.index');
    }

    public function destroy($id)
    {
        abort_unless(\Gate::allows('absence_edit'), 403);

        $absence = Absence::where('id', $id)->first();
        $absence->absence_logs()->delete();
        $absence->delete();
        // $absence->update($request->all());

        return redirect()->back();
    }
}
