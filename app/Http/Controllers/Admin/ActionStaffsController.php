<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Action;
use App\Staff;

class ActionStaffsController extends Controller
{

    public function index($actionId)
    {
        $action = Action::findOrFail($actionId);

        // $staffs = $action->staff;

       return view('admin.actionstaffs.index', compact('action'));
        
    }

    public function create($actionId)
    {
        $action = Action::findOrFail($actionId);

        $action_staffs = Action::where('id', $actionId)->with('staff')->first();

        $staffs = Staff::where('dapertement_id', $action->dapertement_id)->get();

        return view('admin.actionstaffs.create', compact('actionId', 'staffs', 'action_staffs'));
    }
}
