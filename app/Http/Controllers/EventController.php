<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class EventController extends Controller
{
    //获取所有活动
    public function index(){
        $a = DB::table("event")->get();
        return response()->json($a,200);
    }
    //修改活动状态
    public function change_type(Request $request,$id){
        DB::table("event")->where("id",$id)->update(["type" => $request->get("type")]);
        return response()->json(["message" => "success"],200);
    }
    //活动状态
    public function event_online($id){
        $a = DB::table("event")->where("id",$id)->select("type")->get();
        if(count($a)){
            if($a[0]->type === "on"){
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }
}
