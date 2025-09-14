<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\wa_history;

class WaBlastCallbackApiController extends Controller
{
    //Dari Api
    public function callback()
    {
        $content = json_decode(file_get_contents('php://input'), true);

        $id = $content['id'];
        $status = $content['status'];
        $phone = $content['phone'];
        $note = $content['note'];
        $sender = $content['sender'];
        $deviceId = $content['deviceId'];

        $data = [
            'status' => $status,
        ];
        wa_history::where('id_wa', $id)->update($data);
    }
}
