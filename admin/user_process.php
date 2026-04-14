<?php
require_once '../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    $id = $_POST['id'] ?? null;

    if ($action == 'add') {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, fullname, email, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $password, $fullname, $email, $phone, $role]);
        $_SESSION['success'] = "Thêm thành công!";
    } elseif ($action == 'edit') {
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET fullname=?, email=?, phone=?, role=?, password=? WHERE id=?");
            $stmt->execute([$fullname, $email, $phone, $role, $password, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET fullname=?, email=?, phone=?, role=? WHERE id=?");
            $stmt->execute([$fullname, $email, $phone, $role, $id]);
        }
        $_SESSION['success'] = "Cập nhật thành công!";
    }
    header("Location: users.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = (int)$_GET['id'];
    if ($id != $_SESSION['user']['id']) {
        $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
        $_SESSION['success'] = "Đã xóa!";
    }
    header("Location: users.php");
    exit;
}