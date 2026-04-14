<?php
require_once '../config.php';
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
    if ($status === 'confirmed') $notif_msg = "Lily Travel đã xác nhận khoản cọc 30% của bạn cho đơn hàng #$id. Chỗ của bạn đã được giữ!";
    if ($status === 'completed') $notif_msg = "Tuyệt vời! Chúng tôi đã xác nhận thanh toán 100% cho tour #$id. Chuẩn bị hành lý và lên đường thôi!";
    if ($status === 'cancelled') $notif_msg = "Rất tiếc, đơn hàng #$id của bạn đã bị hủy trên hệ thống.";

    // Thêm thông báo cho người dùng (Sử dụng dữ liệu đã fetch để tối ưu hiệu năng)
    $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)")
        ->execute([$booking['user_id'], "Cập nhật đơn hàng #$id", $notif_msg, "payment", "profile.php?tab=tours"]);

    $status_names = [
        'confirmed' => 'Xác nhận đã cọc (30%)',
        'completed' => 'Đã thanh toán đủ (100%)',
        'cancelled' => 'Đã hủy'
    ];
    $current_status = $status_names[$status] ?? $status;

    $to = $booking['customer_email'];
    $subject = "=?UTF-8?B?".base64_encode("Cập nhật trạng thái Tour: " . $booking['title'])."?=";
    
    // Xác định màu sắc chủ đạo dựa trên trạng thái
    $header_color = $status === 'completed' ? '#10b981' : ($status === 'confirmed' ? '#2563eb' : '#64748b');
    $status_title = $status === 'completed' ? 'THANH TOÁN THÀNH CÔNG' : ($status === 'confirmed' ? 'XÁC NHẬN ĐÃ NHẬN CỌC' : 'CẬP NHẬT ĐƠN HÀNG');

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
                    <div style='background: #fdfdfd; padding: 20px; border-radius: 12px; border: 1px solid #f1f5f9;'>
                        <table style='width: 100%; font-size: 14px;'>
                            <tr><td style='padding: 8px 0; color: #94a3b8;'>Chuyến đi:</td><td style='text-align: right; font-weight: 700;'>{$booking['title']}</td></tr>
                            <tr><td style='padding: 8px 0; color: #94a3b8;'>Tổng cộng:</td><td style='text-align: right; font-weight: 700; color: #2563eb;'>" . number_format($booking['total_price'], 0, ',', '.') . "đ</td></tr>
                        </table>
                    </div>
                    <p style='margin-top: 30px; font-size: 13px; color: #64748b;'>Chúng tôi đang chuẩn bị những trải nghiệm tuyệt vời nhất cho bạn. Hẹn gặp lại sớm!</p>
                    <div style='text-align: center; margin-top: 40px;'>
                        <a href='http://localhost/travel_booking/profile.php?tab=tours' style='background: #1e293b; color: #ffffff; padding: 15px 30px; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 12px;'>CHI TIẾT ĐƠN HÀNG</a>
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

    @mail($to, $subject, $message, $headers);
}

$_SESSION['success'] = "Cập nhật tour thành công!";
header("Location: bookings.php");
exit;