<?php
header('Content-type: text/html; charset=utf-8');


$secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa'; //Put your secret key in there

if (!empty($_GET)) {
	$partnerCode = $_GET["partnerCode"];
	$orderId = $_GET["orderId"];
	$requestId = $_GET["requestId"];
	$amount = $_GET["amount"];	
	$orderInfo = $_GET["orderInfo"];
	$orderType = $_GET["orderType"];
	$transId = $_GET["transId"];
	$resultCode = $_GET["resultCode"];
	$message = $_GET["message"];
	$payType = $_GET["payType"];
	$responseTime = $_GET["responseTime"];
	$extraData = $_GET["extraData"];
	$localMessage = $_GET["localMessage"] ?? "";
	$m2signature = $_GET["signature"]; //MoMo signature
	

	//Checksum
	$rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&message=" . $message . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo .
		"&orderType=" . $orderType . "&partnerCode=" . $partnerCode . "&payType=" . $payType . "&requestId=" . $requestId . "&responseTime=" . $responseTime .
		"&resultCode=" . $resultCode . "&transId=" . $transId;

    $partnerSignature = hash_hmac("sha256", $rawHash, $secretKey);

    if ($m2signature == $partnerSignature) {
        if ($resultCode == '0') {
            $result = '<div class="alert alert-success"><strong>Payment status: </strong>Success</div>';
        } else {
            $result = '<div class="alert alert-danger"><strong>Payment status: </strong>' . $message .'/'.$localMessage. '</div>';
        }
    } else {
        $result = '<div class="alert alert-danger">This transaction could be hacked, please check your signature and returned signature</div>';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>MoMo Sandbox</title>
    <script type="text/javascript" src="./statics/jquery/dist/jquery.min.js"></script>
    <script type="text/javascript" src="./statics/moment/min/moment.min.js"></script>
    <script type="text/javascript" src="./statics/bootstrap/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript"
            src="./statics/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/css/bootstrap.min.css"/>
    <link rel="stylesheet"
          href="./statics/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css"/>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h1 class="panel-title">Payment status/Kết quả thanh toán</h1>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?php echo $result; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <a href="index.php" class="btn btn-primary">Quay lại trang chủ</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>