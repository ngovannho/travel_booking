<?php
require_once 'config.php';
require_once 'mail_helper.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->execute([$username, $email]);
    
    if ($check->rowCount() > 0) {
        $error = "Tên đăng nhập hoặc email đã được sử dụng!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, fullname, email, role) VALUES (?, ?, ?, ?, 'user')");
        if ($stmt->execute([$username, $password, $fullname, $email])) {
            $new_user_id = $pdo->lastInsertId();

            // Gửi email chào mừng thành viên mới
            $subject = "Chào mừng bạn đến với Lily Travel!";
            $body = "
                    <html>
                    <body style='font-family: Arial, sans-serif; color: #334155; padding: 20px;'>
                        <div style='max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 24px; overflow: hidden; background: #ffffff;'>
                            <div style='background: #2563eb; padding: 40px; text-align: center;'>
                                <div style='color: #fbbf24; font-size: 24px; font-weight: 900; font-style: italic; margin-bottom: 10px;'>LILY-TRAVEL</div>
                                <h2 style='color: white; margin: 0; text-transform: uppercase; font-size: 18px; letter-spacing: 2px;'>Chào mừng thành viên mới</h2>
                            </div>
                            <div style='padding: 40px;'>
                                <p style='font-size: 16px;'>Xin chào <b>$fullname</b>,</p>
                                <p>Cảm ơn bạn đã đăng ký tài khoản tại <b>Lily Travel</b>. Chúng tôi rất vui mừng được đồng hành cùng bạn trong những hành trình khám phá sắp tới.</p>
                                
                                <div style='background: #f8fafc; border-radius: 20px; padding: 25px; margin: 30px 0; border: 1px solid #f1f5f9; text-align: center;'>
                                    <p style='margin: 0; font-size: 12px; color: #64748b; text-transform: uppercase;'>Quà tặng ra mắt</p>
                                    <p style='margin: 10px 0; font-size: 24px; font-weight: 900; color: #2563eb;'>Mã: HELLO</p>
                                    <p style='margin: 0; font-size: 11px; color: #94a3b8;'>Sử dụng mã này cho lần đặt tour đầu tiên của bạn để nhận ưu đãi!</p>
                                </div>

                                <p style='font-size: 14px; line-height: 1.6;'>Bạn có thể đăng nhập ngay để xem các tour du lịch hot nhất và quản lý hành trình của mình.</p>
                                
                                <div style='text-align: center; margin-top: 40px;'>
                                    <a href='" . BASE_URL . "login.php' style='background: #0f172a; color: #ffffff; padding: 18px 35px; text-decoration: none; border-radius: 15px; font-weight: 900; font-size: 11px; letter-spacing: 1px; display: inline-block; text-transform: uppercase;'>Đăng nhập tài khoản</a>
                                </div>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
            sendEmail($email, $fullname, $subject, $body);

            // Tự động tặng mã giảm giá 'HELLO' cho thành viên mới
            $promo_stmt = $pdo->prepare("SELECT id, usage_limit FROM promos WHERE code = 'HELLO' LIMIT 1");
            $promo_stmt->execute();
            $promo = $promo_stmt->fetch();

            if ($promo) {
                $check_usage = $pdo->prepare("SELECT COUNT(*) FROM user_promos WHERE promo_id = ?");
                $check_usage->execute([$promo['id']]);
                $current_usage = $check_usage->fetchColumn();
                
                if (!$promo['usage_limit'] || $current_usage < $promo['usage_limit']) {
                    $pdo->prepare("INSERT INTO user_promos (user_id, promo_id, is_used) VALUES (?, ?, 0)")->execute([$new_user_id, $promo['id']]);

                    // Thêm thông báo chào mừng kèm mã giảm giá
                    $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)")
                        ->execute([$new_user_id, "Quà chào mừng!", "Bạn vừa nhận được mã giảm giá HELLO dành cho thành viên mới!", "promo", "profile.php?tab=promos"]);
                }
            }

            header("Location: login.php?success=1");
            exit;
        }
    }
}

include 'header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center py-12 px-4">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-gray-100">
        <div class="text-center mb-8">
            <div class="text-3xl font-bold text-blue-600 inline-flex items-center">
                <i class="fas fa-plane-departure mr-2"></i>LILY-<span class="text-yellow-500">TRAVEL</span>
            </div>
            <p class="text-gray-500 mt-2 font-medium">Tạo tài khoản du lịch của bạn</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-4 text-sm border border-red-200">
                <i class="fas fa-exclamation-triangle mr-2"></i><?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Họ và tên</label>
                <input type="text" name="fullname" required 
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tên đăng nhập</label>
                <input type="text" name="username" required 
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required 
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Mật khẩu</label>
                <input type="password" name="password" required 
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>
            <button type="submit" class="w-full bg-yellow-500 text-white py-3 rounded-lg font-bold hover:bg-yellow-600 transform hover:scale-[1.02] transition duration-200 shadow-md mt-4">
                TẠO TÀI KHOẢN
            </button>
        </form>

        <div class="text-center mt-6 border-t pt-6">
            <span class="text-gray-600">Đã có tài khoản?</span>
            <a href="login.php" class="text-blue-600 font-bold hover:underline ml-1">Đăng nhập</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>