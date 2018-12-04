<?php

namespace App\Http\Middleware;

use Closure;

class isStudent
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
        if($request->user()->role === 'student' || $request->user()->role === 'admin') {
            return $next($request);
        }
        else{
            return response(["message" => "你不是学生账号"],412);
        }
    }
}
