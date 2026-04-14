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
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-7xl mx-auto px-4 mt-8">
    <header class="relative h-[400px] rounded-[2.5rem] overflow-hidden flex items-center justify-center text-white shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-b from-black/20 to-black/60 z-10"></div>
        
        <div class="absolute inset-0 bg-[url('assets/uploads/banner.png')] bg-cover bg-center transition-transform duration-700 hover:scale-110"></div>
        
        <div class="relative z-20 text-center px-6 w-full max-w-2xl">
            <h1 class="text-4xl md:text-5xl font-black mb-4 uppercase tracking-tighter italic">
                Hành trình mơ ước
            </h1>
            
            <p class="text-sm md:text-base mb-8 font-medium text-gray-200 uppercase tracking-[0.3em]">
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
                
                <button class="bg-yellow-500 hover:bg-yellow-600 text-slate-900 px-6 py-3 rounded-xl font-black text-xs uppercase transition-all">
                    Tìm kiếm
                </button>
                <div class="search-suggestions absolute top-full left-0 w-full bg-white mt-2 rounded-2xl shadow-2xl z-50 hidden overflow-hidden border border-slate-100"></div>
            </form>
        </div>
    </header>
</div>

<main class="max-w-7xl mx-auto px-4 py-16">
    <div class="flex flex-col lg:flex-row gap-12">

        <!-- SIDEBAR -->
        <aside class="w-full lg:w-1/4">
            <div class="sticky top-28">
                
                <div class="group bg-slate-50/50 p-6 rounded-[2.5rem] border border-slate-100 hover:bg-white hover:shadow-2xl transition-all duration-500 cursor-default">
                    <h3 class="text-xs font-black uppercase tracking-widest text-blue-600 flex items-center leading-none">
                        <span class="w-6 h-1 bg-blue-600 rounded-full mr-3"></span> 
                        Danh mục Tour
                        <i class="fas fa-chevron-down ml-auto text-[8px] transition-transform duration-500 group-hover:rotate-180 opacity-40"></i>
                    </h3>

                    <div class="space-y-2 max-h-0 opacity-0 overflow-hidden group-hover:max-h-[600px] group-hover:opacity-100 group-hover:mt-6 transition-all duration-700 ease-in-out">
                        <a href="tours.php" class="flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-50 hover:border-blue-100 hover:shadow-md transition-all group/item">
                            <div class="flex items-center">
                                <span class="font-bold text-gray-700 group-hover/item:text-blue-600">
                                    Tất cả chuyến đi
                                </span>
                            </div>
                            <i class="fas fa-chevron-right text-[10px] text-gray-300 group-hover/item:text-blue-600"></i>
                        </a>

                        <?php foreach($categories as $cat): ?>
                            <a href="tours.php?category=<?= $cat['id'] ?>" class="flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-50 hover:border-blue-100 hover:shadow-md transition-all group/item">
                                <div class="flex items-center">
                                    <span class="font-bold text-gray-700 group-hover/item:text-blue-600">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </span>
                                </div>
                                <i class="fas fa-chevron-right text-[10px] text-gray-300 group-hover/item:text-blue-600"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- PROMO -->
                <div class="mt-12 bg-slate-900 rounded-[2rem] p-8 text-white relative overflow-hidden shadow-xl">
                    <div class="relative z-10">
                        <h4 class="text-xl font-black leading-tight mb-4">
                            Đặt tour ngay hôm nay
                        </h4>
                        <p class="text-xs text-gray-400 mb-6 font-medium">
                            Đăng ký ngay hôm nay để nhận ưu đãi đặc biệt.
                        </p>
                        <button onclick="showPromoModal()" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-500 transition-all">
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
                <h2 class="text-2xl font-black text-gray-900 uppercase italic">
                    Chuyến đi mới nhất
                </h2>
                <a href="tours.php" class="text-[10px] font-black uppercase tracking-widest text-blue-600 hover:underline">
                    Khám phá thêm <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach($tours as $tour): ?>
                    <div class="bg-white rounded-[2rem] overflow-hidden border border-gray-100 hover:shadow-2xl transition-all duration-500 group">
                        
                        <!-- Bọc ảnh trong container để làm hiệu ứng nhảy ảnh -->
                        <div class="relative overflow-hidden h-56 tour-card-slider">
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

                            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-[10px] font-black text-blue-600 shadow-sm uppercase">
                                <?= $tour['duration'] ?>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="text-lg font-bold text-gray-800 leading-tight pr-4">
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
                                        <span class="text-lg font-black text-blue-600 tracking-tighter"><?= number_format($min_p, 0, ',', '.') ?>đ</span>
                                        <span class="text-slate-300 font-black">-</span>
                                        <span class="text-lg font-black text-blue-600 tracking-tighter"><?= number_format($max_p, 0, ',', '.') ?>đ</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-gray-50 mt-4">
                                <div class="flex items-center text-gray-400 text-[10px] font-bold uppercase tracking-widest">
                                    <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                                    <?= $tour['departure_location'] ?>
                                </div>

                                <a href="tour-detail.php?id=<?= $tour['id'] ?>" class="bg-gray-900 text-white px-5 py-2.5 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-600 transition-colors shadow-lg shadow-gray-200">
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

</main>

<script>
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

<?php include 'footer.php'; ?>