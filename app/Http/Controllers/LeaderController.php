<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Leader;
use DB;

class LeaderController extends Controller
{
    public function index(){
        $a = DB::table("leader_user")->get();
        for($i = 0; $i < count($a); $i++){
            $c = Leader::find($a[$i]->id)->to_personal;
            $a[$i]->user = $c;
        }
        return $a;
    }
    public function store(Request $request){
        $request->merge(["create_time" => date('Y-m-d H:i:s'),"id" => time() . md5(uniqid())]);
        DB::table("leader_user")->insert([
            "id" => $request->get("id"),
            "user_id" => $request->get("user_id"),
            "create_time" => $request->get("create_time")
        ]);
        return response()->json(["message" => "success"],200);
    }
    public function show(Request $request,$job_id){
        $a = DB::table("job")->where("id",$job_id)->get();
        if(count($a)){
            $leader_id = $a[0]->leader_id;
            return Leader::find($leader_id)->to_personal;
        }
    }
    public function find_user(Request $request){
        $a = DB::table("personal_user")->where("phone",$request->get("name"))->get();
        if(count($a)) {
            return response()->json($a, 200);
        }
        else{
            return response()->json("",400);
        }
    }
}
