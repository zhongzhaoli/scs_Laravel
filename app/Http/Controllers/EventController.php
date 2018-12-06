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
    //新用户注册活动
    public function new_user($del_count,$user_id){
        //如果活动开启
        if(!$del_count){ //第一次填写信息 不会删除到东西
            DB::table("users")->where("id",$user_id)->increment("integral",18);//加18积分
            //账单
            $bill = new BillController();
            $bill->bill_create($user_id,"","","+18",date("Y-m-d H:i:s"),"新用户注册活动");
        }
    }
    //游园活动——页面刷新是否有领取，没有领取则弹窗提示领取
    public function garden_event_1(Request $request){
       if($this->event_online("15439101101f42de0f968142f82061deedbc9f8cae")){ //1536140152a1e660bc1e9e9c594148534a22666d55 为 活动ID
            $user_is_receive = DB::table("event_garden")->where("user_id",$request->user()->id)->get();
            //没领取
            if(!count($user_is_receive)){
                return response()->json(["message" => "no"],200);
            }
            //领取了
            else{
                return response()->json(["message" => $user_is_receive->status],400);
            }
        }
        else{
            return response()->json("",400);
        }
    }
}
