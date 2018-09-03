<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use App\User;
use Illuminate\Support\Facades\Auth;
class IsAdmin
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
        if($request->user()->role === 'admin') {
            return $next($request);
        }
        else{
            return response(["message" => "你不是管理员"],400);
        }
    }
}
