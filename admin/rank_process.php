<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    exit('Access Denied');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $min_points = (int)$_POST['min_points'];
    $icon = trim($_POST['icon']);
    $color = trim($_POST['color']);
    $rank_up_promo_code = $_POST['rank_up_promo_code'] ?: null;

    try {
        $sql = "UPDATE ranks SET name = ?, min_points = ?, icon = ?, color = ?, rank_up_promo_code = ? WHERE id = ?";
        $pdo->prepare($sql)->execute([$name, $min_points, $icon, $color, $rank_up_promo_code, $id]);
        
        $_SESSION['success'] = "Đã cập nhật cấu hình hạng $name!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Có lỗi xảy ra: " . $e->getMessage();
    }
    
    header("Location: ranks.php");
    exit;
}
header("Location: ranks.php");
exit;