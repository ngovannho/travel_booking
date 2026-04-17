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
$payment_method = $_POST['payment_method'] ?? 'momo';

try {
    $pdo->beginTransaction();

    // Truy vấn lại giá tour từ DB để đảm bảo tính chính xác và bảo mật
    $stmtTour = $pdo->prepare("SELECT title, price_base, price_child, price_infant, max_people, max_participants FROM tours WHERE id = ? AND status = 1");
    $stmtTour->execute([$tour_id]);
    $tour = $stmtTour->fetch();

    if (!$tour) throw new Exception("Tour không khả dụng hoặc đã ngừng nhận khách.");

    // Kiểm tra tính khả dụng của số chỗ ngồi
    $total_pax = $num_adults + $num_children + $num_infants;
    
    // Lấy tổng số người thực tế đã đặt cho ngày khởi hành này
    $stmt_booked = $pdo->prepare("SELECT SUM(num_adults + num_children + num_infants) FROM bookings WHERE tour_id = ? AND departure_date = ? AND status != 'cancelled'");
    $stmt_booked->execute([$tour_id, $departure_date]);
    $booked_seats = (int)$stmt_booked->fetchColumn();
    
    $max_capacity = ($tour['max_people'] > 0) ? (int)$tour['max_people'] : (int)$tour['max_participants'];

    if (($booked_seats + $total_pax) > $max_capacity) {
        $remaining = $max_capacity - $booked_seats;
        throw new Exception("Ngày này chỉ còn $remaining chỗ, không đủ cho đoàn của bạn ($total_pax người).");
    }

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

    // Đảm bảo số tiền là số nguyên sạch, tránh sai số float sau khi tính giảm giá
    $final_total = (int)round($total_price);
    $amount = (string)$final_total;

    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, tour_id, customer_name, customer_phone, customer_email, total_price, num_adults, num_children, num_infants, departure_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$user_id, $tour_id, $customer_name, $customer_phone, $customer_email, $final_total, $num_adults, $num_children, $num_infants, $departure_date]);
    $booking_id = $pdo->lastInsertId();

    $pdo->commit();

    if ($payment_method !== 'momo') {
        // Chuyển hướng đến trang thanh toán ngân hàng chuyên dụng
        header("Location: bank_payment.php?booking_id=$booking_id");
        exit;
    }

    // --- LOGIC THANH TOÁN MOMO ---
    $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";

    // --- GIỮ NGUYÊN PHẦN TRÊN CỦA BẠN ĐẾN ĐOẠN ĐỊNH NGHĨA BIẾN ---

// 1. Khai báo thông tin kết nối
    $partnerCode = "MOMOBKUN20180529";
    $accessKey   = "klm05TvNBzhg7h7j"; 
    $secretKey   = "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa"; 

    // 2. Chuẩn bị dữ liệu ĐỒNG NHẤT (Quan trọng: Phải gán vào biến trước khi hash)
    $orderId     = (string)$booking_id . "_" . time(); 
    $requestId   = (string)time();
    $amount      = (string)$final_total;
    $orderInfo   = "Thanh toán Tour #" . $booking_id . " - " . $tour['title']; 

    // Tự động lấy URL cơ sở để đảm bảo redirect chính xác về trang chủ
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $host = $_SERVER['HTTP_HOST'];
    $folder = dirname($_SERVER['PHP_SELF']);
    $redirectUrl = $protocol . "://" . $host . $folder . "/payment_result.php";
    $ipnUrl      = $protocol . "://" . $host . $folder . "/momo_ipn.php";

    $extraData   = "";
    $requestType = "payWithATM"; // Sử dụng phương thức thanh toán thẻ nội địa theo yêu cầu

    // 3. Xây dựng chuỗi Raw Hash (CHỈ DÙNG DẤU CHẤM ĐỂ NỐI)
    // Cấu trúc này phải khớp 100% với yêu cầu của MoMo
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

    // 4. Tạo chữ ký Signature bằng thuật toán HMAC-SHA256
    $signature = hash_hmac("sha256", $rawHash, $secretKey);

    // 5. Mảng dữ liệu gửi lên API MoMo
    $data = array(
        'partnerCode' => $partnerCode,
        'partnerName' => "LILY TRAVEL",
        'storeId'     => "LILY_TRAVEL_STORE",
        'requestId'   => $requestId,
        'amount'      => (int)$amount, // Chuyển về kiểu số nguyên cho JSON payload
        'orderId'     => $orderId,
        'orderInfo'   => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl'      => $ipnUrl,
        'lang'        => 'vi',
        'extraData'   => $extraData,
        'requestType' => $requestType,
        'signature'   => $signature
    );

    $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJson);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Thêm dòng này để bỏ qua kiểm tra SSL trên localhost
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $result = curl_exec($ch);
    $jsonResult = json_decode($result, true);
    curl_close($ch);

    // Hiển thị modal loading trước khi chuyển hướng
    echo '<!DOCTYPE html>
    <html lang="vi">
    <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lily Travel - Đang chuyển hướng...</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #0f172a; color: white; font-family: sans-serif; }</style>
    </head>
    <body>
        <div class="flex flex-col items-center">
            <div class="relative w-20 h-20 mb-8">
                <div class="absolute inset-0 border-4 border-blue-500/20 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-t-blue-500 rounded-full animate-spin"></div>
                <i class="fas fa-paper-plane absolute inset-0 flex items-center justify-center text-blue-500 animate-pulse"></i>
            </div>
            <h2 class="text-xl font-black uppercase tracking-widest italic mb-2">Đang kết nối MoMo</h2>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest opacity-60">Vui lòng không đóng trình duyệt...</p>
        </div>
    </body>
    </html>';

    if (isset($jsonResult['payUrl'])) {
        header("Location: " . $jsonResult['payUrl']);
    } else {
        throw new Exception("Cổng thanh toán MoMo đang bảo trì hoặc thông tin cấu hình sai.");
    }
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $_SESSION['error'] = "Lỗi đặt tour: " . $e->getMessage();
    header("Location: tour-detail.php?id=" . $tour_id);
    exit;
}