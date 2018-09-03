<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Evaluate;

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
    //插入评价
    public function store(Request $request,$id){
        $user_id = $request->user()->id;
        $a = DB::table("evaluate")->where("job_id",$id)->get();
        if(count($a) >= 3){
            return response()->json(["message" => "此次评价已上限"],400);
        }
        DB::table("evaluate")->insert([
            "id" => time() . md5(uniqid()),
            "user_id" => $user_id,
            "job_id" => $id,
            "text" => $request->get("text"),
            "type" => $request->get("type"),
            "create_time" => date("Y-m-d H:i:s")
        ]);
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
}
