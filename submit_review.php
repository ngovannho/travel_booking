<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];
    $booking_id = $_POST['booking_id'];
    $tour_id = $_POST['tour_id'];
    $rating = (int)$_POST['rating'];
    $comment = $_POST['comment'];

    // Kiểm tra xem đã đánh giá chưa
    $check = $pdo->prepare("SELECT id FROM reviews WHERE booking_id = ?");
    $check->execute([$booking_id]);
    
    if ($check->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO reviews (booking_id, tour_id, user_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$booking_id, $tour_id, $user_id, $rating, $comment]);
    }

    header("Location: profile.php?tab=tours&success=review");
    exit;
}