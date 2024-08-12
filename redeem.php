<?php
//=========参数配置=======//
$app='TYREyo';//应用特征码
$domain='https://api.31l.cc';//网站地址
//=====================//

$code=$_GET['code'];
$apiurl=$domain.'/api/down/redeem/'.$app.'/code/'.$code.'/v1';//应用接口
 
function fetchRemoteDataWithCurl($url) {
    // 初始化cURL会话
    $curl = curl_init();
    
    // 设置cURL选项
    curl_setopt($curl, CURLOPT_URL, $url); // 设置URL
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 返回而不是输出内容
    
    // 执行cURL会话
    $response = curl_exec($curl);
    
    // 关闭cURL会话
    curl_close($curl);
    
    if ($response === false) {
        // 发生错误，返回错误信息
        return 'Error: ' . curl_error($curl);
    }
    
    return $response; // 返回API响应
}

$response=fetchRemoteDataWithCurl($apiurl);
$responseData = json_decode($response, true);
$code=$responseData['code'];
if($code==0){
    echo $response;
}else{
        $tf1_link = $responseData['data']['tf1_link']??$responseData['data']['url'];
        if($tf1_link){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://testflight.apple.com/v1/invite/{$tf1_link}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 忽略 SSL 证书验证
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 忽略 SSL 主机验证
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3'
            ));
    
            $html = curl_exec($ch);
    
            if ($html === false) {
                echo json_encode(['error' => curl_error($ch)]);
            } else {
                if(strpos($html,"Not Found")){
                    echo json_encode(['code' => -1, 'msg' => '暂无可用链接'], JSON_UNESCAPED_UNICODE);
                }else if (strpos($html, 'The invitation has already been redeemed') !== false || strpos($html, '已兑换') !== false) {
                    echo json_encode(['code' => -1, 'msg' => '已被兑换'], JSON_UNESCAPED_UNICODE);
                } else {
                    $dhm = explode('</span> and start testing.</li>', explode("<li>Enter <span class='bold black'>", $html)[1])[0];
                    echo json_encode(['code' => 1, 'data' => ['url' => $tf1_link,'tfcode' => $dhm], 'msg' => '获取成功'], JSON_UNESCAPED_UNICODE);
                }
            }

        }else{
            echo json_encode(['code' => -1, 'msg' => '暂无可用链接'], JSON_UNESCAPED_UNICODE);
        }

    
    
    
    
}



?>