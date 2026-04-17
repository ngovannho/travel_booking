<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        $redirect = $_GET['redirect'] ?? ($user['role'] == 'admin' ? 'admin/index.php' : 'index.php');
        header("Location: " . $redirect);
        exit;
    } else {
        $error = "Tài khoản hoặc mật khẩu không chính xác!";
    }
}

include 'header.php';
?>

<div class="min-h-[70vh] flex items-center justify-center px-4 py-12">
    <div class="bg-white p-10 rounded-3xl shadow-2xl w-full max-w-md border border-gray-100">
        <div class="text-center mb-10">
            <div class="text-4xl font-extrabold text-blue-600 inline-flex items-center">
                <i class="fas fa-plane-departure mr-3 text-blue-500"></i>LILY- <span class="text-yellow-500">TRAVEL</span>
            </div>
            <p class="text-gray-400 mt-3 tracking-wide">Đăng nhập để khám phá thế giới</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="bg-red-50 text-red-500 p-4 rounded-xl mb-6 text-sm flex items-center border border-red-100">
                <i class="fas fa-info-circle mr-3"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 mb-2 ml-1">Tên đăng nhập</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                        <i class="fas fa-user text-sm"></i>
                    </span>
                    <input type="text" name="username" required 
                           class="w-full pl-11 pr-4 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 mb-2 ml-1">Mật khẩu</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                        <i class="fas fa-lock text-sm"></i>
                    </span>
                    <input type="password" name="password" required 
                           class="w-full pl-11 pr-4 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all">
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold shadow-lg hover:bg-blue-700 hover:-translate-y-1 transition-all duration-300">
                ĐĂNG NHẬP
            </button>
        </form>

        <div class="mt-10 pt-8 border-t border-gray-100 text-center">
            <p class="text-gray-500">Bạn chưa có tài khoản?</p>
            <a href="register.php" class="text-blue-600 font-bold hover:underline mt-2 inline-block">Đăng ký tài khoản.</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>