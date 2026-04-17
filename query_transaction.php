<?php
header('Content-type: text/html; charset=utf-8');


function execPostRequest($url, $data)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data))
    );
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    //execute post
    $result = curl_exec($ch);
    //close connection
    curl_close($ch);
    return $result;
}

$endpoint = "https://test-payment.momo.vn/v2/gateway/api/query";
$partnerCode = 'MOMOBKUN20180529';
$accessKey = 'klm05TvNBzhg7h7j';
$secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
$requestId = time()."";
$requestType = "queryTransaction";

$displayMessage = "";

if (!empty($_POST)) {
    $orderId = $_POST["orderId"]; // Mã đơn hàng cần kiểm tra trạng thái

    //before sign HMAC SHA256 signature
    $rawHash = "accessKey=".$accessKey."&orderId=".$orderId."&partnerCode=".$partnerCode."&requestId=".$requestId;

    $signature = hash_hmac("sha256", $rawHash, $secretKey);

    $data = array('partnerCode' => $partnerCode,
        'requestId' => $requestId,
        'orderId' => $orderId,
        'requestType' => $requestType,
        'signature' => $signature,
        'lang' => 'vi');
    $result = execPostRequest($endpoint, json_encode($data));
    $jsonResult = json_decode($result, true);  // decode json
    // check signature response
    if(!empty($result)){
        $partnerCode = $jsonResult["partnerCode"];
        $accessKey = $jsonResult["accessKey"];
        $requestId = $jsonResult["requestId"];
        $orderId = $jsonResult["orderId"];
        $errorCode = $jsonResult["errorCode"];
        $transId = $jsonResult["transId"];
        $amount = $jsonResult["amount"];
        $message = $jsonResult["message"];
        $localMessage = $jsonResult["localMessage"];
        $requestType = $jsonResult["requestType"];
        $payType = $jsonResult["payType"];
        $extraData = ($jsonResult["extraData"] ? $jsonResult["extraData"] : "");
        $m2signature = $jsonResult["signature"];

        //before sign HMAC SHA256 signature
        $rawHash = "partnerCode=".$partnerCode."&accessKey=".$accessKey."&requestId=".$requestId."&orderId=".$orderId."&errorCode=".$errorCode."&transId=".$transId."&amount=".$amount."&message=".$message."&localMessage=".$localMessage."&requestType=".$requestType."&payType=".$payType."&extraData=".$extraData;
        $partnerSignature = hash_hmac("sha256", $rawHash, $secretKey);

        if ($m2signature == $partnerSignature) {
            if ($errorCode == 0) {
                $displayMessage = '<div class="alert alert-success"><strong>Kết quả: </strong>' . $localMessage . ' (Mã giao dịch: ' . $transId . ')</div>';
            } else {
                $displayMessage = '<div class="alert alert-danger"><strong>Thất bại: </strong>' . $localMessage . '</div>';
            }
        } else {
            $displayMessage = '<div class="alert alert-warning"><strong>Cảnh báo: </strong>Chữ ký phản hồi không hợp lệ!</div>';
        }
    }
}
?>