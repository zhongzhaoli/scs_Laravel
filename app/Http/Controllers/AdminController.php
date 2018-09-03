<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use App\Job;
use App\Personal;
use Validator;

class AdminController extends Controller
{
    //查看审核中用户信息
    public function index(Request $request)
    {
        $a = DB::table("personal_user")->where("status", "examine")->OrderBy("create_time", "desc")->get();
        return response()->json($a, 200);
    }

    //同意审核用户信息
    public function access($userid)
    {
        $a = DB::Table("personal_user")->where("id", $userid)->get();
        if (count($a)) {
            // $content = $a[0]->name . "同学你好，你于" . $a[0]->create_time . "填写的资料审核合格，谢谢合作。";
            // $b = new sentmail($a[0],$content);
            $qc = new Qcloudsms();
            $b = $qc->sendcode($a[0]->phone, "", "148743");
            ($b == "success") ? $b = true : $b = false;
            $c = DB::table("personal_user")->where("id", $a[0]->id)->update(["status" => "adopt"]);
            if ($c && $b) {
                return response()->json(["message" => "success"], 200);
            } else {
                return response()->json(["message" => "error"], 400);
            }
        } else {
            return response()->json(["message" => "error"], 400);
        }
    }

    //拒绝审核用户信息
    public function refuse($userid)
    {
        $a = DB::Table("personal_user")->where("id", $userid)->get();
        if (count($a)) {
            // $content = $a[0]->name . "同学你好，你于" . $a[0]->create_time . "填写的资料审核不合格，请重新填写，谢谢合作。";
            // $b = new sentmail($a[0], $content);
            $qc = new Qcloudsms();
            $b = $qc->sendcode($a[0]->phone, "", "148766");
            ($b == "success") ? $b = true : $b = false;
            $c = DB::table("personal_user")->where("id", $a[0]->id)->update(["status" => "refuse"]);
            if ($c && $b) {
                return response()->json(["message" => "success"], 200);
            } else {
                return response()->json(["message" => "error"], 400);
            }
        } else {
            return response()->json(["message" => "error"], 400);
        }
    }

    //分类显示用户信息
    public function condition(Request $request)
    {
        $a = DB::table("personal_user")->where("status", $request->get("condition"))->OrderBy("create_time", "DESC")->get();
        return response()->json($a, 200);
    }

    //搜索用户信息
    public function search(Request $request)
    {
        $val = "%" . $request->get('value') . "%";
        if ($request->get("type") == "all") {
            $a = DB::select("select * from personal_user where status = '" . $request->get('status') . "' and  (name like '" . $val . "' or wechat like '" . $val . "' or phone like '" . $val . "' or class like '" . $val . "' or schoolcode like '" . $val . "' or intention like '" . $val . "') Order By create_time desc;");
        } else {
            $a = DB::select("select * from personal_user where status = '" . $request->get('status') . "' and " . $request->get('type') . " like '" . $val . "' Order by create_time desc;");
        }
        return response()->json($a, 200);
    }
//    ----------------------------------------------------------------------------------------------------------    //
    //查看审核中兼职信息
    public function job_index()
    {
        $a = DB::table("job")->where("status", "examine")->OrderBy("create_time", "desc")->get();
        for($i = 0;$i < count($a); $i++){
            $a[$i]->user = Job::find($a[$i]->id)->to_user;
        }
        return response()->json($a, 200);
    }

    //通过审核兼职信息
    public function job_access(Request $request, $id)
    {
        $change_money = $request->get("change_money");
        $a = DB::table("job")->where("id", $id)->update(["status" => "adopt", "admin_change_money" => $change_money, "leader_id" => $request->get("leader_id")]);
        if ($a) {
            $qc = new Qcloudsms();
            $b = $qc->sendcode(Job::find($id)->to_user->name, "", "149681");
            return response()->json(["message" => "success"], 200);
        } else {
            return response()->json(["message" => "error"], 400);
        }
    }

