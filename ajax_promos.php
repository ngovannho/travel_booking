<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user'])) exit(json_encode(['status' => 'error', 'message' => 'Unauthorized']));

$user_id = $_SESSION['user']['id'];
$action = $_GET['action'] ?? '';

// Kiểm tra điều kiện: Đã đặt ít nhất 1 tour và tổng chi tiêu > 5tr
$stmt = $pdo->prepare("SELECT SUM(total_price) as total, COUNT(*) as count FROM bookings WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

// Lấy rank_id hiện tại của user
$user_rank = $pdo->prepare("SELECT rank_id FROM users WHERE id = ?");
$user_rank->execute([$user_id]);
$current_rank_id = $user_rank->fetchColumn();

if ($stats['total'] < 5000000 || $stats['count'] == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Điều kiện nhận mã: Bạn cần hoàn thành ít nhất 1 tour và có tổng chi tiêu trên 5.000.000đ.']);
    exit;
}

if ($action == 'list') {
    // Lấy danh sách mã mà user chưa nhận
    $promos = $pdo->prepare("
        SELECT p.* FROM promos p 
        WHERE (p.expiry_date IS NULL OR p.expiry_date >= CURDATE()) 
        AND p.id NOT IN (SELECT promo_id FROM user_promos WHERE user_id = ?)
        AND (p.min_rank_id IS NULL OR p.min_rank_id <= ?)
        AND (p.usage_limit IS NULL OR p.usage_limit = 0 OR (SELECT COUNT(*) FROM user_promos WHERE promo_id = p.id) < p.usage_limit)
    ");
    $promos->execute([$user_id, $current_rank_id]);
    echo json_encode(['status' => 'success', 'promos' => $promos->fetchAll()]);
} 
elseif ($action == 'claim') {
    $promo_id = (int)$_GET['id'];
    
    // Kiểm tra giới hạn số lần sử dụng trước khi cho phép nhận
    $stmt = $pdo->prepare("
        SELECT usage_limit, 
        (SELECT COUNT(*) FROM user_promos WHERE promo_id = ?) as current_usage 
        FROM promos WHERE id = ?
    ");
    $stmt->execute([$promo_id, $promo_id]);
    $promo = $stmt->fetch();

    if ($promo && $promo['usage_limit'] > 0 && $promo['current_usage'] >= $promo['usage_limit']) {
        echo json_encode(['status' => 'error', 'message' => 'Rất tiếc, mã giảm giá này đã hết lượt nhận.']);
        exit;
    }

    $check = $pdo->prepare("SELECT id FROM user_promos WHERE user_id = ? AND promo_id = ?");
    $check->execute([$user_id, $promo_id]);
    
    if ($check->rowCount() == 0) {
        $ins = $pdo->prepare("INSERT INTO user_promos (user_id, promo_id) VALUES (?, ?)");
        $ins->execute([$user_id, $promo_id]);
        
        $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)")
            ->execute([$user_id, "Nhận mã thành công", "Mã giảm giá đã được lưu vào ví của bạn.", "promo", "profile.php?tab=promos"]);
            
        echo json_encode(['status' => 'success', 'message' => 'Mã đã được lưu vào ví của bạn!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Bạn đã nhận mã này rồi.']);
    }
}