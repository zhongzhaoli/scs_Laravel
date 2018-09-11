<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class DemandController extends Controller
{
    public function index(Request $request){
        $a = DB::table("demand")->paginate(10);
        return $a;
    }
}
