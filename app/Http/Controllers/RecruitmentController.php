<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class RecruitmentController extends Controller
{
    public function store(Request $request){
        $arr = [];
        $id = time() . md5(uniqid());
        $cre_time = date("Y-m-d H:i:s");
        $img_list = $request->get("img_list");
        foreach($img_list as $i){
            $prove_up = new ProveUpload();
            $bo_prove = $prove_up->upload($i,"uploads/recruitment/");
            array_push($arr,"http://122.152.249.114/scs/public/".$bo_prove);
        }
        DB::table("recruitment")->insert([
            "id" => $id,
            "create_time" => $cre_time,
            "user_id" => $request->user()->id,
            "text" => $request->get("text"),
            "img_list" => json_encode($arr),
            "type" => $request->get("type")
        ]);
        return response()->json(["message" => "success"],200);
    }
}
