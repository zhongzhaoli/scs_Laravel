<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Validator;
use App\Job;
use App\User;
use App\JobSign;

class JobController extends Controller
{
    //所有通过的兼职
    public function index(){
        $a = DB::table("job")->where("status","adopt")->get();
        return response($a,200);
    }
    //首页三条
    public function job_index(){
        $a = DB::table("job")->where(["status" => "adopt"])->OrderBy("create_time","desc")->limit(3)->get();
        for($i = 0; $i < count($a); $i++){
//            $a[$i]->user = DB::table("personal_enterprise")->where("user_id",$a[$i]->user_id)->get()[0];
            $a[$i]->user_img = DB::table("users")->where("id",$a[$i]->user_id)->select("user_img")->get()[0]->user_img;
        }
        return response($a,200);
    }
    //显示某个兼职
    public function show($id){
        $a = DB::table("job")->where("id",$id)->get();
        if(count($a)) {
            return response($a, 200);
        }
        else{
            return response(["message" => "没有这个兼职"],400);
        }
    }
    //插入兼职
    public function job_insert(Request $request){
        //Validator 检验
        if($request->user()->role != "admin"){
            $a = DB::table("personal_enterprise")->where("user_id",$request->user()->id)->get();
            if(!count($a)){
                return response()->json(["message" => "未完善企业信息"],400);
            }
            else{
                if($a[0]->status == "examine"){
                    return response()->json(["message" => "企业信息正在审核中"],400);
                }
                if($a[0]->status == "refuse"){
                    return response()->json(["message" => "请重新填写企业信息"],400);
                }
            }
        }
        $result = Validator::make($request->all(),[
            "job_title" => "required|Max:255",
            "job_introduce" => "required|Max:255",
            "job_num" => "required|Integer",
            "job_type" => "required|Max:255",
            "job_place" => "required|Max:255",
            "job_start_date" => "required|date",
            "job_start_time" => "required",
            "job_end_date" => "required|date",
            "job_hour" => "required|Integer",
            "job_money" => "required|Integer",
            "job_rest" => "required|Integer",
            "balance_type" => "required",
            "job_detail_place" => "required",
            "latitude_longitude" => "required"
        ],[
            "job_title.required" => "兼职标题不能为空",
            "job_introduce.required" => "兼职简介不能为空",
            "job_num.required" => "兼职人数不能为空",
            "job_num.integer" => "兼职人数不合法",
            "job_type.required" => "兼职类型不能为空",
            "job_place.required" => "兼职地点不能为空",
            "job_start_date.required" => "兼职开始日期不能为空",
            "job_start_date.date" => "兼职开始日期不合法",
            "job_start_time.required" => "兼职开始时间不能为空",
            "job_end_date.required" => "兼职结束日期不能为空",
            "job_end_date.date" => "兼职结束日期不能为空",
            "job_hour.required" => "兼职时长不能为空",
            "job_hour.integer" => "兼职时长不合法",
            "job_money.required" => "兼职工资不能为空",
            "job_money.integer" => "兼职工资不合法",
            "job_rest.required" => "兼职休息时间不能为空",
            "job_rest.integer" => "兼职休息时间不合法",
            "balance_type.required" => "结算方式不能为空",
            "job_detail_place.required" => "兼职详细地址不能为空",
            "latitude_longitude.required" => "地点经纬度不能为空",
        ]);
        if($result->fails()){
            return response()->json($result->errors(),400);
        }
        $id = time() . md5(uniqid());
        $request->merge(["create_time" => date("Y-m-d H:i:s"),"id" => $id, "user_id" => $request->user()->id, "status" => "examine"]);
        DB::table("job")->insert([
            "id" => $request->get("id"),
            "job_title" => $request->get("job_title"),
            "job_introduce" => $request->get("job_introduce"),
            "job_num" => $request->get("job_num"),
            "job_type" => $request->get("job_type"),
            "job_place" => $request->get("job_place"),
            "job_start_date" => $request->get("job_start_date"),
            "job_start_time" => $request->get("job_start_time"),
            "job_end_date" => $request->get("job_end_date"),
            "job_hour" => $request->get("job_hour"),
            "job_money" => $request->get("job_money"),
            "job_detail_time" => ($request->get("job_detail_time") == "") ? "无" : $request->get("job_detail_time"),
            "job_detail_content" => ($request->get("job_detail_content") == "") ? "无" : $request->get("job_detail_content"),
            "job_detail_subsidy" => ($request->get("job_detail_subsidy") == "") ? "无" : $request->get("job_detail_subsidy"),
            "job_remarks" => ($request->get("job_remarks") == "") ? "无" : $request->get("job_remarks"),
            "create_time" => $request->get("create_time"),
            "status" => $request->get("status"),
            "user_id" => $request->get("user_id"),
            "job_rest" => $request->get("job_rest"),
            "balance_type" => $request->get("balance_type"),
            "job_detail_place" => $request->get("job_detail_place"),
            "latitude_longitude" => $request->get("latitude_longitude")
        ]);
        return response()->json(["message" => "success"],200);
    }
    //兼职报名
    public function job_sign(Request $request, $job_id){
        $job_has_num = DB::table("job")->where("id",$job_id)->select("job_has_num")->get();
        $job_want_num = DB::table("job")->where("id",$job_id)->select("job_num")->get();
        $user_id = $request->user()->id;        
        $if_you_sign = DB::table("job_sign")->where(["user_id" => $user_id, "job_id" => $job_id])->where("status", "!=" , "refuse")->get();
        if(count($if_you_sign)){
            return response()->json(["message" => "你已经报过名了"],400);
        }
        $if_personal_user = DB::table("personal_user")->where("user_id", $user_id)->get();
        if(!count($if_personal_user)){
            return response()->json(["message" => "未完善个人信息"],400);
        }
        else{
            if($if_personal_user[0]->status == "examine"){
                return response()->json(["message" => "个人信息正在审核中"],400);
            }
            if($if_personal_user[0]->status == "refuse"){
                return response()->json(["message" => "请重新填写个人信息"],400);
            }
        }
        $if_you_refuse = DB::table("job_sign")->where(["user_id" => $request->user()->id, "job_id" => $job_id, "status" => "refuse"])->get();
        if(count($if_you_refuse)){
            return response()->json(["message" => "你已经被此兼职拒绝"],400);
        }
        if($job_has_num[0]->job_has_num < $job_want_num[0]->job_num){
            $id = time() . md5(uniqid());
            $a = DB::table("job_sign")->insert([
                "id" => $id,
                "user_id" => $user_id,
                "job_id" => $job_id,
                "status" => "examine"
            ]);
            if($a){
                DB::table("job")->where("id",$job_id)->increment("job_has_num");
            }
            return response()->json(["message" => "success"],200);
        }
        else{
            return response()->json(["message" => "报名失败，人数已满", "type" => "1"],400);
        }
    }
    //查看报名学生
    public function job_sign_student($id){
        $a = DB::table("job_sign")->where("job_id",$id)->where("status","examine")->get();
        for($i = 0; $i < count($a); $i++){
            $c = JobSign::find($a[$i]->id)->to_personal;
            $a[$i]->user = $c;
        }
        return $a;
    }
    //我的兼职
    public function my_job(Request $request){
        $a = DB::table("job_sign")->where("user_id",$request->user()->id)->get();
        if(count($a)){
            for ($b = 0; $b < count($a); $b++){
                $c = JobSign::find($a[$b]->id)->to_job;
                $a[$b]->job = $c;
            }
            return response()->json($a,200);
        }
        return $a;
    }
    //我的兼职(企业)
    public function enterprise_my_job(Request $request){
        $user_id = $request->user()->id;
        $a = DB::table("job")->where("user_id",$user_id)->get();
        return response()->json($a,200);
    }
    //兼职反馈插入
    public function job_feedback_insert(Request $request, $job_id){
        $user_id = $request->user()->id;
        $val = $request->get("value");
        if($val != "" && $val != null){
            $id = time() . md5(uniqid());
            $data_n = date("Y-m-d H:i:s");
            $a = DB::table("job_feedback")->insert(["id" => $id, "create_time" => $data_n, "user_id" => $request->user()->id, "job_id" => $job_id, "value" => $val, "job_title" => $request->get("title")]);
            if($a){
                return response()->json(["message" => "success"],200);
            }
            else{
                return response()->json(["message" => "反馈失败"],400);
            }
        }
        else{
            return response()->json(["message" => "内容不能为空"],400);
        }
    }
    //获取所有兼职反馈
    public function job_feedback_all(){
        $a =DB::table("job_feedback")->get();
        return $a;
    }
    //通过学生申请的兼职
    public function job_sign_adopt($id, $job_id){
        $a = DB::table("job_sign")->where(["user_id" => $id, "job_id" => $job_id])->update(["status" => "adopt"]);
        if($a){
            //通知学生
            $qc = new Qcloudsms();
            $phone_ = User::find($id)->name;
            $job_title_ = Job::find($job_id)->job_title;
            $job_title_ = (stristr($job_title_,"兼职")) ? $job_title_ : $job_title_ . "兼职";
            $qc->sendcode($phone_, $job_title_, "173750");
            //更改学生兼职状态
            DB::table("personal_user")->where("user_id", $id)->update(["job_status" => "in"]);
            return response()->json(["message" => "success"],200);
        }
        else{
            return response()->json(["message" => "error"],400);
        }
    }
    //拒绝学生申请的兼职
    public function job_sign_refuse(Request $request,$id, $job_id){
        $a = DB::table("job_sign")->where(["user_id" => $id, "job_id" => $job_id])->update(["status" => "refuse", "refusal" => $request->get("ref")]);
        if($a){
            DB::table("job")->where("id",$job_id)->increment("job_has_num","-1");
            //通知学生API
            $qc = new Qcloudsms();
            $phone_ = User::find($id)->name;
            $job_title_ = Job::find($job_id)->job_title;
            $job_title_ = (stristr($job_title_,"兼职")) ? $job_title_ : $job_title_ . "兼职";
            $qc->sendcode($phone_, $job_title_, "174103");
            return response()->json(["message" => "success"],200);
        }
        else{
            return response()->json(["message" => "error"],400);
        }
    }
    //通过类型查找兼职
    public function find_job_type(Request $request){
        $a = DB::table("job")->where(["job_type" => $request->get("type"),"status" => "adopt"])->get();
        return response($a,200);
    }
}
