<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use DB;

class PhoneCode
{
    public function phone_code($phone, $redis_name, $text_id)
    {
        if(!preg_match("/^(13[0-9]|14[5-9]|15[012356789]|166|17[0-8]|18[0-9]|19[8-9])[0-9]{8}$/",$phone)){
            return "手机号不合法";
        }
        $rands = rand(1000, 9999);
        Redis::setex($redis_name . $phone, 120, $rands);
        $qc = new Qcloudsms();
        $a = $qc->sendcode($phone, $rands,$text_id);
        if($a === "success"){
            return "success";        
        }
        else{
            return "获取验证码失败";
        }
    }
}

?>