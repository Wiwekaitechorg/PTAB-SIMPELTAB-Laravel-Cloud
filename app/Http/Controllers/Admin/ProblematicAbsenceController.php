<?php

namespace App\Http\Controllers\Admin;

use App\ProblematicAbsence;
use App\ProblematicAbsenceCategories;
use App\Day;
use App\Holiday;
use App\Http\Controllers\Controller;
use App\Requests;
use App\ShiftStaff;
use App\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class ProblematicAbsenceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(\Gate::allows('customer_access'), 403);
        $qry = ProblematicAbsence::selectRaw('problematic_absences.*, users.name as user, days.name as day, problematic_absence_categories.title as problematic_absence_category')->leftJoin('users', 'problematic_absences.user_id', '=', 'users.id')
            ->leftJoin('days', 'problematic_absences.day_id', '=', 'days.id')
            ->leftJoin('problematic_absence_categories', 'problematic_absences.Problematic_absence_category_id', '=', 'problematic_absence_Categories.id');
        // dd($qry->get());
        // $qry = TestModel::Filter($request)->Order('id', 'desc')->skip(0)->take(10)->get();
        // return $qry;
        if ($request->ajax()) {
            //set query
            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = '';
                $editGate = '';
                $deleteGate = '';
                $crudRoutePart = '';

                return view('partials.datatablesActions', compact(
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

            $table->editColumn('user', function ($row) {
                return $row->user ? $row->user : "";
            });

            $table->editColumn('image', function ($row) {
                return $row->image ? $row->image : "";
            });

            $table->editColumn('lat', function ($row) {
                return $row->lat ? $row->lat : "";
            });
            $table->editColumn('lng', function ($row) {
                return $row->lng ? $row->lng : "";
            });
            $table->editColumn('register', function ($row) {
                return $row->register ? $row->register : "";
            });
            $table->editColumn('absen_category', function ($row) {
                return $row->absen_category ? $row->absen_category : "";
            });
            $table->editColumn('day', function ($row) {
                return $row->day ? $row->day : "";
            });
            $table->editColumn('late', function ($row) {
                return $row->late ? $row->late : "";
            });
            $table->editColumn('value', function ($row) {
                return $row->value ? $row->value : "";
            });
            $table->editColumn('late', function ($row) {
                if ($row->late === 0) {
                    return "Lambat";
                } else {
                    return "Tepat";
                }
            });
            $table->editColumn('updated_at', function ($row) {
                return $row->updated_at ? $row->updated_at : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        //default view
        // return view('admin.schedule.index');

        return view('admin.problematicAbsence.index');
    }

    public function create()
    {
        $problematicAbsenceCategories = ProblematicAbsenceCategories::where('day_id', null)->get();
        $day = Day::get();
        $users = User::where('staff_id', '!=', '0')->orderBy('name')->get();
        return view('admin.problematicAbsence.create', compact('problematicAbsenceCategories', 'day', 'users'));
    }

    public function store(Request $request)
    {
        $checkD = date("w",  strtotime($request->register));
        if ($checkD == "0") {
            $day = 7;
        } else {
            $day = $checkD;
        }
        $data = [
            'user_id' => $request->user_id,
            'image' => '',
            'lat' => '',
            'lng' => '',
            'register' => $request->register,
            'shift_id' => '',
            'Problematicabsence_category_id' => $request->Problematicabsence_category_id,
            'day_id' => $day,
        ];
        ProblematicAbsence::create($data);
        dd($data);
    }


    public function edit($id)
    {
    }

    public function update(Request $request)
    {
    }
}
