<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\User;
use Validator;

class RecruitmentController extends Controller
{
    //所有失物招领
    public function index(Request $request){
        $a = DB::table("recruitment")->where("over","!=","1")->OrderBy("to_scs","desc")->OrderBy("create_time","desc")->get();
        foreach ($a as $i => $key){
            $a[$i]->user = User::find($key->user_id);
            if($request->user()){
                if($request->user()->id = $a[$i]->user_id){
                    $a[$i]->is_my = 1;
                }
                else{
                    $a[$i]->is_my = 0;
                }
            }
            else{
                $a[$i]->is_my = 0;
            }
        }
        return response()->json($a,200);
    }
    //我的失物招领
    public function my(Request $request){
        $user_id = $request->user()->id;
        $a = DB::table("recruitment")->where("user_id",$user_id)->get();
        return $a;
    }
    //删除我的失物招领
    public function destory($id,Request $request){
        $user_id = $request->user()->id;
        $a = DB::table("recruitment")->where(["user_id" => $user_id, "id" => $id])->delete();
        if($a){
            return response()->json(["message" => "删除成功"],200);
        }
        else{
            return response()->json(["message" => "删除失败"],400);
        }
    }
    //插入
    public function store(Request $request){
        $arr = [];
        $id = time() . md5(uniqid());
        $cre_time = date("Y-m-d H:i:s");
        $img_list = $request->get("img_list");
        foreach($img_list as $i){
            $prove_up = new ProveUpload();
            $bo_prove = $prove_up->upload($i,"uploads/recruitment/");
            if($bo_prove) {
                array_push($arr, "http://122.152.249.114/scs/public/" . $bo_prove);
            }
            else{
                return response()->json(["message" => "图片上传失败"],400);
            }
        }
        if($request->get("type") == 2){
            if(!$request->get("find_address")){
                return response()->json(["message" => "领取地址不能为空"],400);
            }
        }
        //Validator 检验
        $result = Validator::make($request->all(),[
            "text" => "required",
            "type" => "required|Integer",
            "find_address" => "max:30",
            "classify" => "required"
        ],[
            "text.required" => "描述不能为空",
            "type.required" => "类型不能为空",
            "type.integer" => "类型不合法",
            "find_address.max" => "地址过长",
            "classify" => "类型不能为空"
        ]);
        if($result->fails()){
            return response()->json($result->errors(),400);
        }
        DB::table("recruitment")->insert([
            "id" => $id,
            "create_time" => $cre_time,
            "user_id" => $request->user()->id,
            "text" => $request->get("text"),
            "img_list" => json_encode($arr),
            "type" => $request->get("type"),
            "classify" => $request->get("classify"),
            "find_address" => $request->get("find_address")
        ]);
        return response()->json(["message" => "success"],200);
    }
    //完成
    public function over($id, Request $request){
        $user_id = $request->user()->id;
        $a = DB::table("recruitment")->where(["id" => $id, "user_id" => $user_id])->update(["over" => "1"]);
        if($a){
            return response()->json(["message" => "success"],200);
        }
        else{
            return response()->json(["message" => "失败"],400);
        }
    }
    //分类查询
    public function condition(Request $request){
        $a = "";
        if($request->get("classify") == "all"){
            $a = DB::table("recruitment")->where("over","!=","1")->OrderBy("to_scs","desc")->OrderBy("create_time","desc")->get();
        }
        else{
            $a = DB::table("recruitment")->where("classify",$request->get("classify"))->where("over","!=","1")->OrderBy("to_scs","desc")->OrderBy("create_time","desc")->get();
        }
        foreach ($a as $i => $key){
            $a[$i]->user = User::find($key->user_id);
            $a[$i]->is_my = 0;
        }
        return response()->json($a,200);
    }
}
