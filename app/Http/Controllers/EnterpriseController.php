<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Validator;

class EnterpriseController extends Controller
{
    //显示企业信息
    public function show(Request $request){
        $arr = (Object) array();
        $o_user = $request->user();
        $user_id = $o_user->id;
        $arr->o_user = $o_user;
        $a = DB::table("personal_enterprise")->where("user_id",$user_id)->get();
        if(count($a)){
            $arr->user = $a[0];
        }
        else{
            $arr->user = "";
        }
        return response()->json($arr,200);
    }
    //插入企业信息
    public function store(Request $request){
        $user_id = $request->user()->id;
        $id = time() . md5(uniqid());
        DB::table("personal_enterprise")->where("user_id",$user_id)->delete();
        $request->merge(["create_time" => date("Y-m-d H:i:s"), "id" => $id, "user_id" => $user_id]);
        //Validator 检验
        $result = Validator::make($request->all(),[
            "name" => "required|Max:255",
            "email" => "required|E-Mail|Max:255",
            "code" => "required",
            "place" => "required",
            "prove" => "required",
        ],[
            "name.required" => "姓名不能为空",
            "email.required" => "邮箱不能为空",
            "email.e_mail" => "邮箱不合法",
            "code.required" => "信用代码或注册号不能为空",
            "place.required" => "办公地不能为空",
            "prove.required" => "认证不能为空",
        ]);
        if($result->fails()){
            return response()->json($result->errors(),400);
        }
        //上传图片
        $prove_up = new ProveUpload();
        $bo_prove = $prove_up->upload($request->get("prove"),"uploads/enterprise/");
        if(!$bo_prove){
            return response()->json(["prove" => ["认证失败"]],400);
        }
        $prove_url = "http://122.152.249.114/scs/public/".$bo_prove;
        $request["prove"] = $prove_url;
        //删除数据库原有的审核不通过的
        DB::table("personal_enterprise")->where("user_id",$user_id)->delete();
        //插入数据库
        DB::table("personal_enterprise")->insert([
            "id" => $request->get("id"),
            "name" => $request->get("name"),
            "phone" => $request->user()->name,
            "email" => $request->get("email"),
            "code" => $request->get("code"),
            "place" => $request->get("place"),
            "prove" => $request->get("prove"),
            "create_time" => $request->get("create_time"),
            "status" => "examine",
            "user_id" => $request->get("user_id")
        ]);
        return response()->json(["message" => "success"],200);
    }
    //删除个人信息（重新填写）
    public function destroy(Request $request){
        $a = DB::table("personal_enterprise")->where("user_id",$request->user()->id)->delete();
        if($a){
            return response()->json("success",200);
        }
        else{
            return response()->json(["message" => "删除失败"],400);
        }
    }
}
