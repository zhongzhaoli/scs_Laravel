<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Validator;
use App\Gift;
use App\User;

class GiftController extends Controller
{
    //查看所有券
    public function index(){
        $a = DB::table("gift")->OrderBy("integral")->get();
        return $a;
    }
    //管理员插入新的券
    public function store(Request $request){
        $id = time() . md5(uniqid());
        //上传图片
        if($request->get("img")) {
            $prove_up = new ProveUpload();
            $bo_prove = $prove_up->upload($request->get("img"), "uploads/gift/");
            if (!$bo_prove) {
                return response()->json(["message" => ["上传失败"]], 400);
            }
            $prove_url = "http://122.152.249.114/scs/public/".$bo_prove;
        }
        else{
            $prove_url = "";
        }
        if($request->get("type") === '5'){
            if(!$prove_url){
                return response()->json(["message" => "实物礼品需要上传图片"],400);
            }
        }
        if($request->get("type") != "5"){
            if(!$request->get("num")){
                return response()->json(["message" => "请填写数值"],400);
            }
            if(!$request->get("notes")){
                return response()->json(["message" => "请填写注释"],400);
            }
        }
        //类型数组(规则而已，没有用到)
        $arr = [
          "1" => "午餐补贴",
          "2" => "交通补贴",
          "3" => "节日补贴",
          "4" => "薪酬增益",
          "5" => "实物礼品"
        ];
        //检验
        $result = Validator::make($request->all(),[
            "name" => "required|Max:255",
            "integral" => "required|Integer",
            "type" => "required|Integer",
        ],[
            "name.required" => "姓名不能为空",
            "integral.required" => "所需积分不能为空",
            "integral.integer" => "所需积分不合法",
            "type.required" => "类型不能为空",
        ]);
        if($result->fails()){
            return response()->json(["message" => "填写数据有误"],400);
        }
        $a = DB::table("gift")->insert([
            "id" => $id,
            "name" => $request->get("name"),
            "integral" => $request->get("integral"),
            "img" => $prove_url,
            "type" => $request->get("type"),
            "num" => $request->get("num"),
            "notes" => $request->get("notes"),
        ]);
        if($a){
            return response()->json(["message" => "success"],200);
        }
        else{
            return response()->json(["message" => "插入失败"],400);
        }
    }
    //兑换
    public function exchange_voucher(Request $request, $voucher_id){
        $user_id = $request->user()->id;
        $gift_integral = Gift::find($voucher_id)->integral;
        $user_integral = User::find($user_id)->integral;
        if($user_integral >= $gift_integral){
            $a = DB::Table("my_voucher")->insert([
                "id" => time() . md5(uniqid()),
                "voucher_id" => $voucher_id,
                "user_id" => $user_id,
                "create_time" => date("Y-m-d H:i:s"),
                "status" => "not",
                "type" => Gift::find($voucher_id)->type,
            ]);
            $b = DB::table("users")->where("id", $user_id)->decrement("integral",$gift_integral);
            if($a && $b){
                return response()->json(["message" => "兑换成功"],200);
            }
            else{
                return response()->json(["message" => "兑换失败"],400);
            }
        }
        else{
            return response()->json(["message" => "积分不足"],400);
        }
    }
    //查看我的券
    public function my_voucher(Request $request){
        $a = DB::table("my_voucher")->where("user_id",$request->user()->id)->get();
        for($i = 0; $i < count($a); $i++){
            $a[$i]->voucher = Gift::find($a[$i]->voucher_id);
        }
        return $a;
    }
    //管理员删除券
    public function destory($id){
        DB::table("gift")->where("id",$id)->delete();
        return response()->json(["message" => "success"]);
    }
}
