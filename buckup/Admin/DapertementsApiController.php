<?php

namespace App\Http\Controllers\api\v1\admin;

use App\DapertementApi;
use App\Http\Controllers\Controller;
use App\Traits\TraitModel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\User;

class DapertementsApiController extends Controller
{

    use TraitModel;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function dapertements($page)
    {
        $dapertements = DapertementApi::paginate(10, ['*'], 'page', $page);
        return response()->json([
            'message' => 'success',
            'data' => $dapertements,
        ]);
    }

    public function index(Request $request)
    {

        $department = '';
        if (isset($request->userid) && $request->userid != '') {
            $admin = User::with('roles')->find($request->userid);
            $role = $admin->roles[0];
            $role->load('permissions');
            $permission = json_decode($role->permissions->pluck('title'));
            if (!in_array("ticket_all_access", $permission)) {
                $department = $admin->dapertement_id;
            }
        }

        try {
            if ($department != '') {
                $dapertements = DapertementApi::where('id',$department)->get();
            } else {
                $dapertements = DapertementApi::all();
            }
            return response()->json([
                'message' => 'Sucess',
                'data' => $dapertements,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Sucess',
                'data' => $th,
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $last_code = $this->get_last_code('dapertement');

        $code = acc_code_generate($last_code, 8, 3);

        $data = $request->all();
        // isset($request->email) ? $data['email'] = $request->email : null;

        $rules = array(
            // 'email' => 'email|unique:customers,email',
            // 'code' => 'unique:customers,code',
            'name' => 'required|unique:dapertements,name',
            'description' => 'required',
        );

        if (isset($request->code)) {
            $rules['code'] = 'required|unique:dapertements,code';
        }

        $data['code'] = isset($request->code) ? $request->code : $code;

        $validator = \Validator::make($data, $rules);
        if ($validator->fails()) {
            $messages = $validator->messages();
            $errors = $messages->all();
            return response()->json([
                'message' => $errors,
                'data' => $request->all(),
            ]);
        }

        $dapertement = DapertementApi::create($data);

        return response()->json([
            'message' => 'Data Dapertement Add Success',
            'data' => $dapertement,
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DapertementApi $dapertement)
    {
        $rules = array(
            'code' => 'required|unique:dapertements,code,' . $dapertement->id,
            'name' => 'required|unique:dapertements,name,' . $dapertement->id,
            'description' => 'required',
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

        $dapertement->update($request->all());

        return response()->json([
            'message' => 'Data Dapertement Update Success',
            'data' => $dapertement,
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DapertementApi $dapertement)
    {
        try {

            $dapertement->delete();
            return response()->json([
                'message' => 'Dapertement berhasil di hapus',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'data masih ada dalam daftar keluhan',
                'data' => $e,
            ]);
        }
    }
}
