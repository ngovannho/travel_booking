<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/814/814513.png">
    <title>LILY-TRAVEL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        #mobile-menu { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        #mobile-menu.open { max-height: 500px; }
        .user-dropdown { display: none; }
        .user-dropdown.show { display: block; }
        
        /* Tùy chỉnh Toast thông báo nhỏ gọn */
        .swal2-toast-small {
            width: 260px !important;
            padding: 0.75rem !important;
            margin-top: 75px !important; /* Đẩy xuống dưới thanh header */
            margin-right: 15px !important;
        }
        .swal2-toast-small .swal2-title { font-size: 11px !important; margin: 0 !important; }
        .swal2-toast-small .swal2-html-container { font-size: 10px !important; margin-top: 4px !important; }
        .swal2-toast-small .swal2-timer-progress-bar { height: 2px !important; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800">
    <nav class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-50 border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center">
                    <a href="index.php" class="text-2xl font-black text-blue-600 flex items-center italic tracking-tighter">
                        <i class="fas fa-globe-asia mr-2"></i>LILY-<span class="text-yellow-500">TRAVEL</span>
                    </a>
                </div>

                <div class="hidden md:flex space-x-8 text-[11px] font-black uppercase tracking-widest text-slate-400">
                    <a href="index.php" class="hover:text-blue-600 transition">Trang chủ</a>
                    <a href="tours.php" class="hover:text-blue-600 transition">Tour</a>
                    <a href="news.php" class="hover:text-blue-600 transition">Tin tức</a>
                    <a href="contact.php" class="hover:text-blue-600 transition">Liên hệ</a>
                </div>

                <div class="hidden md:flex items-center">
                    <?php if (isset($_SESSION['user'])): ?>
                        <!-- Notification Bell -->
                        <div class="relative mr-4">
                            <button onclick="toggleNotifMenu()" class="relative p-3 bg-slate-50 rounded-2xl border border-slate-100 hover:bg-white hover:shadow-md transition-all group">
                                <i class="fas fa-bell text-slate-400 group-hover:text-blue-600"></i>
                                <span id="notif-badge" class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[9px] font-black rounded-full border-2 border-white flex items-center justify-center shadow-sm">0</span>
                            </button>
                            
                            <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-72 bg-white rounded-[1.5rem] shadow-2xl border border-slate-100 overflow-hidden z-[60]">
                                <div class="p-4 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                                    <h5 class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-none">Thông báo mới</h5>
                                    <i class="fas fa-bell text-[10px] text-blue-500"></i>
                                </div>
                                <div id="notif-list" class="max-h-80 overflow-y-auto">
                                    <!-- JS will render here -->
                                    <div class="p-8 text-center">
                                        <p class="text-[10px] font-bold text-slate-300 uppercase italic">Không có thông báo mới</p>
                                    </div>
                                </div>
                                <div class="p-3 bg-slate-50/50 text-center border-t border-slate-50">
                                    <button class="text-[9px] font-black text-slate-400 uppercase tracking-widest hover:text-blue-600 transition">Đánh dấu tất cả đã đọc</button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['user'])): ?>
                        <div class="relative">
                            <button onclick="toggleUserMenu()" class="flex items-center space-x-3 bg-slate-50 px-4 py-2 rounded-2xl border border-slate-100 hover:bg-white hover:shadow-md transition-all group">
                                <?php 
                                    $header_avatar = !empty($_SESSION['user']['avatar']) 
                                        ? 'assets/uploads/' . $_SESSION['user']['avatar'] 
                                        : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user']['fullname']) . '&background=random';
                                ?>
                                <img src="<?= $header_avatar ?>" class="w-8 h-8 rounded-xl border-2 border-white shadow-sm object-cover">
                                <span class="text-xs font-black text-slate-700 uppercase tracking-tighter"><?= $_SESSION['user']['fullname'] ?></span>
                                <i class="fas fa-chevron-down text-[10px] text-slate-400 group-hover:text-blue-600"></i>
                            </button>

                            <div id="user-dropdown" class="user-dropdown absolute right-0 mt-3 w-56 bg-white rounded-[2rem] shadow-2xl border border-slate-50 overflow-hidden py-3">
                                <div class="px-6 py-3 border-b border-slate-50 mb-2">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Tài khoản</p>
                                    <p class="text-xs font-bold text-slate-800 truncate"><?= $_SESSION['user']['email'] ?></p>
                                </div>
                                <a href="profile.php" class="flex items-center px-6 py-3 text-xs font-black text-slate-600 uppercase tracking-tighter hover:bg-blue-50 hover:text-blue-600 transition">
                                    <i class="fas fa-user-circle mr-3 w-4"></i> Hồ sơ cá nhân
                                </a>
                                <?php if ($_SESSION['user']['role'] == 'admin'): ?>
                                    <a href="admin/index.php" class="flex items-center px-6 py-3 text-xs font-black text-purple-600 uppercase tracking-tighter hover:bg-purple-50 transition">
                                        <i class="fas fa-user-shield mr-3 w-4"></i> Trang quản trị
                                    </a>
                                <?php endif; ?>
                                <div class="px-4 mt-2">
                                    <a href="logout.php" class="flex items-center px-4 py-3 bg-red-50 rounded-2xl text-[10px] font-black text-red-500 uppercase tracking-widest hover:bg-red-500 hover:text-white transition-all text-center justify-center">
                                        Đăng xuất
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="bg-slate-900 text-white px-8 py-3 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl shadow-slate-200">Đăng nhập</a>
                    <?php endif; ?>
                </div>

                <div class="md:hidden flex items-center">
                    <button id="menu-btn" class="outline-none text-2xl text-blue-600">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="md:hidden bg-white border-t">
            <div class="px-6 py-8 space-y-4 font-black uppercase text-xs tracking-widest">
                <a href="index.php" class="block py-2 text-slate-400 hover:text-blue-600">Trang chủ</a>
                <a href="tours.php" class="block py-2 text-slate-400 hover:text-blue-600">Tour</a>
                <a href="news.php" class="block py-2 text-slate-400 hover:text-blue-600">Tin tức</a>
                <a href="contact.php" class="block py-2 text-slate-400 hover:text-blue-600">Liên hệ</a>
                <div class="pt-6 border-t">
                    <?php if (isset($_SESSION['user'])): ?>
                        <div class="flex items-center space-x-4 mb-6">
                            <?php 
                                $mobile_avatar = !empty($_SESSION['user']['avatar']) 
                                    ? 'assets/uploads/' . $_SESSION['user']['avatar'] 
                                    : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user']['fullname']) . '&background=random';
                            ?>
                            <img src="<?= $mobile_avatar ?>" class="w-12 h-12 rounded-2xl object-cover">
                            <div>
                                <p class="text-sm font-black text-slate-800 uppercase italic tracking-tighter"><?= $_SESSION['user']['fullname'] ?></p>
                                <a href="profile.php" class="text-[10px] text-blue-600 font-black">Xem hồ sơ</a>
                            </div>
                        </div>
                        <a href="logout.php" class="block bg-red-500 text-white py-4 rounded-2xl text-center shadow-lg">Đăng xuất</a>
                    <?php else: ?>
                        <a href="login.php" class="block bg-slate-900 text-white py-4 rounded-2xl text-center shadow-xl">Đăng nhập</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <script>
        function toggleNotifMenu() {
            document.getElementById('notif-dropdown').classList.toggle('hidden');
            document.getElementById('user-dropdown').classList.add('hidden');
            if(!document.getElementById('notif-dropdown').classList.contains('hidden')) {
                loadAllNotifications();
            }
        }

        function toggleUserMenu() {
            document.getElementById('user-dropdown').classList.toggle('show');
            document.getElementById('notif-dropdown').classList.add('hidden');
        }

        window.onclick = function(event) {
            if (!event.target.closest('.relative') && !event.target.closest('#notif-dropdown')) {
                const dropdowns = document.getElementsByClassName("user-dropdown");
                for (let i = 0; i < dropdowns.length; i++) {
                    dropdowns[i].classList.remove('show');
                }
                document.getElementById('notif-dropdown')?.classList.add('hidden');
            }
        }

        // Logic gợi ý tìm kiếm Tour
        document.querySelectorAll('.tour-search-input').forEach(input => {
            const suggestionBox = input.parentElement.querySelector('.search-suggestions');
            
            input.addEventListener('input', function() {
                const query = this.value.trim();
                if (query.length < 1) {
                    suggestionBox.classList.add('hidden');
                    return;
                }

                fetch(`ajax_tour_suggestions.php?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.length > 0) {
                            suggestionBox.innerHTML = data.map(tour => `
                                <a href="tour-detail.php?id=${tour.id}" class="flex items-center gap-4 p-4 hover:bg-slate-50 transition-all border-b border-slate-50 last:border-0 group/item">
                                    <img src="assets/uploads/${tour.image || 'default-tour.jpg'}" class="w-14 h-14 rounded-xl object-cover shadow-sm">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-[8px] font-black bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded uppercase tracking-widest">${tour.cat_name || 'Tour'}</span>
                                            <span class="text-[8px] font-bold text-slate-400 uppercase italic">${tour.duration}</span>
                                        </div>
                                        <p class="text-[11px] font-black text-slate-800 uppercase italic leading-tight truncate mb-1 group-hover/item:text-blue-600 transition-colors">${tour.title}</p>
                                        <div class="flex items-center justify-between">
                                            <p class="text-[10px] font-black text-blue-600">${new Intl.NumberFormat('vi-VN').format(tour.price_base)}đ</p>
                                            <p class="text-[8px] font-bold text-slate-400 italic">Khởi hành: ${tour.departure_dates ? tour.departure_dates.split(',')[0] : 'Liên hệ'}</p>
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
                                </a>
                            `).join('');
                            suggestionBox.classList.remove('hidden');
                        } else {
                            suggestionBox.innerHTML = '<div class="p-4 text-[10px] font-bold text-slate-400 uppercase italic text-center">Không tìm thấy tour phù hợp</div>';
                            suggestionBox.classList.remove('hidden');
                        }
                    });
            });
        });

        // Đóng gợi ý khi click ra ngoài
        window.addEventListener('click', function(e) {
            if (!e.target.closest('.tour-search-input')) {
                document.querySelectorAll('.search-suggestions').forEach(box => box.classList.add('hidden'));
            }
        });

        // Logic thông báo
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            customClass: { popup: 'swal2-toast-small rounded-2xl border border-slate-100 shadow-2xl' }
        });

        function updateNotifCount() {
            fetch('ajax_notifications.php?action=get_count')
                .then(res => res.json())
                .then(res => {
                    const badge = document.getElementById('notif-badge');
                    if(res.status === 'success' && res.count > 0) {
                        badge.innerText = res.count > 9 ? '9+' : res.count;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                });
        }

        function checkNewNotifications() {
            fetch('ajax_notifications.php?action=fetch')
                .then(res => res.json())
                .then(res => {
                    if(res.status === 'success' && res.data.length > 0) {
                        updateNotifCount();
                        res.data.forEach(notif => {
                            if (notif.type === 'rank_up') {
                                // Hiển thị thông báo chúc mừng thăng hạng nổi bật
                                Swal.fire({
                                    title: `<span class="text-xl font-black uppercase italic text-blue-600">${notif.title}</span>`,
                                    html: `<div class="p-4 text-sm font-bold text-slate-600 uppercase tracking-tighter">${notif.message}</div>`,
                                    icon: 'success',
                                    confirmButtonText: 'TUYỆT VỜI!',
                                    confirmButtonColor: '#2563eb',
                                    customClass: { popup: 'rounded-[3rem] border-0 shadow-2xl' }
                                }).then(() => {
                                    if(notif.link) window.location.href = notif.link;
                                });
                            } else {
                                Toast.fire({
                                    icon: 'info',
                                    title: `<span class="text-xs font-black uppercase italic">${notif.title}</span>`,
                                    text: notif.message,
                                    didOpen: (toast) => {
                                        toast.style.cursor = 'pointer';
                                        toast.addEventListener('click', () => {
                                            if(notif.link) window.location.href = notif.link;
                                        });
                                    }
                                });
                            }
                            // Đánh dấu đã hiện Toast để không hiện lại popup, nhưng vẫn giữ trạng thái chưa đọc (is_read=0)
                            fetch(`ajax_notifications.php?action=mark_toasted&id=${notif.id}`);
                        });
                    }
                });
        }

        function loadAllNotifications() {
            fetch('ajax_notifications.php?action=get_all')
                .then(res => res.json())
                .then(res => {
                    const list = document.getElementById('notif-list');
                    if(res.data.length === 0) {
                        list.innerHTML = '<div class="p-8 text-center text-[10px] font-bold text-slate-300 uppercase italic">Bạn không có thông báo nào</div>';
                        return;
                    }
                    list.innerHTML = res.data.map(n => `
                        <div onclick="goToNotif(${n.id}, '${n.link}')" class="p-4 border-b border-slate-50 hover:bg-slate-50 transition-colors cursor-pointer ${n.is_read == 0 ? 'bg-blue-50/30' : ''}">
                            <div class="flex gap-3">
                                <div class="w-7 h-7 rounded-lg bg-white shadow-sm flex items-center justify-center flex-shrink-0 border border-slate-100">
                                    <i class="fas ${getIcon(n.type)} text-[9px] ${getColor(n.type)}"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-[10px] font-black text-slate-800 uppercase italic leading-tight mb-0.5">${n.title}</p>
                                    <p class="text-[10px] text-slate-500 font-medium leading-relaxed line-clamp-2">${n.message}</p>
                                </div>
                            </div>
                        </div>
                    `).join('');
                    updateNotifCount();
                });
        }

        function goToNotif(id, link) {
            fetch(`ajax_notifications.php?action=mark_read&id=${id}`).then(() => {
                if(link && link !== 'null') window.location.href = link;
                else updateNotifCount();
            });
        }

        function getIcon(type) {
            const icons = { payment: 'fa-credit-card', news: 'fa-newspaper', tour: 'fa-map-marked-alt', promo: 'fa-gift', rank_up: 'fa-crown' };
            return icons[type] || 'fa-bell';
        }
        function getColor(type) {
            const colors = { payment: 'text-emerald-500', news: 'text-blue-500', tour: 'text-amber-500', promo: 'text-purple-500', rank_up: 'text-yellow-500' };
            return colors[type] || 'text-slate-400';
        }

        <?php if(isset($_SESSION['user'])): ?>
            setInterval(checkNewNotifications, 10000); // Kiểm tra mỗi 10 giây
            checkNewNotifications();
            updateNotifCount(); // Lấy số lượng ngay khi tải trang
        <?php endif; ?>

        const btn = document.getElementById('menu-btn');
        const menu = document.getElementById('mobile-menu');
        btn.addEventListener('click', () => {
            menu.classList.toggle('open');
            btn.querySelector('i').classList.toggle('fa-bars');
            btn.querySelector('i').classList.toggle('fa-times');
        });
    </script>