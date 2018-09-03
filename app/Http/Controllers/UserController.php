<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Support\Facades\Redis;
use App\Personal;


class UserController extends Controller
{
    //获取用户信息
    public function index(Request $request){
        $arr = (Object)array();
        $o_user = $request->user();
        $user_id = $o_user->id;
        $arr->o_user = $o_user;
        $a = User::find($user_id)->to_personal;
        if ($a) {
            $arr->user = $a;
        } else {
            $arr->user = "";
        }
        return response()->json($arr,200);
    }
    //获取用户角色
    public function user_role(Request $request){
        $a = DB::table("users")->where("id",$request->user()->id)->get();
	return response(["role" => $a[0]->role],200);
    }
    //兼职状态
    public function job_status(Request $request){
        $want = $request->get("job_status");
        if($want == "in"){
            return response()->json(['message' => '无权修改至此状态'],400);
        }
        $user_id = $request->user()->id;
        $sta_j = DB::table("personal_user")->where("user_id",$user_id)->select("job_status")->get()[0]->job_status;
        if($sta_j === "in"){
            return response()->json(["message" => "你正在兼职中,查看你的兼职"],400);
        }
        $a = DB::table("personal_user")->where("user_id",$user_id)->get();
        if(count($a)){
            if($a[0]->job_status == "in"){
                return response(['message' => '您正在兼职'],400);
            }
            else{
                $a = DB::table("personal_user")->where("id",$a[0]->id)->update(["job_status" => $want]);
                return response()->json(["message" => "success"], 200);
            }
        }
        else{
            return response()->json(['message' => '请先完善信息'],400);
        }
    }
    //忘记密码验证码
    public function reset_code(Request $request){
        $a = new PhoneCode();
        $b = $a->phone_code($request->get('phone'), "reset_", "146507");
        if ($b == "success") {
            return response()->json(["message" => "success"], 200);
        }
        else{
            return response()->json(["message" => $b], 400);
        }
    }
    //忘记密码记录
    public function reset_mes(Request $request){
        if(Redis::get("reset_".$request->get("phone"))){
            if(Redis::get("reset_".$request->get("phone")) != $request->get("code")){
                return response()->json(["message" => "验证码错误"],400);
            }
            else{
                Redis::setex("can_reset_".$request->get("phone"),300,"allow");
                return response()->json(["message" => "success"],200);
            }
        }
        else{
            return response()->json(["message" => "请先发送验证码"],400);
        }
    }
    //上传图片
    public function user_img(Request $request){
        $prove_up = new ProveUpload();
        $bo_prove = $prove_up->upload($request->get("img"),"uploads/user/");
        if(!$bo_prove){
            return response()->json(["message" => "头像上传失败"],400);
        }
        DB::table("users")->where("id",$request->user()->id)->update(["user_img" => "http://122.152.249.114/scs/public/".$bo_prove]);
        return response()->json(["message" => "success"],200);
    }
    //昵称修改
    public function user_nickname(Request $request){
        DB::table("users")->where("id",$request->user()->id)->update(["nickname" => $request->get("nickname")]);
        return response()->json(["message" => "success"],200);
    }
}
