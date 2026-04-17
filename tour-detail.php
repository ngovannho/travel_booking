<?php
require_once 'config.php';
include 'header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT t.*, c.name as cat_name, 
                      (SELECT GROUP_CONCAT(image_path) FROM tour_images WHERE tour_id = t.id) as extra_images 
                      FROM tours t 
                      LEFT JOIN categories c ON t.category_id = c.id 
                      WHERE t.id = ? AND t.status = 1");
$stmt->execute([$id]);
$tour = $stmt->fetch();

if (!$tour) { header("Location: index.php"); exit; }

$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$user_booking = null;
if (isset($_SESSION['user'])) {
    $stmt_check = $pdo->prepare("
    SELECT id, status, total_price 
    FROM bookings 
    WHERE user_id = ? 
    AND tour_id = ? 
    AND status IN ('pending','confirmed','balance_pending','completed')
    ORDER BY id DESC 
    LIMIT 1
");
    $stmt_check->execute([$_SESSION['user']['id'], $id]);
    $user_booking = $stmt_check->fetch();

    // Lấy mã giảm giá user đang có
    $stmt_my_promos = $pdo->prepare("SELECT p.* FROM user_promos up JOIN promos p ON up.promo_id = p.id WHERE up.user_id = ? AND up.is_used = 0 AND (p.expiry_date IS NULL OR p.expiry_date >= CURDATE())");
    $stmt_my_promos->execute([$_SESSION['user']['id']]);
    $my_promos = $stmt_my_promos->fetchAll();
}

$is_wishlisted = false;
if (isset($_SESSION['user'])) {
    $stmt_wish = $pdo->prepare("SELECT 1 FROM wishlists WHERE user_id = ? AND tour_id = ?");
    $stmt_wish->execute([$_SESSION['user']['id'], $id]);
    $is_wishlisted = (bool)$stmt_wish->fetch();
}
?>

<!-- Thêm Meta Tags SEO & Social Sharing -->
<meta name="description" content="<?= htmlspecialchars($tour['description']) ?>">
<meta property="og:title" content="<?= htmlspecialchars($tour['title']) ?> | Lily Travel">
<meta property="og:description" content="<?= htmlspecialchars($tour['description']) ?>">
<meta property="og:image" content="<?= (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" ?>/travel_booking/assets/uploads/<?= $tour['image'] ?>">
<meta property="og:url" content="<?= $current_url ?>">
<meta property="og:type" content="website">

<?php
$stmt_reviews = $pdo->prepare("SELECT r.*, u.fullname FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.tour_id = ? ORDER BY r.created_at DESC");
$stmt_reviews->execute([$id]);
$reviews = $stmt_reviews->fetchAll();

// Tính toán số chỗ còn trống cho từng ngày khởi hành
$dates_raw = explode(',', $tour['departure_dates'] ?? '');
$availability_data = [];
$max_capacity = ($tour['max_people'] > 0) ? (int)$tour['max_people'] : (int)$tour['max_participants'];

foreach ($dates_raw as $date_item) {
    $date_item = trim($date_item);
    if (!$date_item) continue;
    
    $stmt_count = $pdo->prepare("SELECT SUM(num_adults + num_children + num_infants) FROM bookings WHERE tour_id = ? AND departure_date = ? AND status != 'cancelled'");
    $stmt_count->execute([$id, $date_item]);
    $booked_count = (int)$stmt_count->fetchColumn();
    
    $availability_data[$date_item] = max(0, $max_capacity - $booked_count);
}

// Lấy các tour liên quan (cùng danh mục, ngoại trừ tour hiện tại)
$stmt_related = $pdo->prepare("
    SELECT t.*, c.name as cat_name 
    FROM tours t 
    LEFT JOIN categories c ON t.category_id = c.id 
    WHERE t.category_id = ? AND t.id != ? AND t.status = 1 
    ORDER BY RAND() LIMIT 3");
$stmt_related->execute([$tour['category_id'], $id]);
$related_tours = $stmt_related->fetchAll();
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .tour-content-scroll { max-height: 550px; overflow-y: auto; padding-right: 15px; }
    .tour-content-scroll::-webkit-scrollbar { width: 4px; }
    .tour-content-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
    .tour-content-scroll::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 10px; }
    .font-black-italic { font-weight: 900; font-style: italic; }
</style>

<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            
            <div class="lg:col-span-8">
                <div class="mb-8">
                    <nav class="flex text-[10px] font-black uppercase tracking-[0.2em] text-blue-600 mb-4 items-center">
                        <a href="index.php" class="hover:opacity-70 transition">Trang chủ</a>
                        <i class="fas fa-chevron-right mx-3 text-[8px] text-gray-300"></i>
                        <span class="text-gray-400 italic"><?= $tour['cat_name'] ?></span>
                    </nav>
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                        <h1 class="text-4xl md:text-6xl font-black-italic text-slate-900 leading-[1.1] uppercase tracking-tighter mb-6 flex-1">
                            <?= htmlspecialchars($tour['title']) ?>
                        </h1>
                        <button onclick="toggleWishlist(<?= $id ?>)" id="wishlist-btn" 
                                class="w-16 h-16 rounded-[2rem] flex items-center justify-center transition-all shadow-xl group <?= $is_wishlisted ? 'bg-red-50 text-red-500 border-2 border-red-100' : 'bg-white text-slate-300 border-2 border-slate-50 hover:text-red-400' ?>">
                            <i class="fa<?= $is_wishlisted ? 's' : 'r' ?> fa-heart text-2xl group-active:scale-125 transition-transform"></i>
                        </button>
                    </div>
                </div>

            <!-- Bọc ảnh trong container để làm hiệu ứng nhảy ảnh -->
            <div class="rounded-[3.5rem] overflow-hidden shadow-2xl shadow-blue-100/50 mb-12 border-[12px] border-white relative group tour-card-slider h-[550px]">
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
                    <button type="button" onclick="event.preventDefault(); moveSlide(this, -1)" class="absolute left-6 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/20 hover:bg-white/80 backdrop-blur text-slate-900 rounded-full opacity-0 group-hover:opacity-100 transition-all z-20 flex items-center justify-center">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" onclick="event.preventDefault(); moveSlide(this, 1)" class="absolute right-6 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/20 hover:bg-white/80 backdrop-blur text-slate-900 rounded-full opacity-0 group-hover:opacity-100 transition-all z-20 flex items-center justify-center">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                <?php endif; ?>

                    <div class="absolute bottom-8 left-8 flex gap-4">
                        <div class="bg-white/90 backdrop-blur-md px-6 py-3 rounded-2xl shadow-xl border border-white/50">
                            <p class="text-[9px] font-black text-blue-600 uppercase mb-1">Thời gian</p>
                            <p class="text-sm font-bold text-slate-800"><?= $tour['duration'] ?></p>
                        </div>
                        <div class="bg-white/90 backdrop-blur-md px-6 py-3 rounded-2xl shadow-xl border border-white/50">
                            <p class="text-[9px] font-black text-orange-600 uppercase mb-1">Khởi hành</p>
                            <p class="text-sm font-bold text-slate-800"><?= $tour['departure_location'] ?></p>
                        </div>
                        <!-- Weather Badge Overlay - MỚI -->
                        <div id="weather-badge-overlay" class="bg-white/90 backdrop-blur-md px-6 py-3 rounded-2xl shadow-xl border border-white/50 hidden">
                            <p class="text-[9px] font-black text-amber-500 uppercase mb-1">Thời tiết</p>
                            <div class="flex items-center gap-2">
                                <span id="overlay-weather-icon" class="text-amber-500 text-xs"></span>
                                <p id="overlay-weather-temp" class="text-sm font-bold text-slate-800">--°C</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-10 md:p-14 rounded-[3.5rem] shadow-xl shadow-slate-200/40 border border-slate-50 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-blue-50/50 rounded-full -mr-20 -mt-20"></div>
                    <h3 class="text-2xl font-black-italic uppercase text-slate-800 mb-10 flex items-center relative z-10">
                        <span class="w-12 h-2 bg-blue-600 rounded-full mr-5 shadow-lg shadow-blue-200"></span> 
                        Lịch trình chi tiết
                    </h3>
                    <div class="tour-content-scroll prose prose-slate max-w-none text-slate-600 leading-relaxed font-medium text-lg relative z-10">
                        <?= $tour['content'] ?>
                    </div>
                </div>

                <!-- Social Share Section - MỚI -->
                <div class="mt-10 p-8 bg-slate-900 rounded-[3rem] text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-2xl">
                    <div>
                        <h4 class="text-xl font-black-italic tracking-tighter mb-1 uppercase">Chia sẻ hành trình</h4>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Gợi ý chuyến đi này cho bạn bè và người thân</p>
                    </div>
                    <div class="flex gap-4">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($current_url) ?>" target="_blank" class="w-12 h-12 bg-white/10 hover:bg-blue-600 rounded-2xl flex items-center justify-center transition-all shadow-lg group" title="Chia sẻ Facebook">
                            <i class="fab fa-facebook-f group-hover:scale-110 transition-transform"></i>
                        </a>
                        <a href="https://sp.zalo.me/share/base?url=<?= urlencode($current_url) ?>" target="_blank" class="w-12 h-12 bg-white/10 hover:bg-blue-500 rounded-2xl flex items-center justify-center transition-all shadow-lg group" title="Chia sẻ Zalo">
                            <span class="font-black text-[9px] group-hover:scale-110 transition-transform">ZALO</span>
                        </a>
                        <button onclick="copyTourUrl()" class="w-12 h-12 bg-white/10 hover:bg-emerald-500 rounded-2xl flex items-center justify-center transition-all shadow-lg group" title="Sao chép liên kết">
                            <i class="fas fa-link group-hover:scale-110 transition-transform"></i>
                        </button>
                    </div>
                </div>

                <!-- Google Maps Section - MỚI -->
                <div class="mt-10 bg-white p-10 md:p-14 rounded-[3.5rem] shadow-xl shadow-slate-200/40 border border-slate-50 overflow-hidden relative">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50/50 rounded-full -mr-16 -mt-16"></div>
                    <h3 class="text-2xl font-black-italic uppercase text-slate-800 mb-8 flex items-center relative z-10">
                        <span class="w-12 h-2 bg-blue-600 rounded-full mr-5 shadow-lg shadow-blue-200"></span> 
                        Vị trí khởi hành
                    </h3>
                    
                    <div class="rounded-[2.5rem] overflow-hidden h-80 shadow-inner border border-slate-100 relative group z-10">
                        <iframe 
                            class="w-full h-full grayscale group-hover:grayscale-0 transition-all duration-700"
                            frameborder="0" 
                            scrolling="no" 
                            marginheight="0" 
                            marginwidth="0" 
                            src="https://maps.google.com/maps?q=<?= urlencode($tour['departure_location']) ?>&t=&z=15&ie=UTF8&iwloc=&output=embed">
                        </iframe>
                    </div>
                    <p class="mt-6 text-[10px] font-black text-slate-400 uppercase italic tracking-widest text-center">
                        <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i> Điểm đón: <?= htmlspecialchars($tour['departure_location']) ?>
                    </p>
                </div>

                <!-- Weather Forecast Section - MỚI -->
                <div id="weather-section" class="mt-10 bg-white p-10 md:p-14 rounded-[3.5rem] shadow-xl shadow-slate-200/40 border border-slate-50 relative overflow-hidden hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-amber-50/50 rounded-full -mr-16 -mt-16"></div>
                    <h3 class="text-2xl font-black-italic uppercase text-slate-800 mb-8 flex items-center relative z-10">
                        <span class="w-12 h-2 bg-amber-400 rounded-full mr-5 shadow-lg shadow-amber-100"></span> 
                        Thời tiết tại điểm khởi hành
                    </h3>
                    
                    <div class="flex flex-col md:flex-row items-center justify-between gap-8 relative z-10">
                        <div class="flex items-center gap-8">
                            <div id="weather-icon" class="text-7xl text-amber-400">
                                <i class="fas fa-sun"></i>
                            </div>
                            <div>
                                <p id="weather-temp" class="text-5xl font-black text-slate-800 leading-none tracking-tighter mb-2">--°C</p>
                                <div class="flex gap-4 mb-3">
                                    <span class="text-[9px] font-black text-red-500 uppercase bg-red-50 px-2 py-0.5 rounded-lg border border-red-100 flex items-center"><i class="fas fa-arrow-up mr-1 text-[7px]"></i> Cao: <span id="weather-max" class="ml-1">--</span>°C</span>
                                    <span class="text-[9px] font-black text-blue-500 uppercase bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-100 flex items-center"><i class="fas fa-arrow-down mr-1 text-[7px]"></i> Thấp: <span id="weather-min" class="ml-1">--</span>°C</span>
                                </div>
                                <div class="flex gap-3 mb-3">
                                    <span class="text-[9px] font-black text-slate-500 uppercase bg-slate-50 px-2 py-0.5 rounded-lg border border-slate-100 flex items-center"><i class="fas fa-tint mr-1 text-[7px] text-blue-400"></i> Độ ẩm: <span id="weather-humidity" class="ml-1">--</span>%</span>
                                    <span class="text-[9px] font-black text-slate-500 uppercase bg-slate-50 px-2 py-0.5 rounded-lg border border-slate-100 flex items-center"><i class="fas fa-wind mr-1 text-[7px] text-teal-400"></i> Gió: <span id="weather-wind" class="ml-1">--</span>km/h</span>
                                </div>
                                <p id="weather-desc" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] italic">Đang tải...</p>
                            </div>
                        </div>
                        <div class="flex-1 max-w-sm text-center md:text-right">
                            <p class="text-xs font-bold text-slate-500 italic leading-relaxed">
                                <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                                Dự báo thời tiết tại <span class="text-slate-800 font-black"><?= htmlspecialchars($tour['departure_location']) ?></span> giúp bạn có sự chuẩn bị tốt nhất về trang phục và sức khỏe cho hành trình.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Phần Đánh giá từ khách hàng -->
                <div class="mt-10 bg-white p-10 md:p-14 rounded-[3.5rem] shadow-xl shadow-slate-200/40 border border-slate-50">
                    <h3 class="text-2xl font-black-italic uppercase text-slate-800 mb-10 flex items-center">
                        <span class="w-12 h-2 bg-amber-400 rounded-full mr-5 shadow-lg shadow-amber-100"></span> 
                        Đánh giá từ du khách
                    </h3>
                    
                    <div class="space-y-8">
                        <?php if (empty($reviews)): ?>
                            <p class="text-xs font-black text-slate-300 uppercase tracking-widest italic text-center py-10">Chưa có đánh giá nào cho tour này</p>
                        <?php endif; ?>

                        <?php foreach($reviews as $rev): ?>
                            <div class="p-6 bg-slate-50/50 rounded-[2.5rem] border border-slate-100">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center gap-4">
                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($rev['fullname']) ?>&background=random" class="w-10 h-10 rounded-2xl shadow-sm">
                                        <div>
                                            <p class="text-[11px] font-black text-slate-800 uppercase italic leading-none mb-1"><?= htmlspecialchars($rev['fullname']) ?></p>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase"><?= date('d/m/Y', strtotime($rev['created_at'])) ?></p>
                                        </div>
                                    </div>
                                    <div class="flex text-amber-400 gap-1">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="<?= $i <= $rev['rating'] ? 'fas' : 'far' ?> fa-star text-[10px]"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="text-sm font-medium text-slate-600 italic leading-relaxed pl-2 border-l-2 border-slate-200">
                                    "<?= htmlspecialchars($rev['comment']) ?>"
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Phần Tour liên quan -->
                <?php if (!empty($related_tours)): ?>
                <div class="mt-16">
                    <div class="flex items-center gap-4 mb-10">
                        <div class="h-px flex-1 bg-slate-200"></div>
                        <h3 class="text-lg font-black text-slate-400 uppercase italic tracking-widest leading-none">Tour liên quan</h3>
                        <div class="h-px flex-1 bg-slate-200"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php foreach($related_tours as $rt): ?>
                        <a href="tour-detail.php?id=<?= $rt['id'] ?>" class="bg-white p-4 rounded-[2.5rem] border border-slate-50 hover:shadow-xl transition-all group flex flex-col">
                            <div class="relative h-40 rounded-[2rem] overflow-hidden mb-4">
                                <img src="assets/uploads/<?= $rt['image'] ?: 'default-tour.jpg' ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                <div class="absolute top-3 left-3 bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-[8px] font-black text-blue-600 uppercase tracking-widest shadow-sm">
                                    <?= $rt['cat_name'] ?>
                                </div>
                            </div>
                            <div class="px-2 flex-1">
                                <h4 class="text-xs font-black text-slate-800 uppercase italic leading-tight mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                    <?= htmlspecialchars($rt['title']) ?>
                                </h4>
                                
                                <div class="flex items-center justify-between mt-auto pt-3 border-t border-slate-50">
                                    <div>
                                        <p class="text-[8px] font-bold text-slate-400 uppercase italic leading-none mb-1">Giá từ</p>
                                        <p class="text-xs font-black text-blue-600 tracking-tighter"><?= number_format($rt['price_base'], 0, ',', '.') ?>đ</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[8px] font-bold text-slate-400 uppercase italic leading-none mb-1">Khởi hành</p>
                                        <p class="text-[9px] font-black text-slate-700 uppercase tracking-tighter italic leading-none truncate max-w-[80px]"><?= $rt['departure_location'] ?></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-4">
                <div class="sticky top-28 space-y-8">
                    <div class="bg-white rounded-[3.5rem] p-10 shadow-2xl border border-slate-50 relative overflow-hidden group">
                        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-600 to-indigo-600"></div>
                        
                        <div class="mb-10 text-center">
                            <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.3em] mb-3 italic">
                                Tổng thanh toán (MoMo)
                            </p>
                            <div class="inline-flex items-baseline bg-slate-50 px-6 py-2 rounded-2xl">
                                <span id="display_total_price_top" class="text-4xl font-black text-blue-600 tracking-tighter">
                                    <?php 
                                        echo $user_booking ? number_format($user_booking['total_price'], 0, ',', '.') . 'đ' : '0đ';
                                    ?>
                                </span>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['user'])): ?>
                            <?php if ($user_booking && trim($user_booking['status']) != 'completed' && trim($user_booking['status']) != 'cancelled'): ?>
                                <div class="p-8 bg-blue-50 rounded-[2.5rem] text-center border border-blue-100">
                                    <div class="w-16 h-16 bg-white rounded-3xl flex items-center justify-center mx-auto mb-5 text-blue-500 shadow-sm"><i class="fas fa-spinner fa-spin"></i></div>
                                    <p class="text-xs font-black text-blue-800 uppercase italic leading-tight">Đơn hàng đang chờ xử lý<br><span class="text-[10px] opacity-60">Vui lòng hoàn tất thanh toán qua MoMo.</span></p>
                                </div>
                            <?php else: ?>
                                <form id="bookingForm" action="booking_process.php" method="POST" class="space-y-5">
                                    <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
                                    <input type="hidden" name="total_price" id="final_price_input" value="0">
                                    
                                    <div class="space-y-3">
                                        <div class="space-y-1">
                                            <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Ngày khởi hành</label>
                                        <select name="departure_date" id="departure_date" onchange="updateAvailabilityInfo()" required class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500">
                                            <?php foreach($availability_data as $date => $slots): ?>
                                                <option value="<?= htmlspecialchars($date) ?>" data-slots="<?= $slots ?>">
                                                    <?= htmlspecialchars($date) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div id="availability-badge" class="mt-2 hidden">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border border-emerald-100">
                                                <i class="fas fa-users mr-1.5"></i> Còn trống: <span id="remaining-slots" class="ml-1">0</span> chỗ
                                            </span>
                                        </div>
                                        </div>

                                        <div class="grid grid-cols-3 gap-2">
                                            <div class="space-y-1">
                                                <label class="text-[9px] font-black uppercase text-slate-400 text-center block w-full">Người lớn<br>(<?= number_format($tour['price_base'], 0, ',', '.') ?>đ)</label>
                                                <input type="number" name="num_adults" id="n_adults" value="0" min="0" oninput="updateTotalPrice()" class="w-full p-3 bg-slate-50 border-0 rounded-xl text-xs font-bold text-center">
                                            </div>
                                            <div class="space-y-1">
                                                <label class="text-[9px] font-black uppercase text-slate-400 text-center block w-full">Người cao tuổi<br>(<?= number_format($tour['price_infant'] ?? 0, 0, ',', '.') ?>đ)</label>
                                                <input type="number" name="num_infants" id="n_infants" value="0" min="0" oninput="updateTotalPrice()" class="w-full p-3 bg-slate-50 border-0 rounded-xl text-xs font-bold text-center">
                                            </div>
                                            <div class="space-y-1">
                                                <label class="text-[9px] font-black uppercase text-slate-400 text-center block w-full">Trẻ em<br>(<?= number_format($tour['price_child'] ?? 0, 0, ',', '.') ?>đ)</label>
                                                <input type="number" name="num_children" id="n_children" value="0" min="0" oninput="updateTotalPrice()" class="w-full p-3 bg-slate-50 border-0 rounded-xl text-xs font-bold text-center">
                                            </div>
                                        </div>

                                        <input type="text" name="customer_name" value="<?= $_SESSION['user']['fullname'] ?>" required class="w-full p-4.5 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500 transition-all text-center">
                                        <input type="text" name="customer_phone" value="<?= $_SESSION['user']['phone'] ?>" required class="w-full p-4.5 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500 transition-all text-center">
                                    </div>

                                    <div class="space-y-1">
                                        <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Hình thức thanh toán</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <label class="relative flex flex-col p-4 bg-slate-50 rounded-2xl border-2 border-transparent cursor-pointer hover:bg-white hover:border-blue-500 transition-all group has-[:checked]:border-blue-600 has-[:checked]:bg-white" onclick="toggleBankInfo(false)">
                                                <input type="radio" name="payment_method" value="momo" checked class="hidden" onchange="toggleBankInfo(false)">
                                                <i class="fas fa-wallet text-xl text-slate-300 group-hover:text-blue-500 mb-2 transition-colors group-has-[:checked]:text-blue-600"></i>
                                                <span class="text-[10px] font-black uppercase tracking-tighter text-slate-500 group-has-[:checked]:text-slate-900">Ví MoMo</span>
                                            </label>
                                            <label class="relative flex flex-col p-4 bg-slate-50 rounded-2xl border-2 border-transparent cursor-pointer hover:bg-white hover:border-blue-500 transition-all group has-[:checked]:border-blue-600 has-[:checked]:bg-white" onclick="toggleBankInfo(true)">
                                                <input type="radio" name="payment_method" value="bank" class="hidden" onchange="toggleBankInfo(true)">
                                                <i class="fas fa-university text-xl text-slate-300 group-hover:text-blue-500 mb-2 transition-colors group-has-[:checked]:text-blue-600"></i>
                                                <span class="text-[10px] font-black uppercase tracking-tighter text-slate-500 group-has-[:checked]:text-slate-900">Chuyển khoản</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Khối thông tin ngân hàng hiện ra khi chọn Chuyển khoản -->
                                    <div id="bank-info-display" class="hidden mt-4 p-5 bg-blue-50 rounded-2xl border border-blue-100 animate-in fade-in slide-in-from-top-2 duration-300 shadow-inner">
                                        <h5 class="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-3 flex items-center"><i class="fas fa-university mr-2"></i> Tài khoản thanh toán</h5>
                                        <div class="flex items-center gap-4">
                                            <div class="flex-1 space-y-1 text-[10px] font-bold text-slate-600">
                                                <p>Ngân hàng: <span class="text-slate-900 uppercase">MB BANK</span></p>
                                                <p>Số tài khoản: <span class="text-blue-600 font-black">0777454550</span></p>
                                                <p>Chủ tài khoản: <span class="text-slate-900 uppercase">NGO VAN NHO</span></p>
                                            </div>
                                            <div class="w-20 h-20 bg-white p-1 rounded-xl shadow-sm border border-blue-100">
                                                <img src="https://img.vietqr.io/image/MB-0777454550-compact2.png?accountName=NGO VAN NHO" alt="QR Preview" class="w-full h-full object-contain">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="text-[9px] font-black uppercase text-slate-400 ml-1 italic">Chọn mã ưu đãi của bạn</label>
                                        <select name="promo_code" id="promo_code" onchange="updateTotalPrice()" class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-emerald-500 transition-all text-center uppercase appearance-none">
                                            <option value="">-- Không sử dụng mã --</option>
                                            <?php if(isset($my_promos)): foreach($my_promos as $mp): ?>
                                                <option value="<?= $mp['code'] ?>" data-percent="<?= $mp['percent'] ?>">
                                                    <?= $mp['code'] ?> (Giảm <?= $mp['percent'] ?>%)
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                        <p id="promo_message" class="text-[9px] font-bold text-center mt-1 hidden"></p>
                                    </div>

                                    <div class="pt-4 border-t border-dashed border-slate-200 mt-4">
                                        <button type="button" id="submit-booking-btn" onclick="handleBooking()" class="w-full bg-slate-900 text-white py-5 rounded-[1.8rem] font-black uppercase text-[11px] tracking-[0.2em] shadow-2xl shadow-slate-300 hover:bg-blue-600 transition-all active:scale-95 flex items-center justify-center">
                                            ĐẶT TOUR NGAY <i class="fas fa-chevron-right ml-3 text-[8px]"></i>
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" class="block w-full bg-slate-900 text-white py-5 rounded-[1.8rem] font-black uppercase text-[11px] tracking-[0.2em] text-center shadow-2xl">ĐĂNG NHẬP ĐỂ ĐẶT VÉ</a>
                        <?php endif; ?>
                    </div>

                    <div class="bg-slate-900 rounded-[3rem] p-10 text-white relative overflow-hidden group shadow-2xl shadow-slate-900/30">
                        <div class="relative z-10">
                            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500 mb-3 italic leading-none">Cần hỗ trợ?</p>
                            <a href="tel:0777454550" class="flex items-center group-hover:translate-x-1 transition-transform">
                                <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center mr-5 shadow-lg shadow-blue-500/30"><i class="fas fa-phone-alt text-sm"></i></div>
                                <span class="text-2xl font-black tracking-tighter">0777454550</span>
                            </a>
                        </div>
                        <i class="fas fa-globe-asia absolute -bottom-6 -right-6 text-9xl text-white/5 -rotate-12 transition-transform duration-1000 group-hover:rotate-0"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateTotalPrice() {
        if (!document.getElementById('n_adults')) return;

        const pAdult = <?= (float)$tour['price_base'] ?>;
        const pSenior = <?= (float)($tour['price_infant'] ?? 0) ?>;
        const pChild = <?= (float)($tour['price_child'] ?? 0) ?>;

        const nAdult = parseInt(document.getElementById('n_adults').value) || 0;
        const nSenior = parseInt(document.getElementById('n_infants').value) || 0;
        const nChild = parseInt(document.getElementById('n_children').value) || 0;

        let total = (nAdult * pAdult) + (nSenior * pSenior) + (nChild * pChild);
        
        // Xử lý mã giảm giá
        const promoSelect = document.getElementById('promo_code');
        const selectedOption = promoSelect.options[promoSelect.selectedIndex];
        const promoCode = promoSelect.value;
        
        // Ưu tiên mã cá nhân, nếu không có lấy mã mặc định của tour
        let discountPercent = selectedOption.dataset.percent ? parseInt(selectedOption.dataset.percent) : 0;
        const msg = document.getElementById('promo_message');

        if (promoCode === "") {
            msg.classList.add('hidden');
        } else {
            const discountAmount = (total * discountPercent) / 100;
            total = total - discountAmount;
            msg.innerText = `Áp dụng thành công! Giảm ${discountPercent}% (-${discountAmount.toLocaleString('vi-VN')}đ)`;
            msg.className = "text-[9px] font-bold text-center mt-1 text-emerald-500 block";
        }

        const formatted = total.toLocaleString('vi-VN') + 'đ';
        document.getElementById('display_total_price_top').innerText = formatted;
        document.getElementById('final_price_input').value = total;
    }

    function updateAvailabilityInfo() {
        const select = document.getElementById('departure_date');
        const badge = document.getElementById('availability-badge');
        const bookingBtn = document.getElementById('submit-booking-btn');
        
        if (!select) return;
        
        const selectedOption = select.options[select.selectedIndex];
        if (!selectedOption) return;

        const slots = parseInt(selectedOption.dataset.slots) || 0;
        badge.classList.remove('hidden');
        const badgeInner = badge.querySelector('span');

        if (slots <= 0) {
            // Trường hợp hết chỗ
            badgeInner.className = "inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-red-50 text-red-600 border border-red-100";
            badgeInner.innerHTML = '<i class="fas fa-exclamation-circle mr-1.5"></i> Ngày này đã hết chỗ';
            
            if (bookingBtn) {
                bookingBtn.disabled = true;
                bookingBtn.innerHTML = 'HẾT CHỖ';
                bookingBtn.classList.remove('bg-slate-900', 'hover:bg-blue-600');
                bookingBtn.classList.add('bg-slate-300', 'text-slate-500', 'cursor-not-allowed');
            }
        } else {
            // Trường hợp còn chỗ
            badgeInner.className = "inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border border-emerald-100";
            badgeInner.innerHTML = `<i class="fas fa-users mr-1.5"></i> Còn trống: <span id="remaining-slots" class="ml-1">${slots}</span> chỗ`;
            
            if (bookingBtn) {
                bookingBtn.disabled = false;
                bookingBtn.innerHTML = 'ĐẶT TOUR NGAY <i class="fas fa-chevron-right ml-3 text-[8px]"></i>';
                bookingBtn.classList.add('bg-slate-900', 'hover:bg-blue-600');
                bookingBtn.classList.remove('bg-slate-300', 'text-slate-500', 'cursor-not-allowed');
            }
        }
        
        // Cập nhật giới hạn tối đa (max) cho các ô nhập số lượng
        ['n_adults', 'n_infants', 'n_children'].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.max = slots;
                if (slots <= 0) input.value = 0;
            }
        });
        updateTotalPrice();
    }

    // Hiển thị thông báo nếu có tham số booking_success trên URL
    <?php if(isset($_GET['booking_success'])): ?>
    Swal.fire({
        title: '<span class="uppercase font-black text-sm italic tracking-widest">Đã gửi yêu cầu!</span>',
        html: '<p class="text-xs font-bold text-slate-500">Chúng tôi đã nhận được yêu cầu và gửi email xác nhận cho bạn. Vui lòng kiểm tra hộp thư.</p>',
        icon: 'success',
        confirmButtonColor: '#0f172a',
        customClass: { popup: 'rounded-[3rem]', confirmButton: 'rounded-2xl px-8 font-black uppercase text-[10px]' }
    });
    <?php endif; ?>

    function handleBooking() {
        const total = parseInt(document.getElementById('final_price_input').value);
        if (total <= 0) {
            Swal.fire({ icon: 'error', title: 'Lỗi đặt tour', text: 'Vui lòng chọn số lượng người.' });
            return;
        }

        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        let title = 'Xác nhận đặt tour';
        let text = 'Hệ thống sẽ tạo yêu cầu đặt tour và hướng dẫn bạn thanh toán.';
        let confirmButtonText = 'TIẾP TỤC';

        if (paymentMethod === 'momo') {
            title = 'Chuyển sang thanh toán MoMo';
            text = 'Bạn sẽ được chuyển hướng tới cổng thanh toán an toàn của MoMo.';
            confirmButtonText = 'THANH TOÁN NGAY';
        } else if (paymentMethod === 'bank') {
            title = 'Thanh toán Chuyển khoản';
            text = 'Hệ thống sẽ hiển thị mã QR và thông tin tài khoản để bạn thực hiện chuyển khoản.';
            confirmButtonText = 'TIẾP TỤC QUÉT MÃ';
        }

        Swal.fire({
            title: title,
            text: text,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: confirmButtonText,
            confirmButtonColor: paymentMethod === 'momo' ? '#a50064' : '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('bookingForm').submit();
        });
    }

    // Chạy tính toán ngay khi trang tải xong
    updateTotalPrice();

    // Script tự động nhảy ảnh cho slider
    function initSliders() {
        document.querySelectorAll('.tour-card-slider').forEach((slider) => {
            const track = slider.querySelector('.tour-track');
            if (!track) return;
            const slides = track.querySelectorAll('img');
            if (slides.length <= 1) return;

            slider.dataset.current = 0;
            
            let interval = setInterval(() => {
                changeSlide(slider, 1);
            }, 2000 + Math.random() * 2000); // 2000ms - 4000ms

            slider.addEventListener('mouseenter', () => clearInterval(interval));
            slider.addEventListener('mouseleave', () => {
                interval = setInterval(() => {
                    changeSlide(slider, 1);
                }, 3000);
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

    function moveSlide(btn, step) {
        const slider = btn.closest('.tour-card-slider');
        changeSlide(slider, step);
    }

    function toggleWishlist(tourId) {
        const btn = document.getElementById('wishlist-btn');
        const icon = btn.querySelector('i');
        const formData = new FormData();
        formData.append('tour_id', tourId);

        fetch('ajax_wishlist.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (data.action === 'added') {
                    btn.className = 'w-16 h-16 rounded-[2rem] flex items-center justify-center transition-all shadow-xl group bg-red-50 text-red-500 border-2 border-red-100';
                    icon.className = 'fas fa-heart text-2xl group-active:scale-125 transition-transform';
                } else {
                    btn.className = 'w-16 h-16 rounded-[2rem] flex items-center justify-center transition-all shadow-xl group bg-white text-slate-300 border-2 border-slate-50 hover:text-red-400';
                    icon.className = 'far fa-heart text-2xl group-active:scale-125 transition-transform';
                }
            }
        });
    }

    function copyTourUrl() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: '<span class="text-[10px] font-black uppercase italic">Đã sao chép liên kết tour!</span>',
                showConfirmButton: false, timer: 2000, timerProgressBar: true,
                customClass: { popup: 'rounded-2xl border border-slate-100 shadow-xl' }
            });
        });
    }

    async function loadWeather(location) {
        const weatherSection = document.getElementById('weather-section');
        if (!weatherSection) return;
        try {
            // 1. Lấy tọa độ từ tên địa điểm
            const geoRes = await fetch(`https://geocoding-api.open-meteo.com/v1/search?name=${encodeURIComponent(location)}&count=1&language=vi&format=json`);
            const geoData = await geoRes.json();
            if (!geoData.results || geoData.results.length === 0) return;
            const { latitude, longitude } = geoData.results[0];

            // 2. Lấy dữ liệu thời tiết thực tế
            const weatherRes = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${latitude}&longitude=${longitude}&current=temperature_2m,relative_humidity_2m,wind_speed_10m,weather_code&daily=temperature_2m_max,temperature_2m_min&timezone=auto`);
            const weatherData = await weatherRes.json();
            const current = weatherData.current;
            
            const weatherMap = {
                0: { icon: 'fa-sun', desc: 'Trời quang đãng' },
                1: { icon: 'fa-cloud-sun', desc: 'Ít mây, có nắng' },
                2: { icon: 'fa-cloud-sun', desc: 'Nhiều mây, hửng nắng' },
                3: { icon: 'fa-cloud', desc: 'Trời nhiều mây' },
                45: { icon: 'fa-smog', desc: 'Có sương mù' },
                61: { icon: 'fa-cloud-showers-heavy', desc: 'Có mưa vừa' },
                95: { icon: 'fa-bolt', desc: 'Có dông' }
            };
            const info = weatherMap[current.weather_code] || { icon: 'fa-cloud-sun', desc: 'Thời tiết ổn định' };

            // 3. Hiển thị lên giao diện
            document.getElementById('weather-temp').innerText = `${Math.round(current.temperature_2m)}°C`;
            document.getElementById('weather-max').innerText = Math.round(weatherData.daily.temperature_2m_max[0]);
            document.getElementById('weather-min').innerText = Math.round(weatherData.daily.temperature_2m_min[0]);
            document.getElementById('weather-humidity').innerText = current.relative_humidity_2m;
            document.getElementById('weather-wind').innerText = current.wind_speed_10m;
            document.getElementById('weather-desc').innerText = info.desc;
            document.getElementById('weather-icon').innerHTML = `<i class="fas ${info.icon}"></i>`;
            weatherSection.classList.remove('hidden');

            // 4. Cập nhật thông tin lên Badge trên khung ảnh
            document.getElementById('overlay-weather-temp').innerText = `${Math.round(current.temperature_2m)}°C`;
            document.getElementById('overlay-weather-icon').innerHTML = `<i class="fas ${info.icon}"></i>`;
            document.getElementById('weather-badge-overlay').classList.remove('hidden');

        } catch (e) { console.error("Weather Error:", e); }
    }

    // Chạy khi DOM sẵn sàng
    document.addEventListener('DOMContentLoaded', () => {
        initSliders();
        updateAvailabilityInfo();
        loadWeather(<?= json_encode($tour['departure_location']) ?>);
    });
</script>

<?php include 'footer.php'; ?>