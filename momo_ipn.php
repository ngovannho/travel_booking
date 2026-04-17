<?php
require_once 'config.php';
require_once 'mail_helper.php';

header("content-type: application/json; charset=UTF-8");
http_response_code(200); //200 - Everything will be 200 Oke

if (!empty($_POST)) {
    $response = array();
    try {
        // Cấu hình mã bí mật (Phải khớp với booking_process.php)
        $accessKey = "klm05TvNBzhg7h7j";
        $secretKey = "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa";

        $partnerCode = $_POST["partnerCode"];
		$orderId = $_POST["orderId"];
		$requestId = $_POST["requestId"];
		$amount = $_POST["amount"];	
		$orderInfo = $_POST["orderInfo"];
		$orderType = $_POST["orderType"];
		$transId = $_POST["transId"];
		$resultCode = $_POST["resultCode"];
		$message = $_POST["message"];
		$payType = $_POST["payType"];
		$responseTime = $_POST["responseTime"];
		$extraData = $_POST["extraData"];
		$m2signature = $_POST["signature"]; //MoMo signature
		

		//Checksum
		$rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&message=" . $message . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo .
			"&orderType=" . $orderType . "&partnerCode=" . $partnerCode . "&payType=" . $payType . "&requestId=" . $requestId . "&responseTime=" . $responseTime .
			"&resultCode=" . $resultCode . "&transId=" . $transId;

        $partnerSignature = hash_hmac("sha256", $rawHash, $secretKey);

        if ($m2signature == $partnerSignature) {
            if ($resultCode == '0') {
                // 1. Lấy ID đơn hàng từ orderId (Tách lấy phần trước dấu gạch dưới)
                $booking_id = explode("_", $orderId)[0];

                // 2. Cập nhật trạng thái đơn hàng trong DB
                $stmtUpdate = $pdo->prepare("UPDATE bookings SET status = 'completed', momo_trans_id = ? WHERE id = ? AND status = 'pending'");
                $stmtUpdate->execute([$transId, $booking_id]);

                // 3. Lấy thông tin khách hàng và tour để gửi email
                $stmtInfo = $pdo->prepare("SELECT b.*, t.title, t.image, t.duration, t.departure_location, t.content FROM bookings b JOIN tours t ON b.tour_id = t.id WHERE b.id = ?");
                $stmtInfo->execute([$booking_id]);
                $booking = $stmtInfo->fetch();

                if ($booking) {
                    // 4. Gửi email cảm ơn
                    $to = $booking['customer_email'];
                    $subject = "Cảm ơn bạn đã đặt tour tại Lily Travel: " . $booking['title'];
                    
                    $message = "
                        <html>
                        <body style='font-family: Arial, sans-serif; color: #334155; padding: 20px;'>
                            <div style='max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 24px; overflow: hidden; background: #ffffff; shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);'>
                                <div style='background: #2563eb; padding: 40px; text-align: center;'>
                                    <div style='color: #fbbf24; font-size: 24px; font-weight: 900; font-style: italic; margin-bottom: 10px;'>LILY-TRAVEL</div>
                                    <h2 style='color: white; margin: 0; text-transform: uppercase; font-size: 18px; letter-spacing: 2px;'>Thanh toán thành công</h2>
                                </div>
                                <div style='padding: 40px;'>
                                    <p style='font-size: 16px;'>Xin chào <b>{$booking['customer_name']}</b>,</p>
                                    <p>Cảm ơn bạn đã tin tưởng lựa chọn Lily Travel. Giao dịch qua MoMo cho đơn hàng <b>#{$booking_id}</b> đã được xác nhận thành công.</p>
                                    
                                    <!-- QR Code Check-in -->
                                    <div style='text-align: center; margin: 30px 0;'>
                                        <p style='font-size: 10px; color: #94a3b8; text-transform: uppercase; margin-bottom: 10px;'>Mã QR Check-in của bạn</p>
                                        <img src='https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=$booking_id' alt='QR Code Check-in' style='border: 4px solid #f1f5f9; border-radius: 15px;'>
                                    </div>
                                    
                                    <div style='background: #f8fafc; border-radius: 20px; padding: 25px; margin: 30px 0; border: 1px solid #f1f5f9;'>
                                        <table style='width: 100%; border-collapse: collapse;'>
                                            <tr>
                                                <td style='padding-bottom: 15px; color: #64748b; font-size: 12px; text-transform: uppercase;'>Chuyến đi của bạn</td>
                                                <td style='padding-bottom: 15px; text-align: right; font-weight: 700; color: #0f172a;'>{$booking['title']}</td>
                                            </tr>
                                            <tr>
                                                <td style='padding-bottom: 15px; color: #64748b; font-size: 12px; text-transform: uppercase;'>Thời lượng / Khởi hành từ</td>
                                                <td style='padding-bottom: 15px; text-align: right; font-weight: 700; color: #0f172a;'>{$booking['duration']} / {$booking['departure_location']}</td>
                                            </tr>
                                            <tr>
                                                <td style='padding-bottom: 15px; color: #64748b; font-size: 12px; text-transform: uppercase;'>Ngày khởi hành</td>
                                                <td style='padding-bottom: 15px; text-align: right; font-weight: 700; color: #0f172a;'>{$booking['departure_date']}</td>
                                            </tr>
                                            <tr>
                                                <td style='padding-bottom: 15px; color: #64748b; font-size: 12px; text-transform: uppercase;'>Số lượng khách</td>
                                                <td style='padding-bottom: 15px; text-align: right; font-weight: 700; color: #0f172a;'>{$booking['num_adults']} NL, {$booking['num_children']} TE, {$booking['num_infants']} NCT</td>
                                            </tr>
                                            <tr>
                                                <td style='padding-bottom: 15px; color: #64748b; font-size: 12px; text-transform: uppercase;'>Số điện thoại</td>
                                                <td style='padding-bottom: 15px; text-align: right; font-weight: 700; color: #0f172a;'>{$booking['customer_phone']}</td>
                                            </tr>
                                            <tr>
                                                <td style='padding-top: 15px; border-top: 1px dashed #e2e8f0; color: #64748b; font-size: 12px; text-transform: uppercase;'>Tổng thanh toán</td>
                                                <td style='padding-top: 15px; border-top: 1px dashed #e2e8f0; text-align: right; font-weight: 900; color: #2563eb; font-size: 18px;'>" . number_format($booking['total_price'], 0, ',', '.') . "đ</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div style='margin-top: 30px; padding: 25px; background: #f8fafc; border-radius: 20px; border: 1px solid #f1f5f9;'>
                                        <h3 style='font-size: 14px; font-weight: 900; color: #2563eb; text-transform: uppercase; margin-bottom: 15px;'>Lịch trình chi tiết</h3>
                                        <div style='font-size: 13px; line-height: 1.8; color: #334155;'>{$booking['content']}</div>
                                    </div>

                                    <p style='font-size: 14px; line-height: 1.6;'>Hệ thống đã tự động ghi nhận lịch trình của bạn. Bạn có thể xem lại chi tiết vé và nhận thông báo mới nhất tại trang cá nhân.</p>
                                    
                                    <div style='text-align: center; margin-top: 40px;'>
                                        <a href='" . rtrim(BASE_URL, '/') . "/profile.php?tab=tours&booking_id=$booking_id' style='background: #0f172a; color: #ffffff; padding: 18px 35px; text-decoration: none; border-radius: 15px; font-weight: 900; font-size: 11px; letter-spacing: 1px; display: inline-block; text-transform: uppercase;'>CHI TIẾT ĐƠN HÀNG</a>
                                    </div>
                                </div>
                                <div style='background: #f8fafc; padding: 30px; text-align: center; border-top: 1px solid #f1f5f9;'>
                                    <p style='margin: 0; font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 2px;'>Lily Travel - Hành trình di sản của bạn</p>
                                    <p style='margin: 10px 0 0; font-size: 10px; color: #cbd5e1;'>Hotline: 0777454550 | Email: contact@lilytravel.com</p>
                                </div>
                            </div>
                        </body>
                        </html>
                    ";

                    sendEmail($to, $booking['customer_name'], $subject, $message);

                    // 5. Logic tích điểm (Tương tự admin/booking_process.php)
                    $points_earned = floor($booking['total_price'] / 100000); 
                    $pdo->prepare("UPDATE users SET loyalty_points = loyalty_points + ? WHERE id = ?")
                        ->execute([$points_earned, $booking['user_id']]);
                    
                    // Cập nhật hạng
                    $pdo->prepare("UPDATE users u 
                                   SET rank_id = (SELECT id FROM ranks r WHERE u.loyalty_points >= r.min_points ORDER BY r.min_points DESC LIMIT 1)
                                   WHERE id = ?")
                        ->execute([$booking['user_id']]);

                    // Thêm thông báo trên hệ thống
                    $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)")
                        ->execute([
                            $booking['user_id'], 
                            "Thanh toán thành công!", 
                            "Đơn hàng #$booking_id đã được thanh toán qua MoMo. Chúc bạn có chuyến đi vui vẻ!", 
                            "payment", 
                            "profile.php?tab=tours"
                        ]);
                }

                $response['message'] = "Received payment result success";
            } else {
                $response['message'] = "Payment failed with resultCode: " . $resultCode;
            }
        } else {
            $response['message'] = "ERROR! Fail checksum";
        }

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    echo json_encode($response);
}
?>