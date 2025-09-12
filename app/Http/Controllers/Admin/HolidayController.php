<?php

namespace App\Http\Controllers\Admin;

use App\Holiday;
use App\Http\Controllers\Controller;
use App\Staff;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(\Gate::allows('absence_all_access'), 403);
        // $holiday =  [
        //     ['id' => '1', 'name' => 'Hari Raya Galungan', 'date' => '10-10-2022'],
        //     ['id' => '2', 'name' => 'Hari Raya Kuningan', 'date' => '10-20-2022'],
        //     ['id' => '3', 'name' => 'Hari Raya Nyepi', 'date' => '10-1-2022'],

        // ];

        // $qry = Holiday::selectRaw('holidays.*, days.name as day')->join('days', 'holidays.day_id', '=', 'days.id')->get();
        // // dd($qry);
        // // $qry = TestModel::Filter($request)->Order('id', 'desc')->skip(0)->take(10)->get();
        // // return $qry;
        // if ($request->ajax()) {
        //     //set query
        //     $table = Datatables::of($qry);

        //     $table->addColumn('placeholder', '&nbsp;');
        //     $table->addColumn('actions', '&nbsp;');

        //     $table->editColumn('actions', function ($row) {
        //         $viewGate = '';
        //         $editGate = 'holiday_edit';
        //         $deleteGate = 'holiday_delete';
        //         $crudRoutePart = 'holiday';

        //         return view('partials.datatablesActions', compact(
        //             'viewGate',
        //             'editGate',
        //             'deleteGate',
        //             'crudRoutePart',
        //             'row'
        //         ));
        //     });
        //     $table->editColumn('id', function ($row) {
        //         return $row->id ? $row->id : "";
        //     });

        //     $table->editColumn('day', function ($row) {
        //         return $row->day ? $row->day : "";
        //     });

        //     $table->editColumn('title', function ($row) {
        //         return $row->title ? $row->title : "";
        //     });

        //     $table->editColumn('description', function ($row) {
        //         return $row->description ? $row->description : "";
        //     });

        //     $table->editColumn('date', function ($row) {
        //         return $row->date ? $row->date : "";
        //     });


        //     $table->editColumn('created_at', function ($row) {
        //         return $row->created_at ? $row->created_at : "";
        //     });
        //     $table->editColumn('updated_at', function ($row) {
        //         return $row->updated_at ? $row->updated_at : "";
        //     });

        //     $table->rawColumns(['actions', 'placeholder']);

        //     $table->addIndexColumn();
        //     return $table->make(true);
        // }
        if ($request->ajax()) {
            $data = Holiday::whereDate('start', '>=', $request->start)
                ->whereDate('end',   '<=', $request->end)
                ->get(['id', 'title', 'start', 'end']);
            return response()->json($data);
        }
        //return $data;
        $pgw = Staff::get();
        //default view
        // return view('admin.schedule.index');
        // dd($holiday);
        return view('admin.holiday.index');
    }

    public function create()
    {
        // abort_unless(\Gate::allows('holiday_create'), 403);
        abort_unless(\Gate::allows('absence_all_access'), 403);
        return view('admin.holiday.create');
    }
    public function store(Request $request)
    {
        // abort_unless(\Gate::allows('holiday_create'), 403);
        abort_unless(\Gate::allows('absence_all_access'), 403);
        $checkD = date("w",  strtotime($request->date));
        if ($checkD == "0") {
            $day = 7;
        } else {
            $day = $checkD;
        }
        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'day_id' => $day,
        ];

        Holiday::create($data);
        return redirect()->route('admin.holiday.index');
    }
    public function edit(Request $request)
    {
        if ($request->ajax()) {
            $data = [];
            $staff = "";
            $holiday = Holiday::where('id', $request->id)->first();
            $data =  $holiday;
            return response()->json($data);
        }
    }
    public function update(Request $request, $id)
    {
        abort_unless(\Gate::allows('holiday_edit'), 403);
        $checkD = date("w",  strtotime($request->date));
        if ($checkD == "0") {
            $day = 7;
        } else {
            $day = $checkD;
        }
        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'day_id' => $day,
        ];
        Holiday::where('id', $id)->update($data);
        return redirect()->route('admin.holiday.index');
    }

    public function destroy($id)
    {
        abort_unless(\Gate::allows('holiday_delete'), 403);
        Holiday::where('id', $id)->delete();
        return redirect()->back();
    }


    public function action(Request $request)
    {
        if ($request->ajax()) {
            if ($request->type == 'add') {
                $cek = Holiday::whereDate('start', '<=', $request->start)
                    ->whereDate('end', '>=', $request->end)
                    ->get();

                if (count($cek) > 0) {
                    $event = "fail";
                } else {
                    $event = Holiday::create([
                        'title'        =>    $request->title,
                        'start'        =>    $request->start,
                        'description'        =>    $request->description,
                        'end'        =>    $request->start
                    ]);
                }
                return response()->json($event);
            }

            if ($request->type == 'update') {
                $event = Holiday::find($request->id)->update([
                    'title'        =>    $request->title,
                    'description'        =>    $request->description
                    // 'start'        =>    $request->start,
                    // 'end'        =>    $request->end
                ]);

                return response()->json($event);
            }

            if ($request->type == 'delete') {
                $event = Holiday::find($request->id)->delete();

                return route('admin.duty.index');
                // return response()->json($event);
            }
        }
    }
}
