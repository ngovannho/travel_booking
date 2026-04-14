<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$tour_id = $_POST['tour_id'];
$customer_name = $_POST['customer_name'];
$customer_phone = $_POST['customer_phone'];
$customer_email = $_SESSION['user']['email'];
$num_adults = $_POST['num_adults'] ?? 1;
$num_children = $_POST['num_children'] ?? 0;
$num_infants = $_POST['num_infants'] ?? 0;
$departure_date = $_POST['departure_date'] ?? '';
$promo_code = trim($_POST['promo_code'] ?? '');

try {
    // Truy vấn lại giá tour từ DB để đảm bảo tính chính xác và bảo mật
    $stmtTour = $pdo->prepare("SELECT title, price_base, price_child, price_infant, discount_code, discount_percent FROM tours WHERE id = ?");
    $stmtTour->execute([$tour_id]);
    $tour = $stmtTour->fetch();

    if (!$tour) throw new Exception("Tour không tồn tại.");

    // Tính toán lại tổng tiền dựa trên giá gốc trong Database
    $total_price = ($num_adults * $tour['price_base']) + 
                   ($num_children * ($tour['price_child'] ?? 0)) + 
                   ($num_infants * ($tour['price_infant'] ?? 0));

    if ($total_price <= 0) throw new Exception("Số tiền thanh toán không hợp lệ.");

    // Check promo code and calculate final price
    if (!empty($promo_code)) {
        $stmtP = $pdo->prepare("SELECT p.id, p.percent FROM user_promos up JOIN promos p ON up.promo_id = p.id WHERE up.user_id = ? AND p.code = ? AND up.is_used = 0 AND (p.expiry_date IS NULL OR p.expiry_date >= CURDATE())");
        $stmtP->execute([$user_id, $promo_code]);
        $promoData = $stmtP->fetch();
        
        if ($promoData) {
            $discount_amount = ($total_price * $promoData['percent']) / 100;
            $total_price -= $discount_amount;
            $upd = $pdo->prepare("UPDATE user_promos SET is_used = 1 WHERE user_id = ? AND promo_id = ?");
            $upd->execute([$user_id, $promoData['id']]);
        }
    }

    $amount = (string)(int)$total_price;

    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, tour_id, customer_name, customer_phone, customer_email, total_price, num_adults, num_children, num_infants, departure_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$user_id, $tour_id, $customer_name, $customer_phone, $customer_email, $total_price, $num_adults, $num_children, $num_infants, $departure_date]);
    $booking_id = $pdo->lastInsertId();

    $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
    $partnerCode = 'MOMOBKUN20180529'; // Mã đối tác (Ví dụ)
    $accessKey = 'klm05E67vDtm98Rz';     // Access Key (Ví dụ)
    $secretKey = 'at67qH6mk8w5Y1SVR0E477sqLE0W9f7a'; // Secret Key (Ví dụ)

    $orderId = (string)$booking_id;
    $orderInfo = "Thanh_toan_Tour_" . $booking_id;
    $redirectUrl = "http://localhost/travel_booking/profile.php";
    $ipnUrl = "http://localhost/travel_booking/momo_ipn.php";
    $requestId = time() . "";
    $requestType = "captureWallet";
    $extraData = "";

    // Construct raw signature string in alphabetical order
    $rawHash = "accessKey=" . $accessKey .
        "&amount=" . $amount .
        "&extraData=" . $extraData .
        "&ipnUrl=" . $ipnUrl .
        "&orderId=" . $orderId .
        "&orderInfo=" . $orderInfo .
        "&partnerCode=" . $partnerCode .
        "&redirectUrl=" . $redirectUrl .
        "&requestId=" . $requestId .
        "&requestType=" . $requestType;

    $signature = hash_hmac("sha256", $rawHash, $secretKey);

    $data = array(
        'partnerCode' => $partnerCode,
        'requestId' => $requestId,
        'amount' => (int)$amount,
        'orderId' => $orderId,
        'orderInfo' => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl' => $ipnUrl,
        'extraData' => $extraData,
        'requestType' => $requestType,
        'signature' => $signature,
        'lang' => 'vi'
    );

    $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJson);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Thêm dòng này để bỏ qua kiểm tra SSL trên localhost
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($dataJson)));
    $result = curl_exec($ch);
    $jsonResult = json_decode($result, true);
    curl_close($ch);

    if (isset($jsonResult['payUrl'])) {
        header("Location: " . $jsonResult['payUrl']);
    } else {
        throw new Exception("Lỗi kết nối MoMo: " . ($jsonResult['message'] ?? 'Unknown error'));
    }
    exit;
} catch (Exception $e) {
    die("Lỗi hệ thống: " . $e->getMessage());
}