<?php

namespace App\Http\Controllers\api\v1\admin;

use App\CtmPelanggan;
use App\CustomerApi;
use App\Http\Controllers\Controller;
use App\Traits\TraitModel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class CustomersApiController extends Controller
{

    use TraitModel;

    public function defcustomer()
    {
        try {
            $customer = CustomerApi::where('_def', '1')->first();
            if (!empty($customer)) {
                $customer_id = $customer->id;
                return response()->json([
                    'message' => 'success',
                    'data' => $customer_id,
                ]);
            } else {
                return response()->json([
                    'message' => 'failed',
                    'data' => '',
                ]);
            }

        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'failed',
                'data' => $ex,
            ]);
        }
    }

    public function customers(Request $request)
    {
        try {
            $search = $request->search;
            if ($search != '') {
                $customer = CtmPelanggan::where('nomorrekening', '=', $search)
                    ->paginate(10, ['*'], 'page', $request->page);
            } else {
                $customer = CustomerApi::paginate(10, ['*'], 'page', $request->page);
            }

            return response()->json([
                'message' => 'success',
                'data' => $customer,
                'page' => $request->page,
                'seacrh' => $request->search,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'failed',
                'data' => $ex,
            ]);
        }
    }

    public function index()
    {
        $customer = CustomerApi::skip(0)->take(100)->get();

        return response()->json([
            'message' => 'Sucess',
            'data' => $customer,
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {

        $last_code = $this->get_last_code('public');

        $code = acc_code_generate($last_code, 8, 3);

        $rules = array(
            'name' => 'required',
            'phone' => 'required|unique:mysql2.tblpelanggan,telp',
            'type' => 'required',
            'gender' => 'required',
            'address' => 'required',
        );

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->messages();
            $errors = $messages->all();
            return response()->json([
                'message' => $errors,
                'data' => $request->all(),
            ]);
        }

        $customer = new CustomerApi;
        $customer->name = $request->name;
        $customer->code = $code;

        if (!isset($request->email)) {
            $customer->email = null;
        } else {
            $request->validate([
                'email' => 'required|email',
            ]);
            $customer->email = $request->email;
        }
        $customer->email_verified_at = null;
        $customer->remember_token = null;
        $customer->password = bcrypt($request->password);
        $customer->phone = $request->phone;
        $customer->type = 'public';
        $customer->gender = $request->gender;
        $customer->address = $request->address;
        $customer->_synced = 99;

        try {
            $customer->save();
            return response()->json([
                'message' => 'Registrasi Berhasil',
                'data' => $customer,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                // 'message' => 'Registrasi Berhasil',
                // 'token' => $token,
                'data' => $ex,
            ]);
        }

    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request)
    {
        $customer = CustomerApi::find($request->code);
        $rules = array(
            'email' => 'required|email',
            'code' => 'required|unique:mysql2.tblpelanggan,nomorrekening,' . $request->code . ',nomorrekening',
            'name' => 'required',
            'phone' => 'required|unique:mysql2.tblpelanggan,telp,' . $request->code . ',nomorrekening',
            'type' => 'required',
            'gender' => 'required',
            'address' => 'required',
        );
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->messages();
            $errors = $messages->all();
            return response()->json([
                'message' => $errors,
                'data' => $customer,
            ]);
        }

        //synced
        $synced=99;
        if($request->type=='customer'){
            $synced=0;
        }

        $customer->name = $request->name;
        $customer->code = $request->code;
        $customer->email = $request->email;
        $customer->phone = $request->phone;
        $customer->type = $request->type;
        $customer->gender = $request->gender;
        $customer->address = $request->address;
        $customer->_synced = $synced;
        $customer->save();

        return response()->json([
            'message' => 'Data Customer Update Success',
            'data' => $customer,
        ]);

    }

    public function destroy(CustomerApi $customer)
    {
        // abort_unless(\Gate::allows('staff_delete'), 403);

        try {

            $customer->delete();
            return response()->json([
                'message' => 'Customer berhasil di hapus',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'data masih ada dalam daftar keluhan',
                'data' => $e,
            ]);
        }

    }

}
