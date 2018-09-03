<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Redis;
use DB;

class PassportController extends Controller
{
    //成功状态码
    public $successStatus = 200;

    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login()
    {
        //数据库验证这个账号密码
        if (Auth::attempt(['name' => request('phone'), 'password' => request('password')])) {
            //获取用户信息
            $user = Auth::user();
            //获取token
            $success['token'] = $user->createToken('MyApp')->accessToken;
            //返回信息
            return response()->json(['success' => $success, 'role' => $user->role], $this->successStatus);
        } else {
            //返回错误信息
            return response()->json(['message' => '账号密码错误'], 400);
        }
    }

    public function admin_login()
    {
        if (Auth::attempt(['name' => request('phone'), 'password' => request('password')])) {
            $user = Auth::user();
            if ($user->role === "admin") {
                //获取token
                $success['token'] = $user->createToken('MyApp')->accessToken;
                //返回信息
                return response()->json(['success' => $success], $this->successStatus);
            } else {
                //返回错误信息
                return response()->json(['message' => '你不是管理员'], 400);
            }
        } else {
            //返回错误信息
            return response()->json(['message' => '账号密码错误'], 400);
        }
    }

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $a = DB::table("users")->where("name", $request->get("name"))->get();
        if (count($a)) {
            return response()->json(["message" => "手机号已注册"], 400);
        }
        //验证
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required',
            'c_password' => 'required',
        ]);
        if ($request->get("password") !== $request->get("c_password")) {
            return response()->json(['message' => "两次密码不同"], 400);
        }
        //验证错误
        if ($validator->fails()) {
            return response()->json(['message' => '必填项不能为空'], 400);
        }
        $a = DB::table("users")->where("name", $request->get("phone"))->get();
        if (count($a)) {
            return "手机号已被注册";
        } else {
            if (Redis::get("register_" . $request->get("name"))) {
                if (Redis::get("register_" . $request->get("name")) == $request->get("code")) {
                    //获取注册信息
                    $input = $request->all();
                    //自己添加的角色,用户信息id
                    $input['role'] = $request->get("role");
                    $input['nickname'] = $request->get("name");
                    //加密密码
                    $input['password'] = bcrypt($input['password']);
                    //添加进数据库
                    $user = User::create($input);
                    //获取token
                    $success['token'] = $user->createToken('MyApp')->accessToken;
                    //获取用户名
                    $success['name'] = $user->name;
                    //返回信息
                    return response()->json(['success' => $success], $this->successStatus);
                } else {
                    return response()->json(['message' => '手机验证码错误'], 400);
                }
            } else {
                return response()->json(['message' => '先获取验证码'], 400);
            }
        }

    }

    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function getDetails()
    {
        //获取用户信息
        $user = Auth::user();
        //返回用户信息
        return response()->json(['success' => $user], $this->successStatus);
    }

    public function get_code(Request $request)
    {
        $a = new PhoneCode();
        $b = $a->phone_code($request->get("phone"), "register_", "146507");
        if ($b == "success") {
            return response()->json(["message" => $b], 200);
        } else {
            return response()->json(["message" => $b], 400);
        }
    }
    public function reset_pass(Request $request){
        if ($request->get("password") != $request->get("c_password")) {
            return response()->json(['message' => "两次密码不同"], 400);
        }
        if(Redis::get("can_reset_".$request->get("phone"))){
            DB::table("users")->where("name",$request->get("phone"))->update(["password" => bcrypt($request->get("password"))]);
            Redis::del("can_reset_".$request->get("phone"));
            return response()->json(["message" => "success"],200);
        }
        else{
            return response()->json(["message" => "请先进行手机验证"],400);
        }
    }
}
