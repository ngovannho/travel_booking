<?php
require_once 'config.php';
header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
if (strlen($q) < 1) {
    exit(json_encode([]));
}

$searchTerm = "%$q%";
$priceValue = is_numeric($q) ? (int)$q : 0;

// Tìm kiếm theo tên tour, tên danh mục, ngày khởi hành hoặc lọc theo giá
$sql = "SELECT t.id, t.title, t.image, t.price_base, t.duration, t.departure_dates, c.name as cat_name 
        FROM tours t 
        LEFT JOIN categories c ON t.category_id = c.id 
        WHERE t.status = 1 
        AND (
            t.title LIKE ? 
            OR c.name LIKE ? 
            OR t.departure_dates LIKE ?
            OR (t.price_base <= ? AND ? > 1000)
        ) 
        ORDER BY (t.title LIKE ?) DESC, t.id DESC
        LIMIT 6";

$stmt = $pdo->prepare($sql);
$stmt->execute([$searchTerm, $searchTerm, $searchTerm, $priceValue, $priceValue, $searchTerm]);
echo json_encode($stmt->fetchAll());