<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\User;
use Validator;

class RecruitmentController extends Controller
{
    public function index(){
        $a = DB::table("recruitment")->get();
        foreach ($a as $i => $key){
            $a[$i]->user = User::find($key->user_id);
        }
        return response()->json($a,200);
    }
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
        //Validator 检验
        $result = Validator::make($request->all(),[
            "text" => "required",
            "type" => "required|Integer",
        ],[
            "text.required" => "描述不能为空",
            "type.required" => "类型不能为空",
            "type.integer" => "类型不合法",
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
            "type" => $request->get("type")
        ]);
        return response()->json(["message" => "success"],200);
    }
}
