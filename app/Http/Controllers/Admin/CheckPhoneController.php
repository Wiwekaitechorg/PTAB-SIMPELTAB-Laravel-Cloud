<?php

namespace App\Http\Controllers\Admin;

use App\CtmWilayah;
use App\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CheckPhoneController extends Controller
{
    public function index(Request $request)
    {
        // $qry = Customer::get();

        // dd($qry);

        $areas = CtmWilayah::select('id as code', 'NamaWilayah')->get();

        if ($request->ajax()) {
            //set query
            $qry = Customer::FilterNumber($request->number)->FilterWilayah($request->area)->take(1000);

            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = '';
                $editGate = '';
                $deleteGate = '';
                $crudRoutePart = 'historywa';

                return view('partials.datatablesAction', compact(
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

            $table->editColumn('namapelanggan', function ($row) {
                return $row->namapelanggan ? $row->namapelanggan : "";
            });

            $table->editColumn('telp', function ($row) {
                return $row->telp ? $row->telp : "";
            });

            $table->editColumn('nomorrekening', function ($row) {
                return $row->nomorrekening ? $row->nomorrekening : "";
            });
            $table->editColumn('adress', function ($row) {
                return $row->alamat ? $row->alamat : "";
            });

            $table->editColumn('idareal', function ($row) {
                return $row->idareal ? $row->idareal : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        return view('admin.whatsapp.check.index', compact('areas'));
    }
}
