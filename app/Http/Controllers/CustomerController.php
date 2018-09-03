<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\User;

class CustomerController extends Controller
{
    //客服获取问题
    public function index(Request $request){
        $a = DB::table("customer")->where("status", "qu")->OrderBy("create_time")->get();
        for($i = 0;$i < count($a); $i++){
            $b = DB::table("customer")->where("from_qu_id",$a[$i]->id)->get();
            $a[$i]->an = (count($b)) ? $b[0] : "";
            $a[$i]->user = DB::table("personal_user")->where("user_id",$a[$i]->user_id)->get();
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
            return response()->json(["message" => "error"],400);
        }
    }
    //我的问题
    public function show(Request $request){
        $user_id = $request->user()->id;
        //获取当前user_id下的问题
        $a = DB::table("customer")->where(["user_id" => $user_id, "from_qu_id" => ""])->OrderBy("create_time")->get();
        for($i = 0;$i < count($a); $i++){
            $b = DB::table("customer")->where("from_qu_id",$a[$i]->id)->get();
            $a[$i]->an = (count($b)) ? $b[0] : "";
        }
        return response()->json($a,200);
    }
    //回答问题
    public function an(Request $request){
        $role = $request->user()->role;
        $crea_time = date("Y-m-d H:i:s");
        $a = DB::table("customer")->insert([
            "id" => time() . md5(uniqid()),
            "user_id" => $request->user()->id,
            "text" => $request->get("text"),
            "role" => $role,
            "create_time" => $crea_time,
            "from_qu_id" => $request->get("qu_id"),
            "status" => "an"
        ]);
        if($a){
            return response()->json(["message" => "success"],200);
        }
        else{
            return response()->json(["message" => "error"],400);
        }
    }
}
