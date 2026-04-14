<?php
require_once '../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
    $user_id = $_SESSION['user']['id'];
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $password = $_POST['new_password'];

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET fullname = ?, phone = ?, password = ? WHERE id = ?");
        $stmt->execute([$fullname, $phone, $hashed, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET fullname = ?, phone = ? WHERE id = ?");
        $stmt->execute([$fullname, $phone, $user_id]);
    }

    // Cập nhật lại session
    $_SESSION['user']['fullname'] = $fullname;
    $_SESSION['user']['phone'] = $phone;

    header("Location: profile.php?success=1");
    exit;
} else {
    header("Location: index.php");
    exit;
}