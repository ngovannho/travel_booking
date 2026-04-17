<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

function sendEmail($to, $toName, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Cấu hình Server
        // $mail->SMTPDebug = 2; // Bỏ comment dòng này để xem nhật ký gửi mail chi tiết
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Người gửi và người nhận
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($to, $toName);

        // Nội dung
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Cập nhật điểm thưởng và hạng thành viên
 */
function updateLoyaltyAndRank($pdo, $userId, $points, $isSubtraction = false) {
    if ($isSubtraction) {
        $pdo->prepare("UPDATE users SET loyalty_points = GREATEST(0, loyalty_points - ?) WHERE id = ?")
            ->execute([$points, $userId]);
    } else {
        $pdo->prepare("UPDATE users SET loyalty_points = loyalty_points + ? WHERE id = ?")
            ->execute([$points, $userId]);
    }
    
    $pdo->prepare("UPDATE users u 
                   SET rank_id = (SELECT id FROM ranks r WHERE u.loyalty_points >= r.min_points ORDER BY r.min_points DESC LIMIT 1)
                   WHERE id = ?")
        ->execute([$userId]);
}
?>