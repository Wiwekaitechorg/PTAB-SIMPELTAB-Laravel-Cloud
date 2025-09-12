<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\WablasTrait;
use App\User;
use App\WaReceives;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WaReceivesController extends Controller
{
    use WablasTrait;

    public function index()
    {
        abort_unless(\Gate::allows('absence_all_access'), 403);
        // in_array('absence_all_access', $checker);

        $waReceives = WaReceives::get();
        // $k = WaReceives::pluck('no_telp');

        // $waReceives = WaReceives::pluck('no_telp');
        // //wa notif                
        // $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
        // $wa_data_group = [];
        // //get phone user
        // for ($i = 0; $i < count($waReceives); $i++) {
        //     $wa_data = [
        //         'phone' => $this->gantiFormat($waReceives[$i]),
        //         'customer_id' => null,
        //         'message' => $message,
        //         'template_id' => '',
        //         'status' => 'gagal',
        //         'ref_id' => $wa_code,
        //         'created_at' => date('Y-m-d h:i:sa'),
        //         'updated_at' => date('Y-m-d h:i:sa')
        //     ];
        //     $wa_data_group[] = $wa_data;
        // }


        // dd($wa_data_group);

        return view('admin.waReceives.index', compact('waReceives'));
    }

    public function store(Request $request)
    {
        abort_unless(\Gate::allows('absence_all_access'), 403);
        $validated = $request->validate([
            'no_telp' => 'required|unique:wa_receives|max:255'
            // 'body' => 'required',
        ]);
        $waReceives = WaReceives::create($request->all());
        return redirect()->back();
    }

    public function destroy($id)
    {
        abort_unless(\Gate::allows('absence_all_access'), 403);
        $a = WaReceives::where('id', $id)->first();
        // dd($a);
        WaReceives::where('id', $id)->delete();
        return redirect()->back();
    }
}
