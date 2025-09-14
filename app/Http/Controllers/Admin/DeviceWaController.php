<?php

namespace App\Http\Controllers\Admin;

use App\Channel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\WablasTrait;
use App\Traits\WablasAreaTrait;
use Doctrine\DBAL\Schema\View;

class DeviceWaController extends Controller
{
    public function index(Request $request)
    {
        // abort_unless(\Gate::allows('user_access'), 403);

        $channelList = Channel::get();
        if ($request->channel != "") {
            $channel = Channel::where('id', $request->channel)->first();
            $scan = WablasTrait::rescan($channel->token);
            $data = WablasTrait::checkOnline($channel->token);
        } else {
            $channel = Channel::where('type', 'reguler')->first();
            $scan = WablasTrait::rescan($channel->token);
            $data = WablasTrait::checkOnline($channel->token);
        }
        // dd($channel, $data);
        // return $channel->token;
        

        // dd(json_decode($data)->data);
        if (json_decode($data)->status) {
            $deviceWa = json_decode($data)->data;
        } else {
            dd('Data Kosong');
        }
        // dd(json_decode($data)->status);

        return view('admin.whatsapp.device.index', compact('deviceWa', 'scan', 'channelList'));
    }
    public function disconect()
    {
        $disconnect = WablasTrait::disconect();
        dd($disconnect);
        return redirect()->back();
    }

    public function create(Request $request)
    {
        $token = $request->token;
        // dd($token);
        return View('admin.whatsapp.device.create', compact('token'));
    }
    public function store(Request $request)
    {
        // dd($phone, $token);
        $phone = WablasAreaTrait::changeNumber($request->phone, $request->token);
        return redirect()->route('admin.devicewa.index');
    }
}
