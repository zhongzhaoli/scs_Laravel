<?php

namespace App\Http\Middleware;

use Closure;
use DB;

class Enterprise_job
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
        $user_id = $request->user()->id;
        $a = DB::table("job")->where("id",$request->id)->get();
        if(count($a)) {
            if ($a[0]->user_id === $user_id || $request->user()->role === "admin") {
                return $next($request);
            } else {
                return response()->json(["message" => "你没有权限"], 400);
            }
        }
        else{
            return response()->json(["message" => "找不到此兼职"],400);
        }
    }
}
