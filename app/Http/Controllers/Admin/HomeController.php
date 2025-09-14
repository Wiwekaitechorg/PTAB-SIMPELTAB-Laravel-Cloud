<?php

namespace App\Http\Controllers\Admin;

class HomeController
{
    public function index()
    {

        // Send Message 
        // $my_apikey = 'E5ZBKLQ3NWRM3953J8V2';
        // $destination = ['+6281236815960', '+6281236815960'];
        // $message = "cek keberhasilan";

        // $api_url = "http://panel.apiwha.com/send_message.php";
        // $api_url .= "?apikey=" . urlencode($my_apikey);
        // $api_url .= "&number=" . urlencode($destination);
        // $api_url .= "&text=" . urlencode($message);
        // // $api_url .= "&custom_data=" . urlencode($custom_data);
        // $my_result_object = json_decode(file_get_contents($api_url, false));
        // //echo "<br>Result: ". $my_result_object->success; 
        // //echo "<br>Description: ". $my_result_object->description; 
        // //echo "<br>Code: ". $my_result_object->result_code;
        // dd($my_result_object);
        return view('home');



        // $key_demo = 'db63f52c1a00d33cf143524083dd3ffd025d672e255cc688';
        // $url = 'http://45.77.34.32:8000/demo/send_message';
        // $data = array(
        //     "phone_no" => '+6281236815960',
        //     "key"     => $key_demo,
        //     "message" => 'Tes keberhasilan, untuk info lebih lanjut silahkan datang ke kantor'
        // );

        // $data_string = json_encode($data, 1);

        // $ch = curl_init($url);
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_VERBOSE, 0);
        // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        // curl_setopt($ch, CURLOPT_TIMEOUT, 360);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        //     'Content-Type: application/json',
        //     'Content-Length: ' . strlen($data_string),
        //     'Authorization: Basic dXNtYW5ydWJpYW50b3JvcW9kcnFvZHJiZWV3b293YToyNjM3NmVkeXV3OWUwcmkzNDl1ZA=='
        // ));
        // echo $res = curl_exec($ch);
        // curl_close($ch);
    }
}
