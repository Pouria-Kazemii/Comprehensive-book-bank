<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function __construct()
    {
    }

    // login
    public function login(Request $request)
    {
        $status = 401;
        $message = "error";
        $data = null;
        $email = $request->input("email", "");
        $password = $request->input("password", "");

        if($email != "" and $password != "")
        {
//            $user= User::where('email', '=', $email)->first();
//            if($user != null)
//            {
//                if(password_verify($password, $user->password))
//                {
            try
            {
                $token = auth()->attempt(["email" => $email, "password" => $password]);
                if(!$token)
                {
                    $status = 400;
                    $message = "Login credentials are invalid.";
                }
                else
                {
                    $status = 200;
                    $message = "ok";
                    $data = [/*"nameFamily" => $user->name,*/ "token" => $token];
                }
            }
            catch(JWTException $e)
            {
                $status = 500;
                $message = "Could not create token.";
            }
//                }
//            }
        }

        return response()->json
        (
            [
                "status" => $status,
                "message" => $message,
                "data" => $data
            ],
            $status
        );
    }
}
