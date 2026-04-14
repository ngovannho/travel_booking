<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user'])) exit(json_encode(['status' => 'error']));

$user_id = $_SESSION['user']['id'];
$action = $_GET['action'] ?? 'fetch';

if ($action == 'fetch') {
    // Lấy thông báo chưa đọc (bao gồm thông báo riêng và thông báo chung)
    // Lưu ý: Hệ thống này đơn giản hóa, thông báo chung (user_id IS NULL) sẽ hiển thị cho mọi người
    $stmt = $pdo->prepare("SELECT * FROM notifications 
                           WHERE (user_id = ? OR user_id IS NULL) 
                           AND is_toasted = 0 
                           ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $notifs = $stmt->fetchAll();

    echo json_encode(['status' => 'success', 'data' => $notifs]);
} 
elseif ($action == 'get_count') {
    // Đếm tất cả thông báo chưa đọc (is_read = 0)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications 
                           WHERE (user_id = ? OR user_id IS NULL) 
                           AND is_read = 0");
    $stmt->execute([$user_id]);
    $count = $stmt->fetchColumn();
    echo json_encode(['status' => 'success', 'count' => (int)$count]);
}
elseif ($action == 'mark_toasted') {
    $id = (int)$_GET['id'];
    $pdo->prepare("UPDATE notifications SET is_toasted = 1 WHERE id = ?")->execute([$id]);
    echo json_encode(['status' => 'success']);
}
elseif ($action == 'mark_read') {
    $id = (int)$_GET['id'];
    // Để đơn giản, khi user thấy hoặc click, chúng ta đánh dấu là đã đọc
    // Đối với thông báo chung (user_id NULL), logic này cần bảng phụ để theo dõi từng user, 
    // ở đây ta tạm thời xử lý cho thông báo cá nhân.
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND (user_id = ? OR user_id IS NULL)");
    $stmt->execute([$id, $user_id]);
    echo json_encode(['status' => 'success']);
}
elseif ($action == 'get_all') {
    // Lấy tất cả để hiện trong dropdown
    $stmt = $pdo->prepare("SELECT * FROM notifications 
                           WHERE (user_id = ? OR user_id IS NULL) 
                           ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$user_id]);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
}
?>