    //拒绝审核兼职信息
    public function job_refuse(Request $request, $id)
    {
        $a = DB::table("job")->where("id", $id)->update(["status" => "refuse"]);
        if($a){
            $qc = new Qcloudsms();
            $b = $qc->sendcode(Job::find($id)->to_user->name, "", "148699");
            return response()->json(["message" => "success"], 200);
        }
        else{
            return response()->json(["message" => "error"], 400);
        }
    }
    //条件搜索
    public function job_condition(Request $request){
        $status = $request->get("type");
        $a = '';
        //over 也代表 adopt
        if($status != "adopt") {
            $a = DB::table("job")->where("status", $status)->get();
        }
        else{
            $a = DB::table("job")->where("status",$status)->orwhere("status","over")->get();
        }
        if(count($a)) {
            for ($i = 0; $i < count($a); $i++) {
                $a[$i]->user = Job::find($a[$i]->id)->to_user;
            }
        }
        return response($a,200);
    }
    //条件搜索 学生
    public function sign_condition($job_id,Request $request){
        $a = DB::table("job_sign")->where(["status" => $request->get("type"),"job_id" => $job_id])->get();
        if(count($a)) {
            for ($i = 0; $i < count($a); $i++) {
                $a[$i]->user = DB::table("personal_user")->where("user_id",$a[$i]->user_id)->get()[0];
            }
        }
        return response()->json($a, 200);
    }
    //获取所有审核中企业
    public function enterprise_index(Request $request){
        $a = DB::table("personal_enterprise")->where("status", "examine")->OrderBy("create_time", "desc")->get();
        return response()->json($a, 200);
    }
    //企业条件搜索
    public function enterprise_condition(Request $request){
        $a = DB::table("personal_enterprise")->where("status", $request->get("condition"))->OrderBy("create_time", "DESC")->get();
        return response()->json($a, 200);
    }
    //同意审核企业信息
    public function enterprise_access($userid)
    {
        $a = DB::Table("personal_enterprise")->where("id", $userid)->get();
        if (count($a)) {
            $qc = new Qcloudsms();
            $b = $qc->sendcode($a[0]->phone, "", "183161");
            ($b == "success") ? $b = true : $b = false;
            $c = DB::table("personal_enterprise")->where("id", $a[0]->id)->update(["status" => "adopt"]);
            if ($c && $b) {
                return response()->json(["message" => "success"], 200);
            } else {
                return response()->json(["message" => "error"], 400);
            }
        } else {
            return response()->json(["message" => "error"], 400);
        }
    }

    //拒绝审核企业信息
    public function enterprise_refuse($userid)
    {
        $a = DB::Table("personal_enterprise")->where("id", $userid)->get();
        if (count($a)) {
            $qc = new Qcloudsms();
            $b = $qc->sendcode($a[0]->phone, "", "183160");
            ($b == "success") ? $b = true : $b = false;
            $c = DB::table("personal_enterprise")->where("id", $a[0]->id)->update(["status" => "refuse"]);
            if ($c && $b) {
                return response()->json(["message" => "success"], 200);
            } else {
                return response()->json(["message" => "error"], 400);
            }
        } else {
            return response()->json(["message" => "error"], 400);
        }
    }
    //管理员完结兼职（线下收到钱后）
    public function admin_job_over($id){
        $now = date("Y-m-d H:i:s");
        $a = DB::table("job")->where("id",$id)->get();
        if(count($a)){
            if($now > $a[0]->job_end_date){
                DB::table("job")->where("id",$a[0]->id)->update(["status" => "admin_over"]);
                $d = DB::table("job_over")->insert(["job_id" => $id, "create_time" => $now]);
                $e = DB::table("job_sign")->where("job_id",$id)->update(["over" => "over"]);
                //更改学生状态
                $c = DB::table("job_sign")->where("job_id",$id)->get();
                if(count($c)) {
                    for ($i = 0; $i < count($c); $i++) {
                        DB::table("personal_user")->where("user_id", $c[$i]->user_id)->update(['job_status' => "wait"]);
                    }
                }
                if($d) {
                    return response()->json(["message" => "success"], 200);
                }
                else{
                    return response()->json(["message" => "完结出错"],400);
                }
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
