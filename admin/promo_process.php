<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    exit('Access Denied');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = strtoupper(trim($_POST['code']));
    $percent = (int)$_POST['percent'];
    $description = $_POST['description'];
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $usage_limit = !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null;
    $action = $_POST['action'] ?? 'add';
    $id = $_POST['id'] ?? null;

    try {
        if ($action == 'add') {
            // Kiểm tra mã đã tồn tại hay chưa
            $check = $pdo->prepare("SELECT id FROM promos WHERE code = ?");
            $check->execute([$code]);
            
            if ($check->rowCount() > 0) {
                $_SESSION['error'] = "Lỗi: Mã giảm giá '$code' đã tồn tại trong hệ thống!";
                header("Location: promos.php");
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO promos (code, percent, description, expiry_date, usage_limit) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$code, $percent, $description, $expiry_date, $usage_limit]);
            $_SESSION['success'] = "Đã thêm mã giảm giá mới!";
        } else {
            $stmt = $pdo->prepare("UPDATE promos SET code = ?, percent = ?, description = ?, expiry_date = ?, usage_limit = ? WHERE id = ?");
            $stmt->execute([$code, $percent, $description, $expiry_date, $usage_limit, $id]);
            $_SESSION['success'] = "Đã cập nhật mã giảm giá!";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Mã này đã tồn tại trong hệ thống.";
    }
    header("Location: promos.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = (int)$_GET['id'];
    $pdo->prepare("DELETE FROM promos WHERE id = ?")->execute([$id]);
    $_SESSION['success'] = "Đã xóa mã giảm giá!";
    header("Location: promos.php");
    exit;
}