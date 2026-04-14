<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']);
    exit;
}

$user_id = $_SESSION['user']['id'];
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy đơn']);
        exit;
    }

    if ($booking['status'] !== 'confirmed') {
        echo json_encode(['status' => 'error', 'message' => 'Trạng thái không hợp lệ']);
        exit;
    }

    $stmtUpdate = $pdo->prepare("
        UPDATE bookings 
        SET status = 'balance_pending'
        WHERE id = ?
    ");
    $stmtUpdate->execute([$booking_id]);

    echo json_encode(['status' => 'success', 'message' => 'Đã gửi yêu cầu']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
file_put_contents('log.txt', "Update booking $booking_id to balance_pending\n", FILE_APPEND);