<?php

namespace App\Http\Controllers\api\v1\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\SubdapertementApi;
use Illuminate\Database\QueryException;
use App\Traits\TraitModel;
use App\User;

class SubdapertementsApiController extends Controller
{
        
    use TraitModel;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function subdapertements(Request $request)
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

        if (isset($request->page) && $request->page != '') {
            if ($department != '') {
                $subdapertements = SubdapertementApi::where('dapertement_id',$department)->with('dapertement')->paginate(10, ['*'], 'page', $request->page);
            } else {
                $subdapertements = SubdapertementApi::with('dapertement')->paginate(10, ['*'], 'page', $request->page);
            }
        }else{
            if ($department != '') {
                $subdapertements = SubdapertementApi::where('dapertement_id',$department)->with('dapertement')->get();
            } else {
                $subdapertements = SubdapertementApi::with('dapertement')->get();
            }
        }

        
        return response()->json([
            'message' => 'success',
            'data' => $subdapertements
        ]);
    }

    public function index()
    {
        
        try {
            $subdapertements = SubdapertementApi::with('dapertement')->all();         
            return response()->json([
                'message' => 'Sucess',
                'data' => $subdapertements
            ]);
       } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Sucess',
                'data' => $th
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
        $last_code = $this->get_last_code('subdapertement');

        $code = acc_code_generate($last_code, 8, 3);
        
        $data = $request->all();
        // isset($request->email) ? $data['email'] = $request->email : null;

        $rules=array(
            // 'email' => 'email|unique:customers,email',
            // 'code' => 'unique:customers,code',
            'name' => 'required|unique:subdapertements,name',
            'description' => 'required',
        );

        if(isset($request->code)){
            $rules['code'] = 'required|unique:subdapertements,code';
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

        $subdapertement = SubdapertementApi::create($data);

        return response()->json([
            'message' => 'Data Dapertement Add Success',
            'data' => $subdapertement
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
    public function update(Request $request, SubdapertementApi $subdapertement)
    {
        $rules=array(
            'code' => 'required|unique:subdapertements,code,'.$subdapertement->id,
            'name' => 'required|unique:subdapertements,name,'.$subdapertement->id,
            'description' => 'required',
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

        
        $subdapertement->update($request->all());

        return response()->json([
            'message' => 'Data Dapertement Update Success',
            'data' => $subdapertement
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( SubdapertementApi $subdapertement)
    {
        try{
            
            $subdapertement->delete();
            return response()->json([
                'message' => 'Dapertement berhasil di hapus',
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
