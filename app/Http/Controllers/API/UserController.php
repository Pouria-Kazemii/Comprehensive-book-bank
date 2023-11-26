<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function authenticate(Request $request)
    {
        // $credentials = $request->only('email', 'password');
       $user = User::where('phone', $request->phone)->first();
        try {
            // if (!$token = JWTAuth::attempt($credentials)) {
            if (!$token = JWTAuth::fromUser($user)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
            try {
                $sms_code = rand(1000, 9999);
                $resultSendSms = $this->sendSmsCode($sms_code,$request->phone);
                return $resultSendSms;
            } catch (JWTException $e) {
                return response()->json(['error' => 'مشکل در ارسال پیامک'], 500);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'مشکل در ساخن توکن'], 500);
        }
        // return response()->json(compact('token'));
    }
    public function login(Request $request)
    {

        // $user = User::where('email', $request->email)->first();
        $user = User::where('phone', $request->phone)->first();
        if ($user != NULL) {
            if ($user->sms_code === $request->smsCode) {
                //todo get authenticated user here 
                if (!$token = JWTAuth::fromUser($user)) {
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
            ['message' => 'کد وارد شده نامعتبر می باشد.'],
            500
        );
    }

    protected function sendSmsCode($sms_code,$phone)
    {
        try {
            $user = User::where('phone', $phone)->first();

            // $user = Auth::user();
            $user->sms_code = $sms_code;
            $user->update();
            //send sms code
            $username = env('SMS_PANEL_USERNAME');
            $password = env('SMS_PANEL_PASSWORD');
            $from = env('SMS_PANEL_NUMBER');
            $content = urlencode("کد ورود به بانک جامع کتاب : $sms_code");
            $url = env('SMS_PANEL_URL') . "?from=$from&to=$user->phone&username=$username&password=$password&message=$content";
            $res = file_get_contents($url);
            if ($res) {
                return response()->json(['message' => 'ok'], 200);
            } else {
                return response()->json(['error' => 'مشکل در ارسال پیامک'], 500);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'مشکل در ارسال پیامک'], 500);
        }
    }

    // find
    public function find(Request $request)
    {
        return $this->lists($request);
    }

    public function lists(Request $request, $defaultWhere = true, $isNull = false, $where = "", $subjectTitle = "", $publisherName = "", $creatorName = "")
    {

        $searchText = (isset($request["searchText"]) && !empty($request["searchText"])) ? $request["searchText"] : "";
        $column = (isset($request["column"]) && !empty($request["column"])) ? $request["column"]['sortField'] : "name";
        $sortDirection = (isset($request["sortDirection"]) && !empty($request["sortDirection"])) ? $request["sortDirection"] : "asc";
        $currentPageNumber = (isset($request["page"]) && !empty($request["page"])) ? $request["page"] : 0;
        $data = null;
        $status = 404;
        $pageRows = (isset($request["perPage"])) && !empty($request["perPage"])  ? $request["perPage"] : 50;
        $totalRows = 0;
        $totalPages = 0;
        $offset = ($currentPageNumber - 1) * $pageRows;
        // DB::enableQueryLog();
        if (!$isNull) {
            // read users
            $users = User::orderBy($column, $sortDirection);
            if ($searchText != "") $users->where('name', 'like', "%$searchText%");
            if ($where != "") $users->whereRaw($where);
            $users = $users->skip($offset)->take($pageRows)->get();
            if ($users != null and count($users) > 0) {
                foreach ($users as $user) {
                    //
                    $data[] =
                        [
                            "id" => $user->id,
                            "name" => $user->name,
                            "phone" => $user->phone,
                            "email" => $user->email,
                        ];
                }
            }

            //
            $users = User::orderBy($column, $sortDirection);
            if ($searchText != "") $users->where('name', 'like', "%$searchText%");
            if ($where != "") $users->whereRaw($where);
            $countusers = $users->get();
            $totalRows =  count($countusers);
            $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $pageRows) : 0;
        }
        //  $query = DB::getQueryLog();
        // return $query;

        if ($data != null or $subjectTitle != "") $status = 200;

        // response
        return response()->json(
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => ["list" => $data, "currentPageNumber" => $currentPageNumber, "totalPages" => $totalPages, "pageRows" => $pageRows, "totalRows" => $totalRows]
            ],
            $status
        );
    }

    public function store(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:191',
                'phone' => 'required|string|max:11|min:11',
                'email' => 'required|email|max:191|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['validation_errors' => $validator->errors()->messages(), 'status' => 422]);
        } else {
            try {
                $user = User::create([
                    'name' => $request->get('name'),
                    'phone' => $request->get('phone'),
                    'email' => $request->get('email'),
                    'password' => Hash::make($request->get('password')),
                ]);
                return response()->json([
                    'message' => 'ok',
                    'status' => 200
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => $e->getMessage()
                ], 500);
            }
        }
    }
    public function info($userId)
    {
        $status = 404;
        $user = User::findOrFail($userId);
        if ($user != null) $status = 200;
        return response()->json(
            [
                "status" => $status,
                "message" => $status == 200 ? "ok" : "not found",
                "data" => $user
            ],
            $status
        );
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'phone' => 'required|string|min:11|max:11',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:6|confirmed',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['validation_errors' => $validator->errors()->messages(), 'status' => 422]);
        } else {
            $user->name = $request->get('name');
            $user->phone = $request->get('phone');
            $user->email = $request->get('email');
            if ($request->get('password') != '') {
                $user->password = Hash::make($request->get('password'));
            }
            try {
                $user->update();
                return response()->json(['message' => 'ok', 'status' => 200]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Something goes wrong while creating a user!!'
                ], 500);
            }
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'), 201);
    }

    public function getAuthenticatedUser()
    {
        try {

            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());
        }

        return response()->json(compact('user'));
    }
}
