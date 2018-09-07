<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class BillController extends Controller
{
    //查看账单
    public function index(Request $request){
        $user_id = $request->user()->id;
        $a = DB::table("user_bill")->where("user_id",$user_id)->get();
        return $a;
    }
    //创建账单
    public function bill_create($user_id, $credit = "+0", $experience = "+0", $integral = "+0", $create_time, $text){
        DB::table("user_bill")->insert([
            "id" => time() . md5(uniqid()),
            "user_id" => $user_id,
            "text" => $text,
            "credit" => $credit,
            "experience" => $experience,
            "integral" => $integral,
            "create_time" => $create_time
        ]);
    }
}
