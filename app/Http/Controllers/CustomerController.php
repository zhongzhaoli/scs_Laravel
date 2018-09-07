<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\User;

class CustomerController extends Controller
{
    //客服获取哪几用户提问题
    public function index(Request $request){
        $a = DB::table("customer")->GroupBy("user_id")->get();
        if(count($a)){
            for($i = 0; $i < count($a); $i++){
                $a[$i]->user = User::find($a[$i]->user_id);
                $a[$i]->last = DB::table("customer")->where("user_id",$a[$i]->user_id)->OrderBy("create_time","desc")->first();
            }
        }
        return response()->json($a,200);
    }
    //提问
    public function store(Request $request){
        $role = $request->user()->role;
        $crea_time = date("Y-m-d H:i:s");
        $a = DB::table("customer")->insert([
            "id" => time() . md5(uniqid()),
            "user_id" => $request->user()->id,
            "text" => $request->get("text"),
            "role" => $role,
            "create_time" => $crea_time,
            "from_qu_id" => "",
            "status" => "qu"
        ]);
        if($a){
            return response()->json(["message" => "success"],200);
        }
        else{
            return response()->json(["message" => "提问失败"],400);
        }
    }
    //我的问题
    public function show(Request $request){
        $user_id = $request->user()->id;
        $a = DB::table("customer")->where("user_id",$user_id)->OrderBy("create_time","asc")->get();
        return $a;
    }
    //客服查看和其他用户的问题
    public function show_admin($id){
        $a = DB::table("customer")->where("user_id",$id)->OrderBy("create_time","asc")->get();
        return $a;
    }
    //回答问题
    public function an(Request $request){
        $role = $request->user()->role;
        $crea_time = date("Y-m-d H:i:s");
        $a = DB::table("customer")->insert([
            "id" => time() . md5(uniqid()),
            "user_id" => $request->get("qu_user_id"),
            "text" => $request->get("text"),
            "role" => $role,
            "create_time" => $crea_time,
            "status" => "an"
        ]);
        if($a){
            return response()->json(["message" => "success"],200);
        }
        else{
            return response()->json(["message" => "回答失败"],400);
        }
    }
}
