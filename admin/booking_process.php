<?php
require_once '../config.php';
require_once '../mail_helper.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    exit('Access Denied');
}

$id = (int)$_GET['id'];
$action = $_GET['action'];

if ($action === 'confirm') {
    $status = 'confirmed';
} elseif ($action === 'cancel') {
    $status = 'cancelled';
    // 1. Kiểm tra xem đơn hàng đã thanh toán 'completed' chưa để thực hiện hoàn tiền MoMo
    $stmtCheck = $pdo->prepare("SELECT status, total_price, momo_trans_id, user_id FROM bookings WHERE id = ?");
    $stmtCheck->execute([$id]);
    $bookingRefund = $stmtCheck->fetch();

    if ($bookingRefund && $bookingRefund['status'] === 'completed' && !empty($bookingRefund['momo_trans_id'])) {
        // THỰC HIỆN GỌI API HOÀN TIỀN CỦA MOMO
        $refundEndpoint = "https://test-payment.momo.vn/v2/gateway/api/refund";
        $partnerCode = "MOMOBKUN20180529";
        $accessKey   = "klm05TvNBzhg7h7j"; 
        $secretKey   = "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa"; 

        $refundOrderId = "refund_" . $id . "_" . time();
        $refundRequestId = (string)time();
        $refundAmount = (int)$bookingRefund['total_price'];
        $description = "Hoàn tiền cho đơn hàng #" . $id . " do hệ thống hủy.";
        $transId = $bookingRefund['momo_trans_id']; // Mã giao dịch gốc từ IPN lưu lại

        $rawHashRefund = "accessKey=" . $accessKey .
                         "&amount=" . $refundAmount .
                         "&description=" . $description .
                         "&orderId=" . $refundOrderId .
                         "&partnerCode=" . $partnerCode .
                         "&requestId=" . $refundRequestId .
                         "&transId=" . $transId;

        $refundSignature = hash_hmac("sha256", $rawHashRefund, $secretKey);

        $refundData = array(
            'partnerCode' => $partnerCode,
            'requestId'   => $refundRequestId,
            'amount'      => $refundAmount,
            'orderId'     => $refundOrderId,
            'transId'     => $transId,
            'description' => $description,
            'signature'   => $refundSignature,
            'lang'        => 'vi'
        );

        $ch = curl_init($refundEndpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($refundData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $refundResult = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!isset($refundResult['resultCode']) || $refundResult['resultCode'] != 0) {
            $_SESSION['error'] = "Lỗi hoàn tiền MoMo: " . ($refundResult['message'] ?? 'Không xác định');
            header("Location: bookings.php");
            exit;
        }
        
        // Trừ lại điểm loyalty nếu đơn hàng bị hủy
        $points_to_subtract = floor($bookingRefund['total_price'] / 100000);
        $pdo->prepare("UPDATE users SET loyalty_points = GREATEST(0, loyalty_points - ?) WHERE id = ?")
            ->execute([$points_to_subtract, $bookingRefund['user_id']]);
        
        // Cập nhật lại hạng thành viên
        $pdo->prepare("UPDATE users u 
                       SET rank_id = (SELECT id FROM ranks r WHERE u.loyalty_points >= r.min_points ORDER BY r.min_points DESC LIMIT 1)
                       WHERE id = ?")
            ->execute([$bookingRefund['user_id']]);

        $status = 'refunded';
    }
}
elseif ($action === 'complete') {

    // Kiểm tra trạng thái hiện tại
    $check = $pdo->prepare("SELECT status FROM bookings WHERE id = ?");
    $check->execute([$id]);
    $current = trim($check->fetchColumn());

    $status = 'completed';
} else {
    header("Location: bookings.php");
    exit;
}

$stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
$stmt->execute([$status, $id]);

// Lấy thông tin khách hàng để gửi email cập nhật
$info = $pdo->prepare("SELECT b.*, t.title FROM bookings b JOIN tours t ON b.tour_id = t.id WHERE b.id = ?");
$info->execute([$id]);
$booking = $info->fetch();

    // Logic tích điểm và nâng hạng khi hoàn tất tour (chỉ chạy khi trạng thái là 'completed')
    if ($status === 'completed') {
        // 1. Lấy thông tin user hiện tại (điểm và hạng) trước khi cập nhật
        $prev_user_data_stmt = $pdo->prepare("SELECT u.loyalty_points, u.rank_id, r.name as rank_name FROM users u JOIN ranks r ON u.rank_id = r.id WHERE u.id = ?");
        $prev_user_data_stmt->execute([$booking['user_id']]);
        $prev_user_data = $prev_user_data_stmt->fetch();
        $prev_rank_id = $prev_user_data['rank_id'];
        $prev_rank_name = $prev_user_data['rank_name'];

        // 2. Tính điểm tích lũy mới: 1,000,000 VND = 10 điểm (tức 100,000 VND = 1 điểm)
        $points_earned = floor($booking['total_price'] / 100000); 
        
        // 3. Cập nhật điểm tích lũy
        $pdo->prepare("UPDATE users SET loyalty_points = loyalty_points + ? WHERE id = ?")
            ->execute([$points_earned, $booking['user_id']]);
            
        // 4. Kiểm tra và cập nhật hạng dựa trên tổng điểm mới
        // Lấy rank_id mới sau khi cộng điểm
        $pdo->prepare("UPDATE users u 
                       SET rank_id = (SELECT id FROM ranks r WHERE u.loyalty_points >= r.min_points ORDER BY r.min_points DESC LIMIT 1)
                       WHERE id = ?")
            ->execute([$booking['user_id']]);
        
        // 5. Lấy thông tin user sau khi cập nhật (điểm và hạng mới)
        $new_user_data_stmt = $pdo->prepare("SELECT u.loyalty_points, u.rank_id, r.name as rank_name, r.rank_up_promo_code FROM users u JOIN ranks r ON u.rank_id = r.id WHERE u.id = ?");
        $new_user_data_stmt->execute([$booking['user_id']]);
        $new_user_data = $new_user_data_stmt->fetch();
        $new_rank_id = $new_user_data['rank_id'];
        $new_rank_name = $new_user_data['rank_name'];
        $rank_up_promo_code = $new_user_data['rank_up_promo_code'];

        // 6. Logic tạo mã giảm giá khi thăng hạng
        if ($new_rank_id > $prev_rank_id && !empty($rank_up_promo_code)) {
            // Lấy promo_id từ bảng promos dựa trên rank_up_promo_code
            $promo_id_stmt = $pdo->prepare("SELECT id FROM promos WHERE code = ?");
            $promo_id_stmt->execute([$rank_up_promo_code]);
            $promo_id = $promo_id_stmt->fetchColumn();

            // Gán mã giảm giá cho người dùng nếu chưa có
            $pdo->prepare("INSERT IGNORE INTO user_promos (user_id, promo_id, is_used) VALUES (?, ?, 0)")
                ->execute([$booking['user_id'], $promo_id]);
            
            // Gửi thông báo cho người dùng về việc thăng hạng và nhận mã
            $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)")
                ->execute([$booking['user_id'], "Chúc mừng thăng hạng!", "Bạn đã thăng hạng lên <b>$new_rank_name</b> và nhận được mã ưu đãi <b>$rank_up_promo_code</b>!", "rank_up", "profile.php?tab=promos"]);
        }
    }

if ($booking) {
    // Xác định nội dung thông báo theo yêu cầu người dùng
    $notif_msg = "Đơn hàng #$id đã được cập nhật trạng thái.";
    if ($status === 'confirmed') $notif_msg = "Lily Travel đã xác nhận thanh toán của bạn cho đơn hàng #$id. Chỗ của bạn đã được giữ!";
    if ($status === 'completed') $notif_msg = "Chuyến đi #$id của bạn đã hoàn tất! Hãy dành chút thời gian để lại đánh giá để giúp chúng tôi phục vụ tốt hơn nhé.";
    if ($status === 'cancelled') $notif_msg = "Rất tiếc, đơn hàng #$id của bạn đã bị hủy trên hệ thống.";
    if ($status === 'refunded') $notif_msg = "Đơn hàng #$id của bạn đã được hủy và hoàn tiền thành công qua MoMo.";

    // Thêm thông báo cho người dùng (Sử dụng dữ liệu đã fetch để tối ưu hiệu năng)
    $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)")
        ->execute([$booking['user_id'], "Cập nhật đơn hàng #$id", $notif_msg, "payment", "profile.php?tab=tours"]);

    $status_names = [
        'confirmed' => 'Đã thanh toán (100%)',
        'completed' => 'Đã thanh toán đủ (100%)',
        'cancelled' => 'Đã hủy',
        'refunded' => 'Đã hoàn tiền'
    ];
    $current_status = $status_names[$status] ?? $status;

    $to = $booking['customer_email'];
    $subject = "Cập nhật trạng thái đơn hàng #" . $id . ": " . $booking['title'];
    
    // Xác định màu sắc chủ đạo dựa trên trạng thái
    $header_color = $status === 'completed' ? '#10b981' : ($status === 'confirmed' ? '#2563eb' : ($status === 'refunded' ? '#ef4444' : '#64748b'));
    $status_title = $status === 'completed' ? 'CHUYẾN ĐI HOÀN TẤT' : 
                   ($status === 'confirmed' ? 'XÁC NHẬN THANH TOÁN' : 
                   ($status === 'refunded' ? 'ĐÃ HOÀN TIỀN' : 'CẬP NHẬT ĐƠN HÀNG'));

    $message = "
        <html>
        <body style='font-family: Arial, sans-serif; color: #334155; padding: 20px;'>
            <div style='max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 20px; overflow: hidden; background: #ffffff;'>
                <div style='background: $header_color; padding: 30px; text-align: center;'>
                    <h2 style='color: white; margin: 0; text-transform: uppercase; font-size: 20px;'>$status_title</h2>
                </div>
                <div style='padding: 40px;'>
                    <p style='font-size: 16px;'>Chào <b>{$booking['customer_name']}</b>,</p>
                    <p>Lily Travel thông báo trạng thái mới cho đơn hàng <b>#{$id}</b>:</p>
                    <div style='background: #f8fafc; border: 2px solid #e2e8f0; padding: 20px; border-radius: 15px; text-align: center; margin: 25px 0;'>
                        <div style='font-size: 12px; color: #64748b; margin-bottom: 5px;'>TRẠNG THÁI HIỆN TẠI</div>
                        <div style='color: $header_color; font-weight: 900; font-size: 22px; text-transform: uppercase; font-style: italic;'>$current_status</div>
                    </div>

                    " . ($status === 'completed' ? "
                    <div style='background: #fffbeb; border: 1px solid #fde68a; padding: 20px; border-radius: 15px; text-align: center; margin: 25px 0;'>
                        <p style='color: #92400e; font-size: 14px; font-weight: 700; margin: 0;'>BẠN CÓ HÀI LÒNG VỚI CHUYẾN ĐI?</p>
                        <p style='color: #b45309; font-size: 11px; margin-top: 5px;'>Đánh giá ngay để nhận thêm điểm tích lũy vào tài khoản nhé!</p>
                    </div>
                    " : "") . "

                    <!-- QR Code Check-in -->
                    <div style='text-align: center; margin: 25px 0;'>
                        <p style='font-size: 10px; color: #94a3b8; text-transform: uppercase; margin-bottom: 10px;'>Mã QR Check-in</p>
                        <img src='https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=$id' alt='QR Code Check-in' style='border: 4px solid #f1f5f9; border-radius: 15px;'>
                    </div>
                    <div style='background: #fdfdfd; padding: 20px; border-radius: 12px; border: 1px solid #f1f5f9;'>
                        <table style='width: 100%; font-size: 14px;'>
                            <tr><td style='padding: 8px 0; color: #94a3b8;'>Chuyến đi:</td><td style='text-align: right; font-weight: 700;'>{$booking['title']}</td></tr>
                            <tr><td style='padding: 8px 0; color: #94a3b8;'>" . ($status === 'refunded' ? 'Số tiền hoàn lại:' : 'Tổng cộng:') . "</td><td style='text-align: right; font-weight: 700; color: " . ($status === 'refunded' ? '#ef4444' : '#2563eb') . ";'>" . number_format($booking['total_price'], 0, ',', '.') . "đ</td></tr>
                        </table>
                    </div>
                    <p style='margin-top: 30px; font-size: 13px; color: #64748b;'>" . ($status === 'refunded' ? 'Số tiền đã được hoàn về nguồn thanh toán ban đầu (ví MoMo hoặc thẻ ngân hàng).' : ($status === 'completed' ? 'Cảm ơn bạn đã lựa chọn dịch vụ của Lily Travel. Hẹn gặp lại bạn trong những hành trình tiếp theo!' : 'Chúng tôi đang chuẩn bị những trải nghiệm tuyệt vời nhất cho bạn.')) . "</p>
                    <div style='text-align: center; margin-top: 40px;'>
                        <a href='" . rtrim(BASE_URL, '/') . "/profile.php?tab=tours&booking_id=$id' style='background: #1e293b; color: #ffffff; padding: 15px 30px; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 12px;'>CHI TIẾT ĐƠN HÀNG</a>
                    </div>
                </div>
                <div style='background: #f8fafc; padding: 20px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #f1f5f9;'>
                    Lily Travel - Kiến tạo hành trình mơ ước của bạn
                </div>
            </div>
        </body>
        </html>
    ";
    sendEmail($to, $booking['customer_name'], $subject, $message);
}

$_SESSION['success'] = "Cập nhật tour thành công!";
header("Location: bookings.php");
exit;