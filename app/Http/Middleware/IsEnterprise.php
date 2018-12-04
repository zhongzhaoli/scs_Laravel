<?php

namespace App\Http\Middleware;

use Closure;

class IsEnterprise
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
        if($request->user()->role === 'enterprise' || $request->user()->role === 'admin') {
            return $next($request);
        }
        else{
            return response(["message" => "你不是企业账号"],412);
        }
    }
}
