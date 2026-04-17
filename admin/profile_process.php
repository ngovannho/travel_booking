<?php
require_once '../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $password = $_POST['new_password'];
    $user_id = $_SESSION['user']['id'];

    $sql = "UPDATE users SET fullname = ?, phone = ?";
    $params = [$fullname, $phone];

    if (!empty($password)) {
        $sql .= ", password = ?";
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }

    if (!empty($_FILES['avatar']['name'])) {
        $avatar_name = time() . '_avatar_' . $_FILES['avatar']['name'];
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], '../assets/uploads/' . $avatar_name)) {
            // Xóa ảnh cũ
            $old_stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
            $old_stmt->execute([$user_id]);
            $old_avatar = $old_stmt->fetchColumn();
            if ($old_avatar && file_exists('../assets/uploads/' . $old_avatar)) {
                unlink('../assets/uploads/' . $old_avatar);
            }
            
            $sql .= ", avatar = ?";
            $params[] = $avatar_name;
            $_SESSION['user']['avatar'] = $avatar_name;
        }
    }

    $sql .= " WHERE id = ?";
    $params[] = $user_id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Cập nhật lại session
    $_SESSION['user']['fullname'] = $fullname;
    $_SESSION['user']['phone'] = $phone;

    header("Location: profile.php?success=1");
    exit;
} else {
    header("Location: index.php");
    exit;
}