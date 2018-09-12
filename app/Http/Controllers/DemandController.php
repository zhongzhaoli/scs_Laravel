<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\User;

class new_td{
}
class DemandController extends Controller
{
    public function index(Request $request){
        $a = DB::table("demand")->OrderBy("create_time","desc")->paginate(7);
        return $a;
    }
    public function store(Request $request){
        $a = $request->file();
        $arr = [];
        foreach ($a as $c => $key){
            $new_obj = new new_td();
            $filedir="uploads/demand/"; //2、定义图片上传路径
            $imagesName=$key->getClientOriginalName(); //3、获取上传图片的文件名
            $extension = $key -> getClientOriginalExtension(); //4、获取上传图片的后缀名
            $newImagesName=md5(time()) . rand(1, 9999999).".".$extension;//5、重新命名上传文件名字
            $key->move($filedir,$newImagesName); //6、使用move方法移动文件.
            $new_obj->type = explode("|",$c)[0];
            $new_obj->path = "http://122.152.249.114/scs/public/".$filedir.$newImagesName;
            array_push($arr,$new_obj);
        }
        $id = time() . md5(uniqid());
        $cr_time = date("Y-m-d H:i:s");
        $user_id = $request->user()->id;
        $text = $request->get("text");
        $d = DB::table("demand")->insert([
            "id" => $id,
            "user_id" => $user_id,
            "text" => $text,
            "media_arr" => json_encode($arr),
            "create_time" => $cr_time,
            "user_img" => User::find($user_id)->user_img,
            "user_nickname" => User::find($user_id)->nickname,
        ]);
        if($d) {
            return response()->json(["message" => "success"], 200);
        }
        else{
            return response()->json(["message" => "发送动态失败，请稍候再试"],400);
        }
    }
}
