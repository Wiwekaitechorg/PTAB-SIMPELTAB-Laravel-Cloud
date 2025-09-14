<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyTestRequest;
use App\Test;
use App\Student;
use App\Subject;
class TestsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        abort_unless(\Gate::allows('test_access'), 403);
        $tests = Test::all();
        $student = Student::all();
        $subject = Subject::all();
        return view('admin.tests.index', compact('tests','student','subject'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $students = Student::all();
        $subjects = Subject::all();
        return view('admin.tests.create',compact('students','subjects'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(\Gate::allows('test_create'), 403);
        $test = Test::create($request->all());
    
        return redirect()->route('admin.tests.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        abort_unless(\Gate::allows('test_show'), 403);
        $students = Student::all();
        $subjects = Subject::all();
        $test = Test::find($id);
        return view('admin.tests.show', compact('students','subjects','test'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        abort_unless(\Gate::allows('test_edit'), 403);
        $students = Student::all();
        $subjects = Subject::all();
        $test = Test::find($id);
        return view('admin.tests.edit', compact('students','subjects','test'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Test $test)
    {
        abort_unless(\Gate::allows('test_edit'), 403);
        $test->update($request->all());
        return redirect()->route('admin.tests.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Test $test)
    {
        abort_unless(\Gate::allows('test_delete'), 403);
        $test->delete();
        return back();
    }
    public function massDestroy(MassDestroyTestRequest $request)
    {
        Test::whereIn('id', request('ids'))->delete();
        return response(null, 204);
    }
}
