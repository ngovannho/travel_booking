<?php
require_once '../config.php';
session_start();

function createSlug($val) {
    $unicode = [
        'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ', 'd'=>'đ', 'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
        'i'=>'í|ì|ỉ|ĩ|ị', 'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ', 'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
        'y'=>'ý|ỳ|ỷ|ỹ|ỵ', 'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ', 'D'=>'Đ', 'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
        'I'=>'Í|Ì|Ỉ|Ĩ|Ị', 'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ', 'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự', 'Y'=>'Ý|Ì|Ỉ|Ĩ|Ị',
    ];
    foreach($unicode as $nonUnicode=>$uni) $val = preg_replace("/($uni)/i", $nonUnicode, $val);
    $val = strtolower($val);
    $val = preg_replace('/[^a-z0-9\s-]/', '', $val);
    $val = preg_replace('/[\s-]+/', '-', $val);
    return trim($val, '-');
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