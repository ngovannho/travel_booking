<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $password = $_POST['new_password'];

    $avatar_name = null;
    if (!empty($_FILES['avatar']['name'])) {
        $avatar_name = time() . '_avatar_' . $_FILES['avatar']['name'];
        move_uploaded_file($_FILES['avatar']['tmp_name'], 'assets/uploads/' . $avatar_name);
        $_SESSION['user']['avatar'] = $avatar_name;
    }

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET fullname = ?, phone = ?, password = ?" . ($avatar_name ? ", avatar = ?" : "") . " WHERE id = ?";
        $params = [$fullname, $phone, $hashed];
        if ($avatar_name) $params[] = $avatar_name;
        $params[] = $user_id;
        $pdo->prepare($sql)->execute($params);
    } else {
        $sql = "UPDATE users SET fullname = ?, phone = ?" . ($avatar_name ? ", avatar = ?" : "") . " WHERE id = ?";
        $params = [$fullname, $phone];
        if ($avatar_name) $params[] = $avatar_name;
        $params[] = $user_id;
        $pdo->prepare($sql)->execute($params);
    }

    $_SESSION['user']['fullname'] = $fullname;
    $_SESSION['user']['phone'] = $phone;

    header("Location: profile.php?tab=info&success=1");
    exit;
}