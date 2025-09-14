<?php

namespace App\Http\Controllers\Admin;

use App\Channel;
use App\Http\Controllers\Controller;
use App\wa_history;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class HistoryWaController extends Controller
{

    public function index(Request $request)
    {
        // $qry = wa_history::FilterStatus($request->status)->FilterDate($request->from, $request->to);

        // dd($qry);

        if ($request->ajax()) {
            //set query
            $qry = wa_history::FilterStatus($request->status)
                ->FilterCustom($request->custom)
                ->FilterChannel($request->channel)
                ->FilterDate($request->from, $request->to);

            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = '';
                $editGate = '';
                $deleteGate = 'lock_delete';
                $crudRoutePart = 'historywa';

                return view('partials.datatablesAction', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });
            $table->editColumn('id_wa', function ($row) {
                return $row->id_wa ? $row->id_wa : "";
            });

            $table->editColumn('phone', function ($row) {
                return $row->phone ? $row->phone : "";
            });

            $table->editColumn('status', function ($row) {
                return $row->status ? $row->status : "";
            });

            $table->editColumn('customer_id', function ($row) {
                return $row->customer_id ? $row->customer_id : "";
            });

            $table->editColumn('template_id', function ($row) {
                return $row->template_id ? $row->template_id : "";
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
        $channelList = Channel::where('type', '!=', 'reguler')->get();
        return view('admin.whatsapp.history.index', compact('channelList'));
    }

    public function destroy($id)
    {
        wa_history::where('id', $id)->delete();
        return redirect()->back();
    }

    public function deleteAll()
    {
        DB::table('wa_histories')->truncate();
        return back();
    }
    public function deleteFilter(Request $request)
    {
        // dd($request->all());
        wa_history::FilterStatus($request->status)->FilterCustom($request->custom)->FilterDate($request->from, $request->to)->delete();
        return back();
    }
    public function resend()
    {
        $customers = wa_history::where('status', 'gagal')->get();
        dd($customers);
        $kumpulan_data = [];
        ini_set("memory_limit", -1);
        foreach ($customers as $value) {
            // $last_code = $this->get_last_code('history_wa');

            // $code = acc_code_generate($last_code, 8, 3);

            $data = [
                'phone' => $this->gantiformat($value->phone),
                // test
                // 'phone' => 'x',
                'customer_id' => $value->nomorrekening,
                'message' => $value->message,
                // 'id_wa' => 'empty',
                'template_id' => $request->template_id,
                'status' => 'gagal',
                'ref_id' => $code . $value->nomorrekening
            ];

            $kumpulan_data[] = $data;
        }
    }
}
