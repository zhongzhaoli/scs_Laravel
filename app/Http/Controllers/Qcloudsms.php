<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Qcloud\Sms\SmsSingleSender;

class Qcloudsms extends Controller
{
    public function sendcode($phone,$rands,$text_id)
    {
        // 短信应用SDK AppID
        $appid = 1400106555;

        // 短信应用SDK AppKey
        $appkey = "9b3bd42453d4c9a819c833a97a5d4000";

        // 需要发送短信的手机号码
        $phoneNumbers = $phone;

        $arr = [
            "148766" => "您的个人信息没有通过审核，请修改信息后再次提交。",
            "148743" => "您的个人信息已审核通过，请登录官网查询。",
            "146507" => "您的验证码是:".$rands."，请于2分钟内填写。如非本人操作，请忽略本短信。",
            "149681" => "您发布的兼职信息已通过审核，您可以登录官网查看实时报名人数。",
            "148699" => "您发布的兼职信息没有通过审核，请修改信息后再次提交。",
            "173750" => "恭喜，你申请的".$rands."，已通过审核。请登陆官网查看负责人的联系方式，和相关事项。谢谢合作。",
            "174103" => "很遗憾，你申请的".$rands."，未通过审核，可以登陆官网查看详细拒绝理由。谢谢合作。",
            "183161" => "您的企业信息已通过审核，请登录官网查看。",
            "183160" => "您的企业信息没有通过审核，请修改后再次提交。"
        ];

        // 短信模板ID，需要在短信应用中申请
        $templateId = $text_id;  // NOTE: 这里的模板ID`7839`只是一个示例，真实的模板ID需要在短信控制台中申请

        $smsSign = "云屯务集"; // NOTE: 这里的签名只是示例，请使用真实的已申请的签名，签名参数使用的是`签名内容`，而不是`签名ID`
        try {
            $ssender = new SmsSingleSender($appid, $appkey);
            $result = $ssender->send(0, "86", $phoneNumbers,
               $arr[$text_id] , "", "");
            $rsp = json_decode($result);
             return "success";
        } catch(\Exception $e) {
            // echo var_dump($e);
            return "error";
        }
    }
}
