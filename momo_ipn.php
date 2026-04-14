<?php
require_once 'config.php';

// Ghi log mọi yêu cầu IPN nhận được để dễ dàng debug
file_put_contents('momo_ipn_log.txt', "[" . date('Y-m-d H:i:s') . "] IPN Request: " . file_get_contents('php://input') . "\n", FILE_APPEND);

// CẤU HÌNH MOMO (Phải giống với cấu hình trong booking_process.php)
$partnerCode = 'MOMOBKUN20180529'; // Mã đối tác (Ví dụ)
$accessKey = 'klm05E67vDtm98Rz';     // Access Key (Ví dụ)
$secretKey = 'at67qH6mk8w5Y1SVR0E477sqLE0W9f7a'; // Secret Key (Ví dụ)

// Lấy dữ liệu IPN từ MoMo
$ipnData = json_decode(file_get_contents('php://input'), true);

if (empty($ipnData)) {
    // Phản hồi lỗi nếu không có dữ liệu
    echo json_encode(['message' => 'Empty IPN data', 'resultCode' => 1]);
    exit;
}

$message = '';
$resultCode = 0; // 0 là thành công, khác 0 là lỗi

try {
    // 1. Lấy các tham số cần thiết từ IPN data
    $partnerCode = $ipnData['partnerCode'];
    $orderId = $ipnData['orderId'];
    $requestId = $ipnData['requestId'];
    $amount = $ipnData['amount'];
    $orderInfo = $ipnData['orderInfo'];
    $orderType = $ipnData['orderType'];
    $transId = $ipnData['transId'];
    $resultCode = $ipnData['resultCode'];
    $message = $ipnData['message'];
    $payType = $ipnData['payType'];
    $responseTime = $ipnData['responseTime'];
    $extraData = $ipnData['extraData'];
    $signature = $ipnData['signature'];

    // 2. Tạo chữ ký để xác thực
    // Sắp xếp theo thứ tự bảng chữ cái: accessKey, amount, extraData, message, orderId, orderInfo, orderType, partnerCode, payType, requestId, responseTime, resultCode, transId
    $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&message=" . $message . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&orderType=" . $orderType . "&partnerCode=" . $partnerCode . "&payType=" . $payType . "&requestId=" . $requestId . "&responseTime=" . $responseTime . "&resultCode=" . $resultCode . "&transId=" . $transId;
    $expectedSignature = hash_hmac("sha256", $rawHash, $secretKey);

    // 3. Xác thực chữ ký
    if ($signature !== $expectedSignature) {
        throw new Exception("Invalid signature: " . $signature);
    }

    // 4. Xử lý kết quả thanh toán
    if ($resultCode == 0) { // Thanh toán thành công
        $booking_id = (int)$orderId;

        // Cập nhật trạng thái đơn hàng trong DB
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'completed', payment_method = 'MoMo', transaction_id = ? WHERE id = ? AND status = 'pending'");
        $stmt->execute([$transId, $booking_id]);

        if ($stmt->rowCount() > 0) {
            // Lấy thông tin booking để gửi email và thông báo
            $info = $pdo->prepare("SELECT b.*, t.title FROM bookings b JOIN tours t ON b.tour_id = t.id WHERE b.id = ?");
            $info->execute([$booking_id]);
            $booking = $info->fetch();

            if ($booking) {
                // Logic tích điểm và nâng hạng khi hoàn tất tour
                // (Đoạn code này đã có trong admin/booking_process.php, bạn có thể tái sử dụng hoặc gọi một hàm chung)
                $points_earned = floor($booking['total_price'] / 100000); 
                $pdo->prepare("UPDATE users SET loyalty_points = loyalty_points + ? WHERE id = ?")->execute([$points_earned, $booking['user_id']]);
                $pdo->prepare("UPDATE users u SET rank_id = (SELECT id FROM ranks r WHERE u.loyalty_points >= r.min_points ORDER BY r.min_points DESC LIMIT 1) WHERE id = ?")->execute([$booking['user_id']]);
                
                // Lấy thông tin user sau khi cập nhật (điểm và hạng mới) để kiểm tra thăng hạng
                $new_user_data_stmt = $pdo->prepare("SELECT u.loyalty_points, u.rank_id, r.name as rank_name, r.rank_up_promo_code FROM users u JOIN ranks r ON u.rank_id = r.id WHERE u.id = ?");
                $new_user_data_stmt->execute([$booking['user_id']]);
                $new_user_data = $new_user_data_stmt->fetch();
                $new_rank_id = $new_user_data['rank_id'];
                $rank_up_promo_code = $new_user_data['rank_up_promo_code'];

                // Gửi thông báo cho người dùng
                $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$booking['user_id'], "Thanh toán thành công!", "Đơn hàng #$booking_id đã được thanh toán 100% qua MoMo. Chúc bạn có chuyến đi vui vẻ!", "payment", "profile.php?tab=tours"]);
                
                // Gửi email xác nhận thanh toán cho khách hàng
                $to = $booking['customer_email'];
                $subject = "=?UTF-8?B?".base64_encode("Xác nhận thanh toán Tour: " . $booking['title'])."?=";
                $message_email = "
                    <html>
                    <body style='font-family: Arial, sans-serif; color: #334155; padding: 20px;'>
                        <div style='max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 20px; overflow: hidden; background: #ffffff;'>
                            <div style='background: #10b981; padding: 30px; text-align: center;'>
                                <h2 style='color: white; margin: 0; text-transform: uppercase; font-size: 20px;'>THANH TOÁN THÀNH CÔNG</h2>
                            </div>
                            <div style='padding: 40px;'>
                                <p style='font-size: 16px;'>Chào <b>{$booking['customer_name']}</b>,</p>
                                <p>Lily Travel xác nhận bạn đã thanh toán thành công 100% cho đơn hàng <b>#{$booking_id}</b> qua MoMo.</p>
                                <div style='background: #f8fafc; border: 2px solid #e2e8f0; padding: 20px; border-radius: 15px; text-align: center; margin: 25px 0;'>
                                    <div style='font-size: 12px; color: #64748b; margin-bottom: 5px;'>TRẠNG THÁI HIỆN TẠI</div>
                                    <div style='color: #10b981; font-weight: 900; font-size: 22px; text-transform: uppercase; font-style: italic;'>ĐÃ HOÀN TẤT</div>
                                </div>
                                <table style='width: 100%; font-size: 14px; color: #64748b;'>
                                    <tr><td style='padding: 8px 0;'>Chuyến đi:</td><td style='text-align: right; font-weight: 700;'>{$booking['title']}</td></tr>
                                    <tr><td style='padding: 8px 0;'>Tổng cộng:</td><td style='text-align: right; font-weight: 700; color: #2563eb;'>" . number_format($booking['total_price'], 0, ',', '.') . "đ</td></tr>
                                    <tr><td style='padding: 8px 0;'>Mã giao dịch MoMo:</td><td style='text-align: right; font-weight: 700;'>$transId</td></tr>
                                </table>
                                <p style='margin-top: 30px; font-size: 13px; color: #64748b;'>Cảm ơn bạn đã tin tưởng Lily Travel. Chúc bạn có một chuyến đi thật vui vẻ và đáng nhớ!</p>
                                <div style='text-align: center; margin-top: 40px;'>
                                    <a href='http://localhost/travel_booking/profile.php?tab=tours' style='background: #1e293b; color: #ffffff; padding: 15px 30px; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 12px;'>XEM CHI TIẾT ĐƠN HÀNG</a>
                                </div>
                            </div>
                            <div style='background: #f8fafc; padding: 20px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #f1f5f9;'>
                                Lily Travel - Kiến tạo hành trình mơ ước của bạn
                            </div>
                        </div>
                    </body>
                    </html>
                ";
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: Lily Travel <noreply@lilytravel.com>" . "\r\n";
                @mail($to, $subject, $message_email, $headers);
            }
        }
    } else {
        // Thanh toán thất bại hoặc bị hủy
        // Có thể cập nhật trạng thái đơn hàng thành 'failed' hoặc 'cancelled' nếu cần
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'failed', payment_method = 'MoMo', transaction_id = ? WHERE id = ? AND status = 'pending'");
        $stmt->execute([$transId, (int)$orderId]);
    }

} catch (Exception $e) {
    $message = $e->getMessage();
    $resultCode = 1; // Đánh dấu lỗi nội bộ
}

// Phản hồi lại MoMo để xác nhận đã nhận IPN
echo json_encode(['message' => $message, 'resultCode' => $resultCode]);
?>