<?php
// 应用公共文件
function nonce_str($len=32){
    $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
    $randStr = str_shuffle($str);//打乱字符串
    $rands= substr($randStr,0,$len);//substr(string,start,length);返回字符串的⼀部分
    return $rands;
}

function upload($file){
    $savename = \think\facade\Filesystem::disk('public')->putFile('topic', $file,'md5');
    return '/storage/' . $savename;
}

function curl($url, $data = '', $headers = ['Content-Type: application/x-www-form-urlencoded; charset=UTF-8'])
{
    $curl = curl_init();
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_HEADER, 0);
    if(count($headers))
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0");
    if (strstr($url, 'https://')) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    curl_setopt($curl, CURLOPT_TIMEOUT, 60);
    $result = curl_exec($curl);
    $error = curl_error($curl);
    $output = $error ? $error : $result;
    $one = substr($output,0,1);
    if(in_array($one,['{','['])){
        return json_decode($output,true);
    }
    return $output;
}

function curls($params){
    $multi = curl_multi_init();
    $handles = [];
    foreach($params as $param){
        $url = $param['url'];
        $curl = curl_init();
        if(!empty($param['data'])){
            curl_setopt($curl,CURLOPT_POST,1);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$param['data']);
        }
        curl_setopt($curl, CURLOPT_HEADER, 0);
        if(!empty($param['headers'])){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $param['headers']);
        }else{
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded; charset=UTF-8']);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0");
        if(strstr($url,'https://')){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_multi_add_handle($multi,$curl);
        // 将 curl 句柄添加到数组中
        $handles[] = $curl;
    }
    $running = null;
    do {
        curl_multi_exec($multi, $running);
    } while ($running);
    
    // 迭代多会话中的cURL句柄，查看它们是否已完成
    $result = [];
    while ($info = curl_multi_info_read($multi)) {
        $ch = $info['handle'];
        if(count($params)>1){
            $result[] = curl_multi_getcontent($ch);
        }else{
            $result = curl_multi_getcontent($ch);
        }
        // 从多会话中删除cURL句柄
        curl_multi_remove_handle($multi, $ch);
    }
    
    // 关闭 curl 句柄
    foreach ($handles as $handle) {
        curl_multi_remove_handle($multi, $handle);
        curl_close($handle);
    }
    // 关闭 curl_multi 句柄
    curl_multi_close($multi);
    return $result;
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return;
    }

    $files = array_diff(scandir($dir), array('.', '..'));

    foreach ($files as $file) {
        $path = $dir . '/' . $file;

        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            @unlink($path);
        }
    }

    @rmdir($dir);
}

function isDomain($str) {
    $pattern = '/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,6}$/';
    return preg_match($pattern, $str);
}

function convertToNumber($input) {
    // 尝试将字符串转换为整数
    $intVal = filter_var($input, FILTER_VALIDATE_INT);
    
    // 如果是整数，直接返回
    if ($intVal !== false) {
        return $intVal;
    }

    // 尝试将字符串转换为浮点数
    $floatVal = filter_var($input, FILTER_VALIDATE_FLOAT);

    // 如果是浮点数，直接返回
    if ($floatVal !== false) {
        return $floatVal;
    }

    // 如果都不是，则返回原始字符串
    return $input;
}