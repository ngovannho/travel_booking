<?php 
require_once 'config.php';
include 'header.php';

// Lấy tour kèm theo chuỗi đường dẫn ảnh bổ sung
$stmtTours = $pdo->query("
    SELECT t.*, 
    (SELECT GROUP_CONCAT(image_path) FROM tour_images WHERE tour_id = t.id) as extra_images 
    FROM tours t 
    WHERE t.status = 1 
    ORDER BY t.id DESC LIMIT 6");
$tours = $stmtTours->fetchAll();

$total_active_tours = $pdo->query("SELECT COUNT(*) FROM tours WHERE status = 1")->fetchColumn();

$stmtCats = $pdo->query("
    SELECT c.*, COUNT(t.id) as tour_count 
    FROM categories c 
    LEFT JOIN tours t ON c.id = t.category_id AND t.status = 1 
    GROUP BY c.id
    ORDER BY tour_count DESC
");
$categories = $stmtCats->fetchAll();

$stmtNews = $pdo->query("SELECT * FROM news ORDER BY id DESC LIMIT 3");
$newsList = $stmtNews->fetchAll();

// Lấy các đánh giá xuất sắc nhất (4-5 sao) để làm Testimonials
$stmtTopReviews = $pdo->query("
    SELECT r.*, u.fullname, t.title as tour_title 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN tours t ON r.tour_id = t.id 
    WHERE r.rating >= 4 ORDER BY r.created_at DESC LIMIT 3");
$topReviews = $stmtTopReviews->fetchAll();
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-7xl mx-auto px-4 mt-6">
    <!-- HERO SECTION NÂNG CẤP -->
    <header class="relative h-[500px] rounded-[3.5rem] overflow-hidden flex items-center justify-center text-white shadow-2xl shadow-blue-900/20">
        <div class="absolute inset-0 bg-gradient-to-b from-slate-900/40 via-slate-900/20 to-slate-900/80 z-10"></div>
        
        <div class="absolute inset-0 bg-[url('assets/uploads/banner.png')] bg-cover bg-center transition-transform duration-700 hover:scale-110"></div>
        
        <div class="relative z-20 text-center px-6 w-full max-w-3xl animate-in fade-in slide-in-from-bottom-10 duration-1000">
            <h1 class="text-5xl md:text-7xl font-black mb-6 uppercase tracking-tighter italic leading-none">
                Hành trình <span class="text-yellow-400">di sản</span>
            </h1>
            
            <p class="text-xs md:text-sm mb-10 font-bold text-gray-100 uppercase tracking-[0.4em] opacity-90">
                Khám phá vẻ đẹp bất tận cùng Lily-Travel
            </p>
            
            <form action="tours.php" method="GET" class="bg-white/10 backdrop-blur-md p-2 rounded-2xl flex shadow-2xl border border-white/20 relative">
                <input 
                    type="text" 
                    name="search" 
                    class="tour-search-input flex-1 bg-transparent px-4 py-3 text-white placeholder-gray-300 focus:outline-none text-sm font-bold"
                    placeholder="Tìm kiếm hành trình của bạn..." 
                    autocomplete="off"
                >
                
                <button type="submit" class="bg-yellow-500 hover:bg-white hover:text-blue-600 text-slate-900 px-8 py-3 rounded-xl font-black text-xs uppercase transition-all shadow-xl">
                    Khám phá ngay
                </button>
                <div class="search-suggestions absolute top-full left-0 w-full bg-white mt-2 rounded-2xl shadow-2xl z-50 hidden overflow-hidden border border-slate-100"></div>
            </form>
        </div>
    </header>

    <!-- QUICK STATS / FEATURES -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-[-3rem] relative z-30 px-6">
        <div class="bg-white/90 backdrop-blur-xl p-6 rounded-3xl shadow-xl border border-white flex items-center gap-5 group hover:bg-blue-600 transition-all duration-500">
            <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 group-hover:bg-white/20 group-hover:text-white transition-colors">
                <i class="fas fa-shield-alt text-xl"></i>
            </div>
            <div>
                <h4 class="font-black uppercase italic text-xs text-slate-800 group-hover:text-white leading-none mb-1">An toàn tuyệt đối</h4>
                <p class="text-[10px] text-slate-400 group-hover:text-blue-100 uppercase font-bold tracking-widest">Bảo hiểm trọn gói</p>
            </div>
        </div>
        <div class="bg-white/90 backdrop-blur-xl p-6 rounded-3xl shadow-xl border border-white flex items-center gap-5 group hover:bg-yellow-500 transition-all duration-500">
            <div class="w-14 h-14 bg-yellow-50 rounded-2xl flex items-center justify-center text-yellow-600 group-hover:bg-white/20 group-hover:text-white transition-colors">
                <i class="fas fa-tag text-xl"></i>
            </div>
            <div>
                <h4 class="font-black uppercase italic text-xs text-slate-800 group-hover:text-white leading-none mb-1">Giá tốt nhất</h4>
                <p class="text-[10px] text-slate-400 group-hover:text-yellow-50 uppercase font-bold tracking-widest">Cam kết không mất phí</p>
            </div>
        </div>
        <div class="bg-white/90 backdrop-blur-xl p-6 rounded-3xl shadow-xl border border-white flex items-center gap-5 group hover:bg-slate-900 transition-all duration-500">
            <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-900 group-hover:bg-white/20 group-hover:text-white transition-colors">
                <i class="fas fa-headset text-xl"></i>
            </div>
            <div>
                <h4 class="font-black uppercase italic text-xs text-slate-800 group-hover:text-white leading-none mb-1">Hỗ trợ 24/7</h4>
                <p class="text-[10px] text-slate-400 group-hover:text-slate-400 uppercase font-bold tracking-widest">Đồng hành mọi lúc,mọi nơi</p>
            </div>
        </div>
    </div>
</div>

<main class="max-w-7xl mx-auto px-4 py-16">
    <div class="flex flex-col lg:flex-row gap-12">

        <!-- SIDEBAR -->
        <aside class="w-full lg:w-1/4">
            <div class="sticky top-28">
                
                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 group/sidebar">
                    <div class="flex items-center justify-between cursor-default">
                        <h3 class="text-xs font-black uppercase tracking-widest text-blue-600 flex items-center leading-none">
                            <span class="w-6 h-1 bg-blue-600 rounded-full mr-3"></span> 
                            Danh mục Tour
                        </h3>
                        <i class="fas fa-chevron-down text-[10px] text-slate-300 transition-transform duration-300 group-hover/sidebar:rotate-180 group-hover/sidebar:text-blue-500"></i>
                    </div>

                    <div class="max-h-0 overflow-hidden group-hover/sidebar:max-h-[1000px] group-hover/sidebar:mt-8 transition-all duration-700 ease-in-out">
                        <div class="flex flex-col gap-2">
                            <a href="tours.php" class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-transparent hover:bg-white hover:border-blue-100 hover:shadow-md transition-all group/item">
                                <span class="text-[10px] font-black uppercase tracking-tighter text-slate-500 group-hover/item:text-blue-600">
                                    Tất cả chuyến đi
                                </span>
                                <span class="text-[10px] font-black text-slate-300 group-hover/item:text-blue-600"><?= $total_active_tours ?></span>
                            </a>
                            <?php foreach($categories as $cat): ?>
                                <a href="tours.php?category=<?= $cat['id'] ?>" class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-transparent hover:bg-white hover:border-blue-100 hover:shadow-md transition-all group/item">
                                    <span class="text-[10px] font-black uppercase tracking-tighter text-slate-500 group-hover/item:text-blue-600">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </span>
                                    <span class="text-[10px] font-black text-slate-300 group-hover/item:text-blue-600"><?= $cat['tour_count'] ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- PROMO -->
                <div class="mt-8 bg-slate-900 rounded-[2.5rem] p-10 text-white relative overflow-hidden shadow-2xl">
                    <div class="relative z-10">
                        <h4 class="text-2xl font-black-italic leading-none mb-4 uppercase tracking-tighter">
                            Đặt Tour ngay!
                        </h4>
                        <p class="text-[10px] text-gray-400 mb-8 font-bold uppercase tracking-[0.2em] leading-relaxed">
                            Đăng ký ngay để nhận<br>mã giảm giá đến 35%.
                        </p>
                        <button onclick="showPromoModal()" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-white hover:text-slate-900 transition-all shadow-xl">
                            <i class="fas fa-gift mr-2"></i> Nhận mã ngay
                        </button>
                    </div>
                    <i class="fas fa-gift absolute -bottom-4 -right-4 text-8xl text-white/5 -rotate-12"></i>
                </div>

                <script>
                function showPromoModal() {
                    <?php if(!isset($_SESSION['user'])): ?>
                        Swal.fire({ icon: 'info', title: 'Thông báo', text: 'Vui lòng đăng nhập để nhận mã!' });
                        return;
                    <?php endif; ?>

                    Swal.fire({
                        title: '<span class="text-sm font-black uppercase italic">Kho mã ưu đãi</span>',
                        html: '<div id="promo_list" class="space-y-3 p-2">Đang kiểm tra điều kiện...</div>',
                        showConfirmButton: false,
                        customClass: { popup: 'rounded-[2.5rem]' },
                        didOpen: () => {
                            fetch('ajax_promos.php?action=list')
                                .then(res => res.json())
                                .then(data => {
                                    const list = document.getElementById('promo_list');
                                    if(data.status === 'error') {
                                        list.innerHTML = `<p class="text-xs font-bold text-red-500 bg-red-50 p-4 rounded-2xl">${data.message}</p>`;
                                    } else if (!data.promos || data.promos.length === 0) {
                                        list.innerHTML = `<p class="text-xs font-bold text-slate-400 p-4 italic">Hiện tại không có mã giảm giá mới nào dành cho bạn.</p>`;
                                    } else {
                                        list.innerHTML = data.promos.map(p => `
                                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                                <div class="text-left">
                                                    <p class="text-xs font-black text-blue-600 uppercase">${p.code} (-${p.percent}%)</p>
                                                    <p class="text-[9px] text-slate-400 font-bold">${p.description}</p>
                                                </div>
                                                <button onclick="claimPromo(${p.id})" class="bg-slate-900 text-white px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-blue-600 transition-all">Nhận</button>
                                            </div>
                                        `).join('');
                                    }
                                });
                        }
                    });
                }

                function claimPromo(promoId) {
                    fetch(`ajax_promos.php?action=claim&id=${promoId}`)
                        .then(res => res.json())
                        .then(data => {
                            Swal.fire({
                                icon: data.status,
                                title: data.status === 'success' ? 'Thành công' : 'Lỗi',
                                text: data.message,
                                customClass: { popup: 'rounded-[2.5rem]' }
                            }).then(() => {
                                if (data.status === 'success') showPromoModal(); // Làm mới danh sách mã
                            });
                        });
                }
                </script>
            </div>
        </aside>

        <!-- TOURS -->
        <section class="w-full lg:w-3/4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-black text-gray-900 uppercase italic tracking-tighter">
                    Chuyến đi mới nhất
                </h2>
                <a href="tours.php" class="text-[10px] font-black uppercase tracking-widest text-blue-600 hover:underline">
                    Khám phá thêm <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach($tours as $tour): ?>
                    <div class="bg-white rounded-[2.5rem] overflow-hidden border border-slate-100 hover:shadow-2xl transition-all duration-500 group">
                        
                        <!-- Bọc ảnh trong container để làm hiệu ứng nhảy ảnh -->
                        <div class="relative overflow-hidden h-64 tour-card-slider">
                            <div class="flex h-full transition-transform duration-700 ease-in-out tour-track" style="transform: translateX(0%);">
                                <?php 
                                    $all_imgs = [$tour['image'] ?: 'default-tour.jpg'];
                                    if (!empty($tour['extra_images'])) {
                                        $all_imgs = array_merge($all_imgs, explode(',', $tour['extra_images']));
                                    }
                                    foreach($all_imgs as $img_path):
                                ?>
                                    <img src="assets/uploads/<?= trim($img_path) ?>" 
                                         class="w-full h-full object-cover flex-shrink-0">
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Nút điều hướng thủ công -->
                            <?php if (count($all_imgs) > 1): ?>
                                <button type="button" onclick="event.preventDefault(); moveSlide(this, -1)" class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-white/20 hover:bg-white/80 backdrop-blur text-slate-900 rounded-full opacity-0 group-hover:opacity-100 transition-all z-20 flex items-center justify-center">
                                    <i class="fas fa-chevron-left text-[10px]"></i>
                                </button>
                                <button type="button" onclick="event.preventDefault(); moveSlide(this, 1)" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-white/20 hover:bg-white/80 backdrop-blur text-slate-900 rounded-full opacity-0 group-hover:opacity-100 transition-all z-20 flex items-center justify-center">
                                    <i class="fas fa-chevron-right text-[10px]"></i>
                                </button>
                            <?php endif; ?>

                            <div class="absolute top-6 left-6 bg-white/90 backdrop-blur px-4 py-1.5 rounded-xl text-[9px] font-black text-blue-600 shadow-sm uppercase tracking-widest">
                                <?= $tour['duration'] ?>
                            </div>
                        </div>

                        <div class="p-8">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="text-xl font-bold text-gray-800 leading-tight pr-4 min-h-[3.5rem] line-clamp-2 uppercase tracking-tighter italic">
                                    <?= htmlspecialchars($tour['title']) ?>
                                </h3>

                                <?php
                                $p_child = $tour['price_child'] ?? 0;
                                $p_adult = $tour['price_base'];
                                $p_senior = $tour['price_infant'] ?? 0;
                                
                                $all_prices = array_filter([$p_child, $p_adult, $p_senior]);
                                $min_p = min($all_prices);
                                $max_p = max($all_prices);
                                ?>
                                <div class="text-right min-w-fit">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1 italic">Giá tour từ</p>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <span class="text-2xl font-black text-blue-600 tracking-tighter"><?= number_format($min_p, 0, ',', '.') ?>đ</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-6 border-t border-slate-50 mt-4">
                                <div class="flex items-center text-gray-400 text-[10px] font-bold uppercase tracking-widest">
                                    <div class="w-8 h-8 bg-slate-50 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-map-marker-alt text-blue-500"></i>
                                    </div>
                                    <?= $tour['departure_location'] ?>
                                </div>

                                <a href="tour-detail.php?id=<?= $tour['id'] ?>" class="bg-slate-900 text-white px-8 py-3.5 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl shadow-slate-200">
                                    Chi tiết
                                </a>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </div>

    <!-- NEWS -->
    <section class="mt-24">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-black text-gray-900 uppercase italic tracking-tighter">
                Cẩm nang du lịch
            </h2>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-[0.4em] mt-2">
                Tin tức & Sự kiện mới nhất
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach($newsList as $n): ?>
                <a href="news-detail.php?id=<?= $n['id'] ?>" class="group block">

                    <div class="relative h-48 rounded-[2rem] overflow-hidden mb-6 shadow-lg shadow-slate-200/50">
                        <img src="assets/uploads/<?= $n['image'] ?: 'default-news.jpg' ?>" 
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" 
                             alt="<?= htmlspecialchars($n['title']) ?>">

                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-80 group-hover:opacity-100 transition-opacity"></div>

                        <div class="absolute bottom-4 left-6 text-white text-[10px] font-black uppercase tracking-widest">
                            <i class="far fa-calendar-alt mr-2"></i>
                            <?= date('d.m.Y', strtotime($n['created_at'])) ?>
                        </div>
                    </div>

                    <div class="px-2">
                        <h3 class="text-lg font-bold text-gray-800 leading-tight group-hover:text-blue-600 transition-colors uppercase italic tracking-tighter">
                            <?= htmlspecialchars($n['title']) ?>
                        </h3>

                        <p class="text-xs text-gray-500 mt-3 line-clamp-2 leading-relaxed font-medium">
                            <?= htmlspecialchars($n['summary']) ?>
                        </p>

                        <div class="mt-4 text-[10px] font-black text-blue-600 uppercase tracking-widest flex items-center opacity-0 group-hover:opacity-100 transition-all transform translate-y-2 group-hover:translate-y-0">
                            Xem chi tiết <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </div>

                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- TESTIMONIALS SECTION - MỚI -->
    <?php if (!empty($topReviews)): ?>
    <section class="mt-24 bg-blue-600 rounded-[4rem] p-12 md:p-20 text-white relative overflow-hidden shadow-2xl shadow-blue-200">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-32 -mt-32"></div>
        <div class="relative z-10">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-black uppercase italic tracking-tighter mb-4">Khách hàng nói gì về chúng tôi</h2>
                <div class="flex justify-center gap-1 text-yellow-400">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach($topReviews as $rev): ?>
                    <div class="bg-white/10 backdrop-blur-md p-8 rounded-[2.5rem] border border-white/20 hover:bg-white/20 transition-all group">
                        <i class="fas fa-quote-left text-3xl text-yellow-400 mb-6 opacity-50"></i>
                        <p class="text-sm font-medium italic leading-relaxed mb-8">"<?= htmlspecialchars($rev['comment']) ?>"</p>
                        <div class="flex items-center gap-4">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($rev['fullname']) ?>&background=random" class="w-10 h-10 rounded-xl border-2 border-white/50">
                            <div>
                                <p class="text-[11px] font-black uppercase italic tracking-tighter"><?= htmlspecialchars($rev['fullname']) ?></p>
                                <p class="text-[9px] font-bold text-blue-200 uppercase tracking-widest"><?= htmlspecialchars($rev['tour_title']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- NEWSLETTER SECTION - MỚI -->
    <section class="mt-24 text-center max-w-4xl mx-auto">
        <div class="bg-white p-12 md:p-16 rounded-[4rem] shadow-xl border border-slate-50 relative overflow-hidden">
            <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-slate-50 rounded-full"></div>
            
            <div class="relative z-10">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-8 text-2xl">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <h2 class="text-3xl font-black text-slate-900 uppercase italic tracking-tighter mb-4">
                    Đừng bỏ lỡ những ưu đãi hấp dẫn!
                </h2>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-[0.3em] mb-10">
                    Đăng ký nhận bản tin để cập nhật các tour mới nhất và mã giảm giá độc quyền.
                </p>
                
                <form onsubmit="event.preventDefault(); Swal.fire({icon: 'success', title: 'Thành công', text: 'Cảm ơn bạn đã đăng ký!', customClass: {popup: 'rounded-[2.5rem]'}});" class="flex flex-col md:flex-row gap-4 max-w-2xl mx-auto">
                    <input type="email" required placeholder="Địa chỉ email của bạn..." 
                           class="flex-1 px-8 py-5 bg-slate-50 border-0 rounded-2xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    <button type="submit" class="bg-slate-900 text-white px-10 py-5 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl">
                        Đăng ký ngay
                    </button>
                </form>
            </div>
        </div>
    </section>

</main>

<script>
    function toggleCategoryList() {
        const container = document.getElementById('category-list-container');
        const icon = document.getElementById('cat-toggle-icon');
        
        if (container.classList.contains('hidden')) {
            container.classList.remove('hidden');
            icon.classList.remove('rotate-180');
        } else {
            container.classList.add('hidden');
            icon.classList.add('rotate-180');
        }
    }

    // Khởi tạo các slider cho card tour
    const cardSliders = [];

    function initSliders() {
        document.querySelectorAll('.tour-card-slider').forEach((slider, index) => {
            const track = slider.querySelector('.tour-track');
            if (!track) return;
            const slides = track.querySelectorAll('img');
            if (slides.length <= 1) return;

            // Lưu trạng thái slide hiện tại vào element
            slider.dataset.current = 0;
            
            // Thiết lập tự động chuyển ảnh
            let interval = setInterval(() => {
                changeSlide(slider, 1);
            }, 2000 + Math.random() * 2000); // Tốc độ nhanh hơn: 2000ms - 4000ms

            // Tạm dừng khi di chuột vào card
            slider.parentElement.addEventListener('mouseenter', () => clearInterval(interval));
            slider.parentElement.addEventListener('mouseleave', () => {
                interval = setInterval(() => {
                    changeSlide(slider, 1);
                }, 4000);
            });
        });
    }

    function changeSlide(slider, step) {
        const track = slider.querySelector('.tour-track');
        if (!track) return;
        const slides = track.querySelectorAll('img');
        let current = parseInt(slider.dataset.current);

        // Tính toán index ảnh tiếp theo
        current = (current + step + slides.length) % slides.length;

        // Di chuyển đường ray ảnh (sliding effect)
        track.style.transform = `translateX(-${current * 100}%)`;

        // Cập nhật lại trạng thái
        slider.dataset.current = current;
    }

    // Hàm xử lý khi click nút thủ công
    function moveSlide(btn, step) {
        const slider = btn.closest('.tour-card-slider');
        changeSlide(slider, step);
    }

    // Chạy khi DOM sẵn sàng
    document.addEventListener('DOMContentLoaded', initSliders);
</script>

<script>
    // Kiểm tra kết quả trả về từ MoMo khi quay lại trang chủ
    <?php if (isset($_GET['resultCode'])): ?>
        const resultCode = "<?= htmlspecialchars($_GET['resultCode']) ?>";
        if (resultCode === "0") {
            Swal.fire({
                title: '<span class="uppercase font-black text-sm italic tracking-widest text-emerald-600">Thanh toán thành công!</span>',
                html: '<p class="text-xs font-bold text-slate-500 uppercase tracking-tighter">Cảm ơn bạn đã tin tưởng Lily Travel. Chuyến đi của bạn đã sẵn sàng!</p>',
                icon: 'success',
                confirmButtonColor: '#0f172a',
                customClass: { popup: 'rounded-[3rem]', confirmButton: 'rounded-xl px-12 py-4 font-black uppercase text-[10px] tracking-widest' }
            }).then(() => {
                // Làm sạch URL để tránh hiện lại thông báo khi F5
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        } else {
            Swal.fire({
                title: '<span class="uppercase font-black text-sm italic tracking-widest text-red-500">Thanh toán thất bại</span>',
                text: 'Giao dịch không thành công hoặc đã bị hủy. Vui lòng thử lại hoặc liên hệ hỗ trợ.',
                icon: 'error',
                confirmButtonColor: '#0f172a',
                customClass: { popup: 'rounded-[3rem]', confirmButton: 'rounded-xl px-12 py-4 font-black uppercase text-[10px] tracking-widest' }
            });
        }
    <?php endif; ?>
</script>

<?php include 'footer.php'; ?>