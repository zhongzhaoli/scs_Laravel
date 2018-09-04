<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Evaluate;
class str{};
class EvaluateController extends Controller
{

    //全部评价
    public function index(){
        $a = DB::table("evaluate")->get();
        return $a;
    }
    public function show($evaluate_id){
        $a = DB::table("evaluate")->where("id",$evaluate_id)->get();
        if(count($a)){
            $a[0]->user_img = DB::table("users")->where("id",$a[0]->user_id)->select("user_img")->get()[0]->user_img;
            $a[0]->user_name = DB::table("personal_enterprise")->where("user_id",$a[0]->user_id)->select('name')->get()[0]->name;
            return response()->json($a,200);
        }
        else{
            return response()->json(["message" => "没有这条评价"],400);
        }
    }
    public function admin_evaluate_index(){
        $a = DB::table("evaluate")->get();
        for($i = 0; $i < count($a); $i++){
            $b = DB::table("index_evaluate")->where("evaluate_id",$a[$i]->id)->get();
            (count($b)) ? $a[$i]->status = 1 : $a[$i]->status = 2;
        }
        return $a;
    }
    //插入评价(给与学生好评差评)
    public function store(Request $request,$id){
        $user_id = $request->user()->id;
        $a = DB::table("evaluate")->where("job_id",$id)->get();
        if(count($a)){
            return response()->json(["message" => "您已经评价过了"],400);
        }
        if(!$request->get("text")){
            return response()->json(["message" => "内容不能为空"],400);
        }
        DB::table("evaluate")->insert([
            "id" => time() . md5(uniqid()),
            "user_id" => $user_id,
            "job_id" => $id,
            "text" => $request->get("text"),
            "type" => $request->get("type"),
            "create_time" => date("Y-m-d H:i:s")
        ]);
        //好评
        for($i = 0; $i < count($request->get("good")); $i++){
            DB::table("evaluate_student")->insert([
                "type" => "good",
                "user_id" => $request->get("good")[$i],
                "job_id" => $id,
                "create_time" => date("Y-m-d H:i:s")
            ]);
            $user_all_arr = DB::table("users")->where("id",$request->get("good")[$i])->get()[0];
            //经验等级增加
            $jy = $user_all_arr->experience + 15;
            $dj = $user_all_arr->level;
            $admin_fun = new AdminController();
            $ret_le = $admin_fun->level_up($dj,$jy);
            DB::table("users")->where("id",$request->get("good")[$i])->update(["level" => $ret_le["level"],"experience" => $ret_le["experience"]]);
        }
        //差评
        for($i = 0; $i < count($request->get("bad")); $i++){
            DB::table("evaluate_student")->insert([
                "type" => "bad",
                "user_id" => $request->get("bad")[$i],
                "job_id" => $id,
                "create_time" => date("Y-m-d H:i:s")
            ]);
            $user_all_arr = DB::table("users")->where("id",$request->get("bad")[$i])->get()[0];
            //积分递减
            DB::table("users")->where("id",$request->get("bad")[$i])->decrement("integral",5);
            //信用修改
            $xy = $user_all_arr->credit;
            if($xy >= 40){
                DB::table("users")->where("id",$request->get("bad")[$i])->update(["credit" => $xy - 30]);
            }
            else{
                DB::table("users")->where("id",$request->get("bad")[$i])->update(["credit" => 0]);
            }
            //经验等级
            $jy = $user_all_arr->experience - 20;
            $dj = $user_all_arr->level;
            $admin_fun = new AdminController();
            $ret_le = $admin_fun->level_up($dj,$jy);
            DB::table("users")->where("id",$request->get("bad")[$i])->update(["level" => $ret_le["level"],"experience" => $ret_le["experience"]]);
        }
        //中评
        for($i = 0; $i < count($request->get("review")); $i++) {
            DB::table("evaluate_student")->insert([
                "type" => "review",
                "user_id" => $request->get("review")[$i],
                "job_id" => $id,
                "create_time" => date("Y-m-d H:i:s")
            ]);
            //啥也不用加
        }
        return response()->json(["message" => "success"],200);
    }
    //更改展示评价
    public function change_evaluate_index(Request $request){
        if(count($request->get('arr')) > 5){
            return response()->json(["message" => "不能展示大于五条评论"],400);
        }
        DB::table("index_evaluate")->delete();
        for($i = 0; $i < count($request->get('arr')); $i++){
            DB::table("index_evaluate")->insert(["id" => time() . md5(uniqid()), "evaluate_id" => $request->get('arr')[$i]]);
        }
        return response()->json(["message" => "success"],200);
    }
    //首页获取评论数据
    public function index_show_evaluate(){
        $a = DB::table("index_evaluate")->get();
        for($i = 0; $i < count($a); $i++){
            $a[$i]->evaluate = Evaluate::find($a[$i]->evaluate_id);
            $a[$i]->user = DB::table("personal_enterprise")->where("user_id",Evaluate::find($a[$i]->evaluate_id)->user_id)->select('name')->get()[0];
            $a[$i]->user->user_img = DB::table("users")->where("id",Evaluate::find($a[$i]->evaluate_id)->user_id)->select("user_img")->get()[0]->user_img;
        }
        return response($a,200);
    }
    //评价页面加载所要获取的兼职标题和所有学生
    public function get_job_all_student($id){
        $arr = [];
        $a = DB::table("job")->where("id",$id)->select(["job_title","id"])->get()[0];
        array_push($arr,$a);
        $b = DB::table("job_sign")->where("job_id",$id)->select("user_id")->get();
        for($i = 0; $i < count($b); $i++){
            $b[$i]->user = DB::table("personal_user")->where("user_id",$b[$i]->user_id)->get()[0];
        }
        array_push($arr,$b);
        return $arr;
    }
}
