<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Validator;

class PersonalController extends Controller
{
    //插入个人信息
   public function store(Request $request){
       $user_id = $request->user()->id;
       $del_count = DB::table("personal_user")->where("user_id",$user_id)->delete();
       //活动代码开始---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
       $event = new EventController();
       if($event->event_online("1536140152a1e660bc1e9e9c594148534a22666d55")){ //1536140152a1e660bc1e9e9c594148534a22666d55 为 活动ID
            $event->new_user($del_count,$request->user()->id);
       }
       //活动代码结束---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
       $id = time() . md5(uniqid());
       $request->merge(["create_time" => date("Y-m-d H:i:s"), "id" => $id, "user_id" => $user_id]);
       //Validator 检验
       $result = Validator::make($request->all(),[
           "name" => "required|Max:255",
           "height" => "required|Integer",
           "weight" => "required|Integer",
           "age" => "required|Integer",
           "email" => "required|E-Mail|Max:255",
           "schoolcode" => "required|max:255",
           "class" => "required|max:255",
           "sex" => "required|max:255",
           "intention" => "required|max:255",
           "prove" => "required",
           "wechat" => "required|max:255",
           "school" => "required"
       ],[
            "name.required" => "姓名不能为空",
            "sex.required" => "性别不能为空",
            "height.required" => "身高不能为空",
            "height.integer" => "身高不合法",
            "weight.required" => "体重不能为空",
            "weight.integer" => "体重不合法",
            "age.required" => "年龄不能为空",
            "age.integer" => "年龄不合法",
            "email.required" => "邮箱不能为空",
            "email.e_mail" => "邮箱不合法",
            "schoolcode.required" => "学号不能为空",
            "class.required" => "班级不能为空",
            "sex.required" => "性别不能为空",
            "intention.required" => "意向不能为空",
            "prove.required" => "认证不能为空",
            "wechat.required" => "微信号不能为空",
            "school.required" => "学校不能为空"
       ]);
       if($result->fails()){
           return response()->json($result->errors(),400);
       }
       //上传图片
       $prove_up = new ProveUpload();
       $bo_prove = $prove_up->upload($request->get("prove"),"uploads/");
       if(!$bo_prove){
           return response()->json(["prove" => ["认证失败"]],400);
       }
       $prove_url = "http://122.152.249.114/scs/public/".$bo_prove;
       $request["prove"] = $prove_url;
       //删除数据库原有的审核不通过的
       DB::table("personal_user")->where("user_id",$user_id)->delete();
       //插入数据库
       DB::table("personal_user")->insert([
           "id" => $request->get("id"),
           "name" => $request->get("name"),
           "height" => $request->get("height"),
           "weight" => $request->get("weight"),
           "age" => $request->get("age"),
           "phone" => $request->user()->name,
           "email" => $request->get("email"),
           "schoolcode" => $request->get("schoolcode"),
           "class" => $request->get("class"),
           "sex" => $request->get("sex"),
           "intention" => $request->get("intention"),
           "prove" => $request->get("prove"),
           "create_time" => $request->get("create_time"),
           "wechat" => $request->get("wechat"),
           "status" => "examine",
           "user_id" => $request->get("user_id")
       ]);
       return response()->json(["message" => "success"],200);
   }
}
