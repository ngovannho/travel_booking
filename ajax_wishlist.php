<?php
// Đảm bảo không có khoảng trắng trước thẻ <?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập để thực hiện tính năng này!']);
    exit;
}

$user_id = (int)$_SESSION['user']['id'];
$tour_id = (int)($_POST['tour_id'] ?? 0);

if ($tour_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM wishlists WHERE user_id = ? AND tour_id = ? LIMIT 1");
    $stmt->execute([$user_id, $tour_id]);
    $exists = $stmt->fetch();

    if ($exists) {
        $pdo->prepare("DELETE FROM wishlists WHERE id = ?")->execute([$exists['id']]);
        echo json_encode(['status' => 'success', 'action' => 'removed']);
    } else {
        $pdo->prepare("INSERT INTO wishlists (user_id, tour_id) VALUES (?, ?)")->execute([$user_id, $tour_id]);
        echo json_encode(['status' => 'success', 'action' => 'added']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}