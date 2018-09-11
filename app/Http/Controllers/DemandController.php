<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\User;

class DemandController extends Controller
{
    public function index(Request $request){
        $a = DB::table("demand")->paginate(7);
        return $a;
    }
}
