<?php

namespace App\Http\Controllers\api\v1\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\StaffApi;
use Illuminate\Database\QueryException;
use App\Traits\TraitModel;
use App\User;
class StaffsApiController extends Controller
{
    use TraitModel;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function staffs(Request $request)
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
        
        $staffs = StaffApi::with('dapertement')->with('subdapertement')->FilterDapertement($department)->paginate(10, ['*'], 'page', $request->page);
        return response()->json([
            'message' => 'success',
            'data' => $staffs
        ]);
    }

    public function index()
    {
        $staffs = StaffApi::with('dapertement')->with('subdapertement')->get();
        return response()->json([
            'message' => 'Sucess',
            'data' => $staffs
        ]);
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
        $last_code = $this->get_last_code('staff');

        $code = acc_code_generate($last_code, 8, 3);
        
        $data = $request->all();
        // isset($request->email) ? $data['email'] = $request->email : null;

        $rules=array(
            // 'email' => 'email|unique:customers,email',
            // 'code' => 'unique:customers,code',
            'name' => 'required|unique:staffs,name',
            'phone' => 'required|unique:staffs,phone',
            'dapertement_id' => 'required',
        );

        if(isset($request->code)){
            $rules['code'] = 'required|unique:staffs,code';
        }
        
        $data['code'] = isset($request->code) ? $request->code : $code;

        $validator=\Validator::make($data,$rules);
        if($validator->fails())
        {
            $messages=$validator->messages();
            $errors=$messages->all();
            return response()->json([
                'message' => $errors,
                'data' => $request->all()
            ]);
        }

        $staff = StaffApi::create($data);

        return response()->json([
            'message' => 'Data Dapertement Add Success',
            'data' => $staff
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
    public function update(Request $request, StaffApi $staff)
    {
        $rules=array(
            // 'email' => 'email|unique:customers,email',
            // 'code' => 'unique:customers,code',
            'code' => 'required|unique:staffs,code,'.$staff->id,
            'name' => 'required|unique:staffs,name,'.$staff->id,
            'phone' => 'required|unique:staffs,phone,'.$staff->id,
            'dapertement_id' => 'required',
        );

        $validator=\Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            $messages=$validator->messages();
            $errors=$messages->all();
            return response()->json([
                'message' => $errors,
                'data' => $request->all()
            ]);
        }

        $staff->update($request->all());
        
  
        return response()->json([
            'message' => 'Data Category Update Success',
            'data' => $staff
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( StaffApi $staff)
    {
        try{
            
            $staff->delete();
            return response()->json([
                'message' => 'Staff berhasil di hapus',
            ]);
        }
        catch(QueryException $e) {
           return response()->json([
               'message' => 'data masih ada dalam daftar keluhan',
               'data' => $e
           ]);
        }
    }
}
