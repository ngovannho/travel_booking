<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

function isActive($page) {
    return strpos($_SERVER['PHP_SELF'], $page) !== false ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-slate-800 hover:text-white';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị viên</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .sidebar-transition { transition: transform 0.3s ease, width 0.3s ease; }
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); position: fixed; z-index: 50; }
            #sidebar.mobile-open { transform: translateX(0); }
            #overlay.active { display: block; }
        }
        #sidebar.collapsed { width: 80px; }
        #sidebar.collapsed .menu-text, #sidebar.collapsed .logo-text { display: none; }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div id="overlay" class="fixed inset-0 bg-black/50 z-40 hidden" onclick="toggleSidebar()"></div>

    <div class="flex min-h-screen relative">
        <aside id="sidebar" class="sidebar-transition w-64 bg-slate-900 text-white flex-shrink-0 h-screen sticky top-0 overflow-y-auto">
            <div class="p-6 flex items-center justify-between">
                <div class="logo-text text-xl font-bold flex items-center text-blue-400">
                    <i class="fas fa-globe-asia mr-2"></i>LILY-<span class="text-yellow-500">TRAVEL</span>
                </div>
                <button onclick="toggleSidebar()" class="text-gray-400 hover:text-white outline-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <nav class="mt-6 px-4 space-y-2">
                <a href="index.php" class="flex items-center p-3 rounded-xl transition <?= isActive('index.php') ?>">
                    <i class="fas fa-chart-line w-6 text-center"></i>
                    <span class="menu-text ml-3 text-sm font-bold">Phân bổ & Xu hướng</span>
                </a>

                <div class="pt-4 pb-2 px-3 text-xs font-bold text-gray-500 uppercase menu-text tracking-widest">Quản lý tour</div>
                
                <a href="categories.php" class="flex items-center p-3 rounded-xl transition <?= isActive('categories.php') ?>">
                    <i class="fas fa-th-large w-6 text-center"></i>
                    <span class="menu-text ml-3 text-sm font-bold">Danh mục Tour</span>
                </a>
                
                <a href="tours.php" class="flex items-center p-3 rounded-xl transition <?= isActive('tours.php') ?>">
                    <i class="fas fa-map-marked-alt w-6 text-center"></i>
                    <span class="menu-text ml-3 text-sm font-bold">Danh sách Tour</span>
                </a>

                <div class="pt-4 pb-2 px-3 text-xs font-bold text-gray-500 uppercase menu-text tracking-widest">Giao dịch & Người dùng</div>

                <a href="bookings.php" class="flex items-center p-3 rounded-xl transition <?= isActive('bookings.php') ?>">
                    <i class="fas fa-ticket-alt w-6 text-center"></i>
                    <span class="menu-text ml-3 text-sm font-bold">Quản lý giao dịch</span>
                </a>

                <a href="momo_logs.php" class="flex items-center p-3 rounded-xl transition <?= isActive('momo_logs.php') ?>">
                    <i class="fas fa-terminal w-6 text-center text-pink-400"></i>
                    <span class="menu-text ml-3 text-sm font-bold">Log MoMo IPN</span>
                </a>

                <a href="tour_reviews.php" class="flex items-center p-3 rounded-xl transition <?= isActive('tour_reviews.php') ?>">
                    <i class="fas fa-star w-6 text-center text-amber-400"></i>
                    <span class="menu-text ml-3 text-sm font-bold">Đánh giá tour</span>
                </a>

                <a href="promos.php" class="flex items-center p-3 rounded-xl transition <?= isActive('promos.php') ?>">
                    <i class="fas fa-percentage w-6 text-center text-emerald-400"></i>
                    <span class="menu-text ml-3 text-sm font-bold">Mã giảm giá VIP</span>
                </a>

                <a href="users.php" class="flex items-center p-3 rounded-xl transition <?= isActive('users.php') ?>">
                    <i class="fas fa-users w-6 text-center"></i>
                    <span class="menu-text ml-3 text-sm font-bold">Quản lý người dùng</span>
                </a>

                <div class="pt-4 pb-2 px-3 text-xs font-bold text-gray-500 uppercase menu-text tracking-widest">Nội dung</div>

                <a href="news.php" class="flex items-center p-3 rounded-xl transition <?= isActive('news.php') ?>">
                    <i class="fas fa-newspaper w-6 text-center"></i>
                    <span class="menu-text ml-3 text-sm font-bold">Tin tức</span>
                </a>

                <div class="pt-4 pb-2 px-3 text-xs font-bold text-gray-500 uppercase menu-text tracking-widest">Hệ thống</div>

                <a href="profile.php" class="flex items-center p-3 rounded-xl transition <?= isActive('profile.php') ?>">
                    <i class="fas fa-user-cog w-6 text-center"></i>
                    <span class="menu-text ml-3 text-sm font-bold">Hồ sơ cá nhân</span>
                </a>

                <a href="../index.php" class="flex items-center p-3 hover:bg-emerald-600/20 text-emerald-400 rounded-xl transition">
                    <i class="fas fa-eye w-6 text-center"></i>
                    <span class="menu-text ml-3 text-sm font-bold">Xem trang chủ</span>
                </a>

                <div class="mt-10 border-t border-slate-800 pt-4">
                    <a href="../logout.php" class="flex items-center p-3 hover:bg-red-600/20 text-red-400 rounded-xl transition">
                        <i class="fas fa-sign-out-alt w-6 text-center"></i>
                        <span class="menu-text ml-3 text-sm font-bold">Đăng xuất</span>
                    </a>
                </div>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
            <header class="h-20 bg-white border-b flex items-center justify-between px-4 md:px-8 flex-shrink-0">
                <div class="flex items-center">
                    <button onclick="toggleSidebar()" class="md:hidden text-2xl text-gray-600 mr-4">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2 class="text-lg font-bold text-gray-800 uppercase tracking-tight"> Trang quản trị - LILYTRAVEL</h2>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="profile.php" class="text-right hidden sm:block hover:opacity-70 transition-opacity">
                        <p class="text-sm font-black text-gray-900 leading-none"><?= $_SESSION['user']['fullname'] ?></p>
                        <p class="text-[10px] text-blue-600 font-bold uppercase mt-1 tracking-tighter">Quản trị viên</p>
                    </a>
                    <a href="profile.php"><img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['fullname']) ?>&background=2563eb&color=fff" class="w-10 h-10 rounded-xl border-2 border-white shadow-sm hover:scale-105 transition-transform"></a>
                </div>
            </header>
            <main class="p-4 md:p-8 overflow-y-auto bg-gray-50 flex-1">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const isMobile = window.innerWidth <= 768;

        if (isMobile) {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('hidden');
            overlay.classList.toggle('active');
        } else {
            sidebar.classList.toggle('collapsed');
        }
    }
</script>