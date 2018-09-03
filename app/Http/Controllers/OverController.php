<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class OverController extends Controller
{
    public function over($id){
        $now = date("Y-m-d");
        $a = DB::table("job")->where("id",$id)->get();
        if(count($a)){
            if($now > $a[0]->job_end_date){
                DB::table("job")->where("id",$a[0]->id)->update(["status" => "over"]);
                //更改学生状态
                $c = DB::table("job_sign")->where("job_id",$id)->get();
                if(count($c)) {
                    for ($i = 0; $i < count($c); $i++) {
                        DB::table("personal_user")->where("user_id", $c[$i]->user_id)->update(['job_status' => "wait"]);
                    }
                }
                return response()->json(["message" => "success"],200);
            }
            else{
                return response()->json(["message" => "完结日期必须是兼职结束日期之后"],400);
            }
        }
        else{
            return response()->json(["message" => "找不到此兼职"],400);
        }
    }
}
