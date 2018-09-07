<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class CreditController extends Controller
{
    public function index(Request $request){
        $user_id = $request->user()->id;
        $credit = DB::table("users")->where("id",$user_id)->select("credit")->get();
        if(count($credit)){
            return response()->json($credit[0],200);
        }
        else {
            return response()->json(["message" => "没有此用户"],400);
        }
    }
}
