<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function __construct()
    {
    }

    // login
    public function auth(Request $request)
    {
        $status = 401;
        $message = "error";
        $data = null;
        $email = $request->input("email", "");
        $password = $request->input("password", "");

        $credentials = $request->only('email', 'password');
        // $user = User::where('email', $request->email)->first();
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
            // if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
            try {
                $sms_code = rand(1000, 9999);
                $resultSendSms =$this->sendSmsCode($sms_code);
                return $resultSendSms;
                
              
            } catch (JWTException $e) {
                return response()->json(['error' => 'مشکل در ارسال پیامک'], 500);

            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'مشکل در ساخن توکن'], 500);
        }


    }
    public function login(Request $request)
    {
     
        $user = User::where('email', $request->email)->first();
        if($user != NULL){
            if ($user->sms_code === $request->smsCode) {
                //todo get authenticated user here 
                if (! $token = JWTAuth::fromUser($user)) {
                    return response()->json(['error' => 'invalid_credentials'], 400);
                }
                $user->sms_code = NULL;
                $user->update();
                return response()->json(
                    [
                        "message" => 'ok',
                        "token" => $token
                    ],
                    200
                );
            }
        }
      
        return response()->json(
            ['message'=> 'کد وارد شده نامعتبر می باشد.'], 
            500
        );
  
    }

    protected function sendSmsCode($sms_code)
    {
        try {
            $user = Auth::user();
            $user->sms_code = $sms_code;
            $user->update();
             //send sms code
             $username= env('SMS_PANEL_USERNAME');
             $password= env('SMS_PANEL_PASSWORD');
             $from= env('SMS_PANEL_NUMBER');
             $content = urlencode("کد ورود به بانک جامع کتاب : $sms_code" );
             $url = env('SMS_PANEL_URL')."?from=$from&to=$user->phone&username=$username&password=$password&message=$content";
             $res = file_get_contents($url);
            if($res){
                return response()->json(['message' => 'ok'], 200);
            }else{
                return response()->json(['error' => 'مشکل در ارسال پیامک'], 500);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'مشکل در ارسال پیامک'], 500);
        }
    }
}
