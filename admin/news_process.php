<?php
require_once '../config.php';
session_start();

function createSlug($val) {
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower($val));
    return trim($slug, '-');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $title = $_POST['title'];
    $slug = createSlug($title);
    $summary = $_POST['summary'];
    $content = $_POST['content'];
    $id = $_POST['id'] ?? null;

    $image_name = null;
    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], '../assets/uploads/' . $image_name);
    }

    if ($action == 'add') {
        $sql = "INSERT INTO news (title, slug, summary, content, image) VALUES (?,?,?,?,?)";
        $pdo->prepare($sql)->execute([$title, $slug, $summary, $content, $image_name]);
        $news_id = $pdo->lastInsertId();

        // Bắn thông báo tin tức mới
        $pdo->prepare("INSERT INTO notifications (title, message, type, link) VALUES (?, ?, ?, ?)")
            ->execute(["Tin tức mới", "Khám phá bài viết: '$title' vừa được đăng tải.", "news", "news-detail.php?id=$news_id"]);

        $_SESSION['success'] = "Đã đăng tin mới!";
    } elseif ($action == 'edit') {
        if ($image_name) {
            $sql = "UPDATE news SET title=?, slug=?, summary=?, content=?, image=? WHERE id=?";
            $pdo->prepare($sql)->execute([$title, $slug, $summary, $content, $image_name, $id]);
        } else {
            $sql = "UPDATE news SET title=?, slug=?, summary=?, content=? WHERE id=?";
            $pdo->prepare($sql)->execute([$title, $slug, $summary, $content, $id]);
        }
        $_SESSION['success'] = "Đã cập nhật tin!";
    }
    header("Location: news.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $pdo->prepare("DELETE FROM news WHERE id=?")->execute([$_GET['id']]);
    $_SESSION['success'] = "Đã xóa bài!";
    header("Location: news.php");
    exit;
}