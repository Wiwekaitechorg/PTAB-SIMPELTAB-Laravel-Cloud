<?php

namespace App\Traits;

use App\Channel;
use App\wa_history;

trait WablasTrait
{
    public static function gantiFormat($nomorhp)
    {
        //Terlebih dahulu kita trim dl
        $nomorhp = trim($nomorhp);
        //bersihkan dari karakter yang tidak perlu
        $nomorhp = strip_tags($nomorhp);
        // Berishkan dari spasi
        $nomorhp = str_replace(" ", "", $nomorhp);
        // bersihkan dari bentuk seperti  (022) 66677788
        $nomorhp = str_replace("(", "", $nomorhp);
        // bersihkan dari format yang ada titik seperti 0811.222.333.4
        $nomorhp = str_replace(".", "", $nomorhp);

        //cek apakah mengandung karakter + dan 0-9
        if (!preg_match('/[^+0-9]/', trim($nomorhp))) {
            // cek apakah no hp karakter 1-3 adalah +62
            if (substr(trim($nomorhp), 0, 3) == '+62') {
                $nomorhp = trim($nomorhp);
            }
            // cek apakah no hp karakter 1 adalah 0
            elseif (substr($nomorhp, 0, 1) == '0') {
                $nomorhp = '62' . substr($nomorhp, 1);
            }
        }
        return $nomorhp;
    }

    public static function sendText($data = [])
    {
        // pilih channel start
        $channel =   Channel::selectRaw('channels.*')
            // ->join('channels', 'channels.id', '=', 'wa_histories.channel_id')
            // ->where('wa_histories.status', 'pending')
            ->where('channels.type', 'reguler')
            ->groupBy('channels.id')
            ->first();
        $token = $channel->token;
        // dd($channel);     
        $curl = curl_init();

        $payload = [
            "data" => $data,
            // "data" => [
            //     [
            //         'phone' => '6281236815960',
            //         'customer_id' => null,
            //         'message' => "Tesss",
            //         'template_id' => '',
            //         'status' => 'gagal',
            //         'created_at' => date('Y-m-d h:i:sa'),
            //         'updated_at' => date('Y-m-d h:i:sa')
            //     ]
            // ]
        ];
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                "Authorization: $token",
                "Content-Type: application/json",
            )
        );
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($curl, CURLOPT_URL, env('DOMAIN_SERVER_WABLAS') . "/api/v2/send-message");
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($curl);
        curl_close($curl);
        // print_r($result);
        // dd($result);
        return $result;
    }

    public static function checkOnline($token)
    {
        $curl = curl_init();
        // pilih channel start
        // $channel =   wa_history::selectRaw('count(wa_histories.id) as total, channels.*')
        //     ->join('channels', 'channels.id', '=', 'wa_histories.channel_id')
        //     // ->where('wa_histories.status', 'pending')
        //     ->groupBy('channels.id')
        //     ->orderBy('total', 'asc')
        //     ->first();
        // dd($channel);  
        $token = $token;
        curl_setopt($curl, CURLOPT_URL, "https://jogja.wablas.com/api/device/info?token=$token");
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    public static function resend($id)
    {
        // pilih channel start
        $channel =   wa_history::selectRaw('count(wa_histories.id) as total, channels.*')
            ->join('channels', 'channels.id', '=', 'wa_histories.channel_id')
            // ->where('wa_histories.status', 'pending')
            ->groupBy('channels.id')
            ->orderBy('total', 'asc')
            ->first();
        // dd($channel);  
        $curl = curl_init();
        $token = $channel->token;
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                "Authorization: $token",
            )
        );
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, "https://jogja.wablas.com/api/resend-message/$id");
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
    public static function rescan($token)
    {
        $token = $token;
        $scan = "https://jogja.wablas.com/api/device/scan?token=" . $token;
        return $scan;
    }
    public static function disconect()
    {
        $curl = curl_init();
        $token = env('SECURITY_TOKEN_WABLAS');
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                "Authorization: $token",
            )
        );
        curl_setopt($curl, CURLOPT_URL, "https://jogja.wablas.com/api/device/disconnect");
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    public static function changeNumber($phone)
    {
        $curl = curl_init();
        $token = env('SECURITY_TOKEN_WABLAS');
        $data = [
            'phone' => $phone,
        ];
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                "Authorization: $token",
            )
        );
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_URL, "https://jogja.wablas.com/api/device/change-number");
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    public static function sendFile($data = [])
    {
        // pilih channel start
        $channel =   wa_history::selectRaw('count(wa_histories.id) as total, channels.*')
            ->join('channels', 'channels.id', '=', 'wa_histories.channel_id')
            // ->where('wa_histories.status', 'pending')
            ->groupBy('channels.id')
            ->orderBy('total', 'asc')
            ->first();
        // dd($channel);  

        $curl = curl_init();
        $token = $channel->token;
        // $payload = $data;

        $payload =  [
            "data" => $data,
        ];
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                "Authorization: $token",
                "Content-Type: application/json"
            )
        );
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($curl, CURLOPT_URL,  "https://jogja.wablas.com/api/v2/send-document");
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
        // echo "<pre>";
        // print_r($result);
    }

    public static function sendImage($data = [])
    {
        // pilih channel start
        $channel =   wa_history::selectRaw('count(wa_histories.id) as total, channels.*')
            ->join('channels', 'channels.id', '=', 'wa_histories.channel_id')
            // ->where('wa_histories.status', 'pending')
            ->groupBy('channels.id')
            ->orderBy('total', 'asc')
            ->first();
        // dd($channel);  

        $curl = curl_init();
        $token = $channel->token;
        $payload =   [
            "data" => $data,
        ];
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                "Authorization: $token",
                "Content-Type: application/json"
            )
        );
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($curl, CURLOPT_URL,  "https://jogja.wablas.com/api/v2/send-image");
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
        // echo "<pre>";
        // print_r($result);
    }

    public static function sendVideo($data = [])
    {
        // pilih channel start
        $channel =   wa_history::selectRaw('count(wa_histories.id) as total, channels.*')
            ->join('channels', 'channels.id', '=', 'wa_histories.channel_id')
            // ->where('wa_histories.status', 'pending')
            ->groupBy('channels.id')
            ->orderBy('total', 'asc')
            ->first();
        // dd($channel);  
        $curl = curl_init();
        $token = $channel->token;
        $payload = [
            "data" => $data,
        ];
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                "Authorization: $token",
                "Content-Type: application/json"
            )
        );
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($curl, CURLOPT_URL,  "https://jogja.wablas.com/api/v2/send-video");
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
}
