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
    $cat_id = $_POST['category_id'];
    $price = $_POST['price_base'];
    $price_child = $_POST['price_child'] ?: 0;
    $price_infant = $_POST['price_infant'] ?: 0;
    $schedules = json_decode($_POST['schedules'] ?? '[]', true);
    $max_people = $_POST['max_people'] ?: 0;
    $discount_code = $_POST['discount_code'] ?: null;
    $discount_percent = (int)$_POST['discount_percent'] ?: 0;
    $duration = $_POST['duration'];
    $location = $_POST['departure_location'];
    $desc = $_POST['description'];
    $content = $_POST['content'];
    $id = $_POST['id'] ?? null;

    $image_name = null;
    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], '../assets/uploads/' . $image_name);
    }
    
    // Xử lý nhiều ảnh bổ sung
    $extra_images = [];
    if (!empty($_FILES['extra_images']['name'][0])) {
        foreach ($_FILES['extra_images']['name'] as $key => $name) {
            if ($_FILES['extra_images']['error'][$key] == 0) {
                $ext_name = time() . '_ex_' . $name;
                move_uploaded_file($_FILES['extra_images']['tmp_name'][$key], '../assets/uploads/' . $ext_name);
                $extra_images[] = $ext_name;
            }
        }
    }

    if ($action == 'add') {
        $sql = "INSERT INTO tours (category_id, title, slug, description, content, image, price_base, price_child, price_infant, departure_dates, max_people, departure_location, duration, discount_code, discount_percent) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $pdo->prepare($sql)->execute([$cat_id, $title, $slug, $desc, $content, $image_name, $price, $price_child, $price_infant, $dates, $max_people, $location, $duration, $discount_code, $discount_percent]);
        $tour_id = $pdo->lastInsertId();
        
        // Bắn thông báo chung cho mọi người (user_id = NULL)
        $pdo->prepare("INSERT INTO notifications (title, message, type, link) VALUES (?, ?, ?, ?)")
            ->execute(["Tour mới cực hot!", "Hành trình '$title' vừa được mở bán. Khám phá ngay!", "tour", "tour-detail.php?id=$tour_id"]);
            
        // Lưu ảnh bổ sung vào bảng tour_images
        foreach ($extra_images as $img) {
            $pdo->prepare("INSERT INTO tour_images (tour_id, image_path) VALUES (?, ?)")->execute([$tour_id, $img]);
        }

        $_SESSION['success'] = "Đã đăng tour mới!";
    } elseif ($action == 'edit') {
        $pdo->beginTransaction();
        if ($image_name) {
            $sql = "UPDATE tours SET category_id=?, title=?, slug=?, description=?, content=?, image=?, price_base=?, price_child=?, price_infant=?, max_people=?, departure_location=?, duration=?, discount_code=?, discount_percent=? WHERE id=?";
            $pdo->prepare($sql)->execute([$cat_id, $title, $slug, $desc, $content, $image_name, $price, $price_child, $price_infant, $max_people, $location, $duration, $discount_code, $discount_percent, $id]);
        } else {
            $sql = "UPDATE tours SET category_id=?, title=?, slug=?, description=?, content=?, price_base=?, price_child=?, price_infant=?, max_people=?, departure_location=?, duration=?, discount_code=?, discount_percent=? WHERE id=?";
            $pdo->prepare($sql)->execute([$cat_id, $title, $slug, $desc, $content, $price, $price_child, $price_infant, $max_people, $location, $duration, $discount_code, $discount_percent, $id]);
        }

        // Cập nhật lịch khởi hành (Xóa cũ thêm mới)
        $pdo->prepare("DELETE FROM tour_schedules WHERE tour_id = ?")->execute([$id]);
        foreach($schedules as $item) {
            $pdo->prepare("INSERT INTO tour_schedules (tour_id, departure_date, max_people) VALUES (?, ?, ?)")
                ->execute([$id, $item['date'], $item['max_people']]);
        }
        
        $pdo->commit();
        
        // Thêm ảnh bổ sung mới (giữ nguyên ảnh cũ)
        foreach ($extra_images as $img) {
            $pdo->prepare("INSERT INTO tour_images (tour_id, image_path) VALUES (?, ?)")->execute([$id, $img]);
        }

        $_SESSION['success'] = "Đã cập nhật!";
    }
    header("Location: tours.php");
    exit;
}

// API lấy lịch khởi hành
if (isset($_GET['action']) && $_GET['action'] == 'get_schedules') {
    $tour_id = (int)$_GET['tour_id'];
    $stmt = $pdo->prepare("SELECT departure_date as date, max_people FROM tour_schedules WHERE tour_id = ? ORDER BY departure_date ASC");
    $stmt->execute([$tour_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}

// Xử lý bật tắt trạng thái nhanh (AJAX)
if (isset($_GET['action']) && $_GET['action'] == 'toggle_status') {
    header('Content-Type: application/json');
    $id = (int)$_GET['id'];
    $status = (int)$_GET['status'];
    
    $stmt = $pdo->prepare("UPDATE tours SET status = ? WHERE id = ?");
    $success = $stmt->execute([$status, $id]);
    
    echo json_encode(['success' => $success]);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $pdo->prepare("DELETE FROM tours WHERE id=?")->execute([$_GET['id']]);
    $_SESSION['success'] = "Đã xóa tour!";
    header("Location: tours.php");
    exit;
}