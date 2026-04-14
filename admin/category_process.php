<?php
require_once '../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $name = $_POST['name'];
    $slug = $_POST['slug'];
    $id = $_POST['id'] ?? null;

    if ($action == 'add') {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
        $_SESSION['success'] = "Thêm danh mục thành công!";
    } elseif ($action == 'edit') {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
        $stmt->execute([$name, $slug, $id]);
        $_SESSION['success'] = "Cập nhật thành công!";
    }
    header("Location: categories.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $_SESSION['success'] = "Đã xóa danh mục!";
    header("Location: categories.php");
    exit;
}