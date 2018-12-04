<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WxController extends Controller
{
    //返回token
    public function index(){
                
        $appid="wx83b6a811c58fa8ee";
        $secret="710b5f0dbcff4fb100d9d12df078bd08";
        
        $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;
        
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        $dd=curl_exec($ch);
        curl_close($ch);
        $access=json_decode($dd,true);
        $url="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access['access_token']."&type=jsapi";
        $ca=curl_init();
        curl_setopt($ca,CURLOPT_URL,$url);
        curl_setopt($ca,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ca,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ca,CURLOPT_SSL_VERIFYHOST,false);
        $da=curl_exec($ca);
        curl_close($ca);
        $ticket=json_decode($da,true);
        //$ticket['timestam']=time();
        // $a->where("category='ticket'")->setField("token",$ticket['ticket']);
        // $a->where("category='ticket'")->setField("expires",$atime);
        return $ticket;
        
        
    // }else{
    //     $ticket['errcode']=0;
    //     $ticket['ticket']=$da['token'];
    // }
    //     $this->ajaxReturn($ticket);
        
    // }

}
}