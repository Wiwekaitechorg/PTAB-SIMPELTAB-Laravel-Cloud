<?php

namespace App\Http\Controllers\Admin;

use App\Absence_categories;
use App\Http\Controllers\Controller;
use App\Schedule;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{

    public function index(Request $request)
    {
        abort_unless(\Gate::allows('schedule_access'), 403);
        $qry = Absence_categories::selectRaw('absence_categories.*, days.name as day')
            ->join('days', 'days.id', '=', 'absence_categories.day_id')
            ->where('day_id', '!=', '')
            ->get();
        // dd($qry);
        // $qry = TestModel::Filter($request)->Order('id', 'desc')->skip(0)->take(10)->get();
        // return $qry;
        if ($request->ajax()) {
            //set query
            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = '';
                $editGate = 'schedule_edit';
                $deleteGate = '';
                $crudRoutePart = 'schedule';

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });
            $table->editColumn('day', function ($row) {
                return $row->day ? $row->day : "";
            });

            $table->editColumn('title', function ($row) {
                return $row->title ? $row->title : "";
            });

            $table->editColumn('time', function ($row) {
                return $row->time ? $row->time : "";
            });

            $table->editColumn('start', function ($row) {
                return $row->start ? $row->start : "";
            });
            $table->editColumn('end', function ($row) {
                return $row->end ? $row->end : "";
            });
            $table->editColumn('value', function ($row) {
                return $row->value ? $row->value : "";
            });
            $table->editColumn('queue', function ($row) {
                return $row->queue ? $row->queue : "";
            });
            $table->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at : "";
            });
            $table->editColumn('updated_at', function ($row) {
                return $row->updated_at ? $row->updated_at : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        //default view
        return view('admin.schedule.index');
    }

    // public function index()
    // {
    //     // $schedule =  [
    //     //     ['id' => '1', 'start' => '07:30:00', 'end' => '08:30:00', 'activity' => 'reguler', 'day' => 'senin', 'part' => '1', 'time' => '08:00:00', 'status' => 'approve'],
    //     //     ['id' => '2', 'start' => '15:30:00', 'end' => '16:30:00', 'activity' => 'reguler', 'day' => 'senin', 'part' => '2', 'time' => '16:00:00', 'status' => 'approve'],
    //     //     ['id' => '3',  'start' => '07:30:00', 'end' => '08:30:00', 'activity' => 'reguler', 'day' => 'selasa', 'part' => '1', 'time' => '08:00:00', 'status' => 'approve'],
    //     //     ['id' => '4', 'start' => '15:30:00', 'end' => '16:30:00', 'activity' => 'reguler', 'day' => 'selasa', 'part' => '2', 'time' => '16:00:00', 'status' => 'approve']
    //     // ];
    //     $data = Absence_categories::get();
    //     // dd($data);

    //     return view('admin.schedule.index', compact('data'));
    // }

    public function edit($id)
    {
        abort_unless(\Gate::allows('schedule_edit'), 403);
        $schedule = Absence_categories::where('id', $id)->first();
        return view('admin.schedule.edit', compact('schedule'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(\Gate::allows('schedule_edit'), 403);
        abort_unless(\Gate::allows('role_edit'), 403);
        $schedule = Absence_categories::where('id', $id)->first();
        $schedule->update($request->all());

        return redirect()->route('admin.schedule.index');
    }

    public function test()
    {
        $test = [
            [
                "label" => 'Kota',
                "data" => [0.8, 0.9, 1],
                "backgroundColor" => [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(255, 99, 132, 0.2)'

                ],
                "borderColor" => [
                    'rgba(255,99,132,1)',
                    'rgba(255,99,132,1)',
                    'rgba(255,99,132,1)',

                ],
                "borderWidth" => 1
            ],
            [
                "label" => 'Kerambitan',
                "data" => [0.6, 0.6, 1],
                "backgroundColor" => [

                    'rgba(54, 162, 235, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(54, 162, 235, 0.2)',

                ],
                "borderColor" => [
                    'rgba(54, 162, 235, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(54, 162, 235, 1)',

                ],
                "borderWidth" => 1
            ],
            [
                "label" => 'Penebel',
                "data" => [0.4, 0.5, 1],
                "backgroundColor" => [
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(255, 206, 86, 0.2)',

                ],
                "borderColor" => [
                    'rgba(255, 206, 86, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(255, 206, 86, 1)',

                ],
                "borderWidth" => 1
            ],
            [
                "label" => 'Baturuti',
                "data" => [0.7, 0.8, 1],
                "backgroundColor" => [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(75, 192, 192, 0.2)',

                ],
                "borderColor" => [
                    'rgba(75, 192, 192, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(75, 192, 192, 1)',

                ],
                "borderWidth" => 1
            ],
            [
                "label" => 'Selemadeg',
                "data" => [0.9, 0.9, 1],
                "backgroundColor" => [
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(153, 102, 255, 0.2)',

                ],
                "borderColor" => [
                    'rgba(153, 102, 255, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(153, 102, 255, 1)',
                ],
                "borderWidth" => 1
            ]
        ];

        $test1 = [
            [
                "label" => 'Absen',
                "data" => [0.8, 0.9, 1],
                "backgroundColor" => [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(255, 99, 132, 0.2)'

                ],
                "borderColor" => [
                    'rgba(255,99,132,1)',
                    'rgba(255,99,132,1)',
                    'rgba(255,99,132,1)',

                ],
                "borderWidth" => 1
            ],
        ];
        $test = json_encode($test);
        $test1 = json_encode($test1);
        // dd($test);

        return view('admin.schedule.edit-bak', compact('test', 'test1'));
    }
}
