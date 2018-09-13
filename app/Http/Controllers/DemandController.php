<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\User;

class new_td{
}
class DemandController extends Controller
{
    //自定义分页。获取所有动态
    public function index(Request $request){
          //url参数
          $page = intval(($request->get('page')) ? $request->get("page") : 1);
          //一页多少条数据
          $page_sj_num = 7;
          //从第几条开始
          $from_sj = ($page -1) * 7;
          //到第几条结束
          $to_sj = $page * $page_sj_num;
          $a = DB::table("demand")->OrderBy("create_time","desc")->count();
          $has_page = ceil($a / $page_sj_num);
          $new_obj = new new_td();
          $new_obj->current_page = $page;
          $new_obj->last_page = $has_page;
          //limit查找
          $b = DB::select("select * from demand ORDER BY create_time DESC limit ".$from_sj.",".$to_sj.";");
          if(count($b)) {
              for ($i = 0; $i < count($b); $i++) {
                  $b[$i]->user = User::find($b[$i]->user_id);
                  $b[$i]->like = DB::table("demand_like")->where("demand_id",$b[$i]->id)->count();
                  $b[$i]->you_like = (count(DB::table("demand_like")->where(["user_id" => $request->user()->id, "demand_id" => $b[$i]->id])->get())) ? 'true' : 'false';
              }
          }
          $new_obj->data = $b;
          return response()->json($new_obj,200);
    }
    //我的动态
    public function my_demand(Request $request){
        //url参数
        $page = intval(($request->get('page')) ? $request->get("page") : 1);
        //一页多少条数据
        $page_sj_num = 7;
        //从第几条开始
        $from_sj = ($page -1) * 7;
        //到第几条结束
        $to_sj = $page * $page_sj_num;
        $a = DB::table("demand")->where("user_id",$request->user()->id)->OrderBy("create_time","desc")->count();
        $has_page = ceil($a / $page_sj_num);
        $new_obj = new new_td();
        $new_obj->current_page = $page;
        $new_obj->last_page = $has_page;
        //limit查找
        $b = DB::select("select * from demand where user_id = ".$request->user()->id." ORDER BY create_time DESC limit ".$from_sj.",".$to_sj.";");
        if(count($b)) {
            for ($i = 0; $i < count($b); $i++) {
                $b[$i]->user = User::find($b[$i]->user_id);
                $b[$i]->like = DB::table("demand_like")->where("demand_id",$b[$i]->id)->count();
            }
        }
        $new_obj->data = $b;
        return response()->json($new_obj,200);
    }
    //插入动态
    public function store(Request $request){
        $a = $request->file();
        if($a == "" && $request->get("text") == ""){
            return response()->json(["message" => "请编辑你要发的动态"],400);
        }
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
        ]);
        if($d) {
            return response()->json(["message" => "success"], 200);
        }
        else{
            return response()->json(["message" => "发送动态失败，请稍候再试"],400);
        }
    }
    //动态点赞
    public function demand_like(Request $request,$de_id){
        $a = DB::table("demand_like")->where(["user_id" => $request->user()->id,"demand_id" => $de_id])->get();
        if(count($a)){
            return response()->json(["message" => "你已经赞过了"],400);
        }
        DB::table("demand_like")->insert([
            "user_id" => $request->user()->id,
            "demand_id" => $de_id,
            "create_time" => date("Y-m-d H:i:s")
        ]);
        return response()->json(["message" => "success"],200);
    }
    public function del_demand($id){
        DB::table("demand")->where("id",$id)->delete();
        DB::table("demand_like")->where("demand_id",$id)->delete();
        return response()->json(["message" => "success"],200);
    }
}
