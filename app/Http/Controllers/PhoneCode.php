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
        if(!preg_match("/^1[34578]{1}\d{9}$/",$phone)){
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