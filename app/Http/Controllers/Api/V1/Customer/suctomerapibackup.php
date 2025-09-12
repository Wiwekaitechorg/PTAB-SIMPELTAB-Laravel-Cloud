<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CustomerApi;
use App\Http\Requests\StoreApiCustomerRegisterPublicRequest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Traits\TraitModel;

class CustomersApiController extends Controller
{
    use TraitModel;

    public function login(Request $request)
    {
        try {
            $customer = CustomerApi::where('email', request('email'))->first();

            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if(Hash::check($request->password, $customer->password)){
                 $this->smsApi($customer->phone, $request->OTP);
                Auth::login($customer);
                $token = Auth::user()->createToken('authToken')->accessToken;

                // $data = [
                //     'success' =>  true,
                //     'message' => 'success login',
                //     'token' => $token,
                //     'data' => $customer,
                // ];
                return response()->json([
                    'success' =>  true,
                    'message' => 'success login',
                    'token' => $token,
                    'data' => $customer,
                ]);
            }else{
                return response()->json([
                    'success' =>  false,
                    'failed' => 'Email Atau Password Yang Di masukkan Salah',
                ]);
                // $data =[
                //     'message' => 'Email Atau Password Yang Di masukkan Salah',
                // ];
            }

        } catch (QueryException $e) {
            return response()->json([
                'message' => $e->message
            ]);
            // $data = [
            //     'message' => $e->message
            // ];
        }

        // return response()->json($data);
    }

    public function register_public(StoreApiCustomerRegisterPublicRequest $request)
    {

        $last_code = $this->get_last_code('customer');

        $code = acc_code_generate($last_code, 8, 3);
        
        $data = $request->all();

        $data['code'] = $code;
        
        $data['type'] = 'public';
        $data['password'] =  bcrypt($request->passwordNew);
        $customer = CustomerAPI::create($data);

        $token= $customer->createToken('appToken')->accessToken;

        return response()->json([
            'message' => 'Registrasi Berhasil',
            'token' => $token,
            'data' => $customer
        ]);
    }

    function smsApi($phone, $OTP)
    {
        date_default_timezone_set("Asia/Singapore");
        $date = date("Y-m-d H:i:s");
        $number = "+62" . ltrim($phone, '0');
        $message = '#plg OTP : ' . $OTP;
        $md5_str = "1f4a449a85" . $date . $number . $message;
        $md5 = md5($md5_str);
        $data = array(
            'outbox' => '',
            'date' => $date,
            'number' => $number,
            'message' => $message,
            'md5' => $md5,
        );

        $url = 'https://tab-jdol.com/gs-gateway-sms-v3/api.php';

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);
    }
}
