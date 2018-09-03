<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class ProveUpload{
    public function upload($base64,$path){
        try {
            // $base_img是获取到前端传递的值
            $base_img = explode('base64,', $base64)[1];
            //  设置文件路径和命名文件名称
            $output_file = md5(time()) . rand(1, 9999999) . '.jpg';
            $path = $path . $output_file;
            //  创建将数据流文件写入我们创建的文件内容中
            file_put_contents($path, base64_decode($base_img));
            return $path;
        }
        catch (\Exception $error){
            return false;
        }
    }
}