<?php

namespace App\Http\Controllers\api\v1\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CategoryApi;
use Illuminate\Database\QueryException;
use App\Traits\TraitModel;
use App\CategoryGroup;
use App\CategoryType;

class CategoriesApiController extends Controller
{
    use TraitModel;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function categoryGroups(Request $request)
    {
        $categorygroups = CategoryGroup::all();
        
        return response()->json([
            'message' => 'success',
            'data' => $categorygroups
        ]);
    }

    public function categoryTypes(Request $request)
    {
        $categorytypes = CategoryType::all();
        
        return response()->json([
            'message' => 'success',
            'data' => $categorytypes
        ]);
    }
    
     public function categories($page)
    {
        $categories = CategoryApi::paginate(10, ['*'], 'page', $page);
        return response()->json([
            'message' => 'success',
            'data' => $categories
        ]);
    }

    public function index()
    {
        $categories = CategoryApi::all();

        return response()->json([
            'message' => 'success',
            'data' => $categories
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
        $data = $request->all();


        $last_code = $this->get_last_code('category');


        $code = acc_code_generate($last_code, 8, 3);

        
        $rules=array(
            // 'email' => 'email|unique:customers,email',
            // 'code' => 'unique:customers,code',
            'name' => 'required|unique:categories,name',
        );


        if(isset($request->code)){
            $rules['code'] = 'required|unique:categories,code';
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

        $category = CategoryApi::create($data);

        return response()->json([
            'message' => 'Data Category Add Success',
            'data' => $category
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
    public function update(Request $request, CategoryApi $category)
    {
        $rules=array(
            // 'email' => 'email|unique:customers,email',
            // 'code' => 'unique:customers,code',
            'name' => 'required|unique:categories,name,'.$category->id,
            'code' => 'required|unique:categories,code,'.$category->id,
        );

        $validator=\Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            $messages=$validator->messages();
            $errors=$messages->all();
            return response()->json([
                'message' => $errors,
                'data' => $customer
            ]);
        }

        $category->update($request->all());

        return response()->json([
            'message' => 'Data Category Update Success',
            'data' => $category
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(CategoryApi $category)
    {
        
        try{
            
            $category->delete();
            return response()->json([
                'message' => 'Kategori berhasil di hapus',
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
