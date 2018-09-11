<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use App\Job;
use App\Personal;
use Validator;
use App\Gift;
use Illuminate\Support\Facades\Redis;

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
                return response()->json(["message" => "短信通知出现问题"], 400);
            }
        } else {
            return response()->json(["message" => "找不到此用户"], 400);
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
                return response()->json(["message" => "短信通知出现问题"], 400);
            }
        } else {
            return response()->json(["message" => "找不到此用户"], 400);
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
            return response()->json(["message" => "修改兼职状态薪酬出错"], 400);
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
            return response()->json(["message" => "修改兼职状态出错"], 400);
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
                return response()->json(["message" => "短信通知出现问题"], 400);
            }
        } else {
            return response()->json(["message" => "找不到此企业"], 400);
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
                return response()->json(["message" => "短信通知出现问题"], 400);
            }
        } else {
            return response()->json(["message" => "找不到此企业"], 400);
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
                        $user_all_arr = DB::table("users")->where("id",$c[$i]->user_id)->get()[0];
                        //修改积分修改 (只给基础)
                        DB::table("users")->where("id",$c[$i]->user_id)->increment("integral",5);
                        //信用修改
                        $xy = $user_all_arr->credit;
                        if($xy != 100 && $xy + 10 >= 100){
                            DB::table("users")->where("id",$c[$i]->user_id)->update(["credit" => 100]);
                        }
                        if($xy != 100 && $xy + 10 < 100){
                            DB::table("users")->where("id",$c[$i]->user_id)->update(["credit" => $xy + 10]);
                        }
                        //经验等级
                        $jy = $user_all_arr->experience + 20;
                        $dj = $user_all_arr->level;
                        $ret_le = $this->level_up($dj,$jy);
                        DB::table("users")->where("id",$c[$i]->user_id)->update(["level" => $ret_le["level"],"experience" => $ret_le["experience"]]);
                        //账单
                        $bill = new BillController();
                        $bill->bill_create($c[$i]->user_id,10,20,5,date("Y-m-d H:i:s"),"完成兼职");
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
    //待处理小数字
    public function admin_treated(){
        $user = count(DB::table("personal_user")->where("status", "examine")->get());
        $enterprise = count(DB::table("personal_enterprise")->where("status", "examine")->get());
        $job = count(DB::table("job")->where("status", "examine")->get());
        $feedback = count(DB::table("job_feedback")->get());
        $customer = count(DB::table("customer")->where("status", "qu")->get()) - count(DB::table("customer")->where("status", "an")->get());
        $arr = ["user" => $user, "enterprise" => $enterprise, "job" => $job, "feedback" => $feedback, "customer" => $customer];
        return response()->json($arr,200);
    }
    //等级处理  功能函数
    public function level_up($level,$experience){
        $level_need = $level * 100;
        if($experience >= $level_need){
            $level = $level + 1;
            $experience = $experience - $level_need;
        }
        if($experience < 0){
            if($level == 1){
                $level = 1;
                $experience = 0;
            }
            else {
                $level_1_need = ($level - 1) * 100;
                $experience = $level_1_need - (-$experience);
                $level = $level - 1;
            }
        }
        return ["level" => $level, "experience" => $experience];
    }
    //admin_over兼职 并且获取企业对学生的评价和平台评价
    public function admin_over_job_student_evaluate(Request $request){
        $a = DB::table("job")->where("status","admin_over")->get();
        if(count($a)){
            for($i = 0; $i < count($a); $i++){
                //平台评价
                $e = DB::table("evaluate")->where("job_id",$a[$i]->id)->get()[0];
                $a[$i]->evaluate = $e;
                //学生评价
                $b = DB::table("evaluate_student")->where("job_id",$a[$i]->id)->get();
                 if(count($b)){
                     for($j = 0; $j < count($b); $j++){
                         $b[$j]->user = DB::table("personal_user")->where("user_id",$b[$j]->user_id)->get()[0];
                     }
                 }
                 $a[$i]->user = $b;
            }
        }
        return $a;
    }
    //兑换礼品
    public function admin_exchange($vo_id, $user_id){
        $a = DB::table("my_voucher")->where(["id" => $vo_id, "user_id" => $user_id])->get();
        if(count($a)){
            if($a[0]->status == "yes"){
                return response()->json(["message" => "此券已兑换"],400);
            }
            else{
                $a[0]->voucher = Gift::find($a[0]->voucher_id);
                $b = DB::table("personal_user")->where("user_id",$user_id)->get();
                if(count($b)){
                    $a[0]->user = $b[0];
                    return response()->json($a,200);
                }
                else{
                    return response()->json(["message" => "此用户还未完善信息"]);
                }
            }
        }
        else{
            return response()->json(["message" => "此券不存在"],400);
        }
    }
    //兑换验证码
    public function admin_exchange_code(Request $request, $user_id){
        $a = DB::table("users")->where("id",$user_id)->get();
        if(count($a)) {
            if (Redis::get("exchange_code_" . $a[0]->name)){
                return response()->json(["message" => "success"],200);
            }
            $c = new PhoneCode();
            $b = $c->phone_code($a[0]->name, "exchange_code_", "146507");
            if($b == "success"){
                return response()->json(["message" => "success"],200);
            }
            else{
                return response()->json(["message" => $b],400);
            }
        }
        else{
            return response()->json(["message" => "没有找到此用户"],400);
        }
    }
    //判断收到的验证码确认领取成功
    public function admin_exchange_yz_code(Request $request, $user_id){
        $a = DB::table("users")->where("id", $user_id)->get();
        if (count($a)) {
            if (Redis::get("exchange_code_" . $a[0]->name) === $request->get("code")) {
                DB::table("my_voucher")->where("id",$request->get("vou_id"))->update(["status" => "yes","use_time" => date("Y-m-d H:i:s")]);
                return response()->json(["message" => "领取成功"],200);
            }
            else{
                return response()->json(["message" => "验证码错误"],400);
            }
        }
        else {
            return response()->json(["message" => "没有找到此用户"],400);
        }
    }
}
