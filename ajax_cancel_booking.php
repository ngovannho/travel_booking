<?php
require_once 'config.php';
require_once 'mail_helper.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) exit(json_encode(['status' => 'error']));

$booking_id = (int)$_POST['booking_id'];
$user_id = $_SESSION['user']['id'];

try {
    // Lấy thông tin đơn hàng để kiểm tra tính hợp lệ
    $stmtCheck = $pdo->prepare("SELECT b.*, t.title FROM bookings b JOIN tours t ON b.tour_id = t.id WHERE b.id = ? AND b.user_id = ?");
    $stmtCheck->execute([$booking_id, $user_id]);
    $booking = $stmtCheck->fetch();

    if (!$booking || !in_array($booking['status'], ['pending', 'confirmed', 'completed'])) {
        exit(json_encode(['status' => 'error', 'message' => 'Không thể hủy đơn hàng này.']));
    }

    $new_status = 'cancelled';
    $is_momo_refund = false;
    $refund_percent = 1.0;

    // Tính toán tỷ lệ hoàn tiền thực tế (Server-side)
    $dep_date_str = $booking['departure_date'];
    $parts = explode('/', $dep_date_str);
    if (count($parts) == 2) {
        $day = (int)$parts[0];
        $month = (int)$parts[1];
        $year = (int)date('Y');
        $dep_time = mktime(0, 0, 0, $month, $day, $year);
        
        // Nếu ngày khởi hành đã qua (ví dụ sang năm mới)
        if ($dep_time < time() && (time() - $dep_time) > 2592000) {
            $dep_time = mktime(0, 0, 0, $month, $day, $year + 1);
        }
        
        // So sánh với đầu ngày hôm nay
        $days_diff = (mktime(0, 0, 0) - mktime(0, 0, 0, date('m', $dep_time), date('d', $dep_time), date('Y', $dep_time))) / 86400;
        $days_diff = abs($days_diff);

        if ($dep_time < time()) $refund_percent = 0;
        elseif ($days_diff < 2) $refund_percent = 0.5;
        else $refund_percent = 1.0;
    }

    // Thực hiện hoàn tiền MoMo nếu đơn hàng đã thanh toán xong
    if ($booking['status'] === 'completed' && !empty($booking['momo_trans_id']) && $refund_percent > 0) {
        $refundEndpoint = "https://test-payment.momo.vn/v2/gateway/api/refund";
        $partnerCode = "MOMOBKUN20180529";
        $accessKey   = "klm05TvNBzhg7h7j"; 
        $secretKey   = "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa"; 

        $refundOrderId = "refund_u_" . $booking_id . "_" . time();
        $refundRequestId = (string)time();
        $refundAmount = (int)($booking['total_price'] * $refund_percent);
        $description = "Khách hàng tự hủy đơn hàng #" . $booking_id;
        $transId = $booking['momo_trans_id'];

        $rawHashRefund = "accessKey=" . $accessKey . "&amount=" . $refundAmount . "&description=" . $description . "&orderId=" . $refundOrderId . "&partnerCode=" . $partnerCode . "&requestId=" . $refundRequestId . "&transId=" . $transId;
        $refundSignature = hash_hmac("sha256", $rawHashRefund, $secretKey);

        $refundData = [
            'partnerCode' => $partnerCode,
            'requestId'   => $refundRequestId,
            'amount'      => $refundAmount,
            'orderId'     => $refundOrderId,
            'transId'     => $transId,
            'description' => $description,
            'signature'   => $refundSignature,
            'lang'        => 'vi'
        ];

        $ch = curl_init($refundEndpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($refundData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $refundResult = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (isset($refundResult['resultCode']) && $refundResult['resultCode'] == 0) {
            $new_status = 'refunded';
            $is_momo_refund = true;
        } else {
            throw new Exception("Lỗi hoàn tiền MoMo: " . ($refundResult['message'] ?? 'Không xác định'));
        }
    }

    // Cập nhật trạng thái trong CSDL
    $stmtUpdate = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmtUpdate->execute([$new_status, $booking_id]);

    if ($stmtUpdate->rowCount() > 0) {
        // Nếu đơn hàng đã hoàn tất (đã tích điểm) thì phải trừ lại điểm
        if ($booking['status'] === 'completed') {
            $points_to_subtract = floor($booking['total_price'] / 100000);
            $pdo->prepare("UPDATE users SET loyalty_points = GREATEST(0, loyalty_points - ?) WHERE id = ?")
                ->execute([$points_to_subtract, $user_id]);
            
            $pdo->prepare("UPDATE users u SET rank_id = (SELECT id FROM ranks r WHERE u.loyalty_points >= r.min_points ORDER BY r.min_points DESC LIMIT 1) WHERE id = ?")
                ->execute([$user_id]);
        }

        // Thông báo cho Admin (giả định Admin có ID = 1)
        $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)")
            ->execute([1, "Hủy tour #$booking_id", "Khách hàng {$_SESSION['user']['fullname']} đã hủy tour từ trang cá nhân.", "payment", "admin/bookings.php"]);

        if ($booking) {
            $to = $booking['customer_email'];
            $subject = "Xác nhận hủy tour tại Lily Travel: " . $booking['title'];
            $message = "
                <html>
                <body style='font-family: Arial, sans-serif; color: #334155; padding: 20px; background: #f8fafc;'>
                    <div style='max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 24px; overflow: hidden; background: #ffffff;'>
                        <div style='background: #ef4444; padding: 40px; text-align: center;'>
                            <div style='color: #ffffff; font-size: 24px; font-weight: 900; font-style: italic; margin-bottom: 10px;'>LILY-TRAVEL</div>
                            <h2 style='color: white; margin: 0; text-transform: uppercase; font-size: 18px; letter-spacing: 2px;'>Xác nhận hủy tour</h2>
                        </div>
                        <div style='padding: 40px;'>
                            <p style='font-size: 16px;'>Xin chào <b>{$booking['customer_name']}</b>,</p>
                            <p>Chúng tôi xác nhận đơn hàng <b>#{$booking_id}</b> cho tour <b>{$booking['title']}</b> đã được hủy thành công theo yêu cầu của bạn.</p>
                            
                            <!-- QR Code (Trạng thái đã hủy) -->
                            <div style='text-align: center; margin: 25px 0; opacity: 0.5;'>
                                <img src='https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=$booking_id' alt='QR Code' style='filter: grayscale(1); border: 2px solid #f1f5f9; border-radius: 10px;'>
                            </div>
                            " . ($is_momo_refund ? "<p style='color: #10b981; font-weight: bold;'>Hệ thống đã thực hiện hoàn tiền tự động " . ($refund_percent * 100) . "% giá trị đơn hàng qua ví MoMo của bạn.</p>" : "") . "
                            
                            <div style='background: #f8fafc; border-radius: 20px; padding: 25px; margin: 30px 0; border: 1px solid #f1f5f9;'>
                                <p style='margin: 0; font-size: 12px; color: #64748b; text-transform: uppercase;'>Trạng thái đơn hàng hiện tại</p>
                                <p style='margin: 10px 0; font-size: 18px; font-weight: 900; color: #ef4444;'>ĐÃ HỦY (" . ($refund_percent * 100) . "% HOÀN TIỀN)</p>
                            </div>
                            " . ($refund_percent < 1.0 && $refund_percent > 0 ? "<p style='font-size: 12px; color: #94a3b8; font-style: italic;'>* Phí hủy 50% được áp dụng do bạn hủy tour trong vòng 2 ngày trước khởi hành.</p>" : "") . "

                            <p style='font-size: 14px; line-height: 1.6;'>Nếu đây là một nhầm lẫn hoặc bạn muốn đặt một hành trình khác, đừng ngần ngại liên hệ với chúng tôi qua hotline 0777454550.</p>
                            
                            <div style='text-align: center; margin-top: 40px;'>
                                <a href='" . BASE_URL . "tours.php' style='background: #0f172a; color: #ffffff; padding: 18px 35px; text-decoration: none; border-radius: 15px; font-weight: 900; font-size: 11px; letter-spacing: 1px; display: inline-block; text-transform: uppercase;'>Khám phá các tour khác</a>
                            </div>
                        </div>
                    </div>
                </body>
                </html>
            ";
            sendEmail($to, $booking['customer_name'], $subject, $message);
        }

        echo json_encode(['status' => 'success', 'message' => 'Đã hủy tour thành công.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Không thể hủy đơn hàng này.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}