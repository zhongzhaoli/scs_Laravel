<?php

namespace App\Http\Middleware;

use Closure;
use DB;
class JobSign
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //判断当前用户是不是通过审核了
        $a = DB::table("job_sign")->where(["user_id" => $request->user()->id, "job_id" => $request->id, "status" => "adopt"])->get();
        if(count($a)) {
            return $next($request);
        }
        else{
            return response()->json(["message" => "你没有权限"],400);
        }
    }
}
