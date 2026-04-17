<?php
require_once 'config.php';
include 'header.php';

if (!isset($_SESSION['user'])) {
    $redirect = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect=$redirect");
    exit;
}

$user_id = $_SESSION['user']['id'];
$user = $pdo->prepare("SELECT u.*, r.name as rank_name, r.color as rank_color, r.icon as rank_icon 
                       FROM users u 
                       LEFT JOIN ranks r ON u.rank_id = r.id 
                       WHERE u.id = ?");
$user->execute([$user_id]);
$curr_user = $user->fetch();

$tab = $_GET['tab'] ?? 'info';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
$count_stmt->execute([$user_id]);
$total_bookings = $count_stmt->fetchColumn();
$total_pages = ceil($total_bookings / $limit);

$booking_id_filter = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if ($booking_id_filter > 0 && $tab == 'tours') {
    // Nếu nhấn từ Mail, lấy đúng đơn hàng đó (không quan tâm phân trang)
    $bookings = $pdo->prepare("SELECT b.*, t.title, t.image, r.rating, r.comment 
                               FROM bookings b 
                               JOIN tours t ON b.tour_id = t.id 
                               LEFT JOIN reviews r ON b.id = r.booking_id 
                               WHERE b.user_id = ? AND b.id = ?");
    $bookings->execute([$user_id, $booking_id_filter]);
    $my_bookings = $bookings->fetchAll();
} else {
    // Hiển thị danh sách phân trang bình thường
    $bookings = $pdo->prepare("SELECT b.*, t.title, t.image, r.rating, r.comment 
                               FROM bookings b 
                               JOIN tours t ON b.tour_id = t.id 
                               LEFT JOIN reviews r ON b.id = r.booking_id 
                               WHERE b.user_id = ? 
                               ORDER BY b.id DESC LIMIT $limit OFFSET $offset");
    $bookings->execute([$user_id]);
    $my_bookings = $bookings->fetchAll();
}

$wishlist_stmt = $pdo->prepare("SELECT t.id, t.title, t.image, t.price_base, c.name as cat_name, w.id as wish_id
                                FROM wishlists w 
                                JOIN tours t ON w.tour_id = t.id 
                                LEFT JOIN categories c ON t.category_id = c.id
                                WHERE w.user_id = ? ORDER BY w.created_at DESC");
$wishlist_stmt->execute([$user_id]);
$my_wishlist = $wishlist_stmt->fetchAll();
?>

<style>
    /* Hiệu ứng đánh giá sao: Sáng tất cả các sao từ trái qua đến vị trí đang chọn/hover */
    .rating-stars label:hover i,
    .rating-stars label:hover ~ label i,
    .rating-stars label:has(input:checked) i,
    .rating-stars label:has(input:checked) ~ label i {
        color: #fbbf24 !important; /* Màu vàng amber-400 */
    }
</style>

<div class="bg-[#f8fafc] min-h-screen pb-20">
    <div class="max-w-6xl mx-auto px-4 pt-10">
        <div class="flex flex-col lg:flex-row gap-8">
            <aside class="w-full lg:w-1/3 space-y-6">
                <div class="bg-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 border border-white relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-24 bg-slate-900"></div>
                    <div class="relative z-10 text-center">
                        <?php 
                            $profile_avatar = $curr_user['avatar'] 
                                ? 'assets/uploads/' . $curr_user['avatar'] 
                                : 'https://ui-avatars.com/api/?name=' . urlencode($curr_user['fullname']) . '&background=random&size=128';
                        ?>
                        <img src="<?= $profile_avatar ?>" class="w-24 h-24 rounded-[2rem] mx-auto border-4 border-white shadow-lg mb-4 object-cover">
                        <h2 class="text-xl font-black text-slate-800 uppercase italic tracking-tighter"><?= htmlspecialchars($curr_user['fullname']) ?></h2>
                        
                        <!-- Hiển thị hạng thành viên -->
                        <div class="mt-2 flex items-center justify-center gap-2">
                            <i class="fas <?= $curr_user['rank_icon'] ?> <?= $curr_user['rank_color'] ?> text-xs"></i>
                            <span class="text-[10px] font-black <?= $curr_user['rank_color'] ?> uppercase tracking-widest">Hạng <?= $curr_user['rank_name'] ?> (<?= number_format($curr_user['loyalty_points']) ?> điểm)</span>
                        </div>
                    </div>

                    <nav class="mt-8 space-y-2">
                        <a href="?tab=info" class="group flex items-center px-4 py-3 rounded-2xl transition-all duration-300 <?= $tab == 'info' ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50' ?>">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center mr-3 transition-colors <?= $tab == 'info' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-400 shadow-inner' ?>">
                                <i class="fas fa-user-circle text-sm"></i>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-widest flex-1">Hồ sơ cá nhân</span>
                            <i class="fas fa-chevron-right text-[8px] opacity-30"></i>
                        </a>

                        <a href="?tab=tours" class="group flex items-center px-4 py-3 rounded-2xl transition-all duration-300 <?= $tab == 'tours' ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50' ?>">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center mr-3 transition-colors <?= $tab == 'tours' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-400 shadow-inner' ?>">
                                <i class="fas fa-map-marked-alt text-sm"></i>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-widest flex-1">Lịch sử đặt vé</span>
                            <i class="fas fa-chevron-right text-[8px] opacity-30"></i>
                        </a>

                        <a href="?tab=promos" class="group flex items-center px-4 py-3 rounded-2xl transition-all duration-300 <?= $tab == 'promos' ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50' ?>">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center mr-3 transition-colors <?= $tab == 'promos' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-400 shadow-inner' ?>">
                                <i class="fas fa-ticket-alt text-sm"></i>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-widest flex-1">Kho ưu đãi</span>
                            <i class="fas fa-chevron-right text-[8px] opacity-30"></i>
                        </a>

                    <a href="?tab=wishlist" class="group flex items-center px-4 py-3 rounded-2xl transition-all duration-300 <?= $tab == 'wishlist' ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-slate-50' ?>">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center mr-3 transition-colors <?= $tab == 'wishlist' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-400 shadow-inner' ?>">
                            <i class="fas fa-heart text-sm"></i>
                        </div>
                        <span class="text-[9px] font-black uppercase tracking-widest flex-1">Yêu thích</span>
                        <i class="fas fa-chevron-right text-[8px] opacity-30"></i>
                    </a>

                        <div class="h-px bg-slate-100 my-4"></div>

                        <a href="logout.php" class="group flex items-center px-4 py-2.5 rounded-2xl text-red-400 hover:bg-red-50 hover:text-red-600 transition-all duration-300">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center mr-3 bg-red-50 text-red-400 group-hover:bg-red-500 group-hover:text-white transition-colors">
                                <i class="fas fa-sign-out-alt text-sm"></i>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest">Đăng xuất</span>
                        </a>
                    </nav>
                </div>
            </aside>

            <div class="w-full lg:w-2/3">
                <?php if ($tab == 'info'): ?>
                    <div class="bg-white rounded-[3rem] p-10 shadow-xl border border-white">
                        <h3 class="text-2xl font-black italic uppercase text-slate-800 mb-8 flex items-center tracking-tighter">
                            <span class="w-10 h-1.5 bg-blue-600 rounded-full mr-4"></span> Hồ sơ cá nhân
                        </h3>
                        <form action="profile_process.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Họ và tên</label>
                                <input type="text" name="fullname" value="<?= htmlspecialchars($curr_user['fullname']) ?>" class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Ảnh đại diện</label>
                                <input type="file" name="avatar" class="w-full p-3 bg-slate-50 border-0 rounded-2xl text-[10px] font-black outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Email</label>
                                <input type="email" value="<?= $curr_user['email'] ?>" readonly class="w-full p-4 bg-slate-100 border-0 rounded-2xl text-xs font-black text-slate-400 cursor-not-allowed">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Số điện thoại</label>
                                <input type="text" name="phone" value="<?= $curr_user['phone'] ?>" class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Mật khẩu mới</label>
                                <input type="password" name="new_password" placeholder="Bỏ trống nếu không đổi" class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2 pt-4">
                                <button type="submit" class="w-full md:w-auto bg-slate-900 text-white px-10 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-blue-600 shadow-xl transition-all">Lưu thay đổi</button>
                            </div>
                        </form>
                    </div>

                <?php elseif ($tab == 'tours'): ?>
                    <div class="bg-white rounded-[3rem] p-10 shadow-xl border border-white">
                        <h3 class="text-2xl font-black italic uppercase text-slate-800 mb-8 flex items-center tracking-tighter">
                            <span class="w-10 h-1.5 bg-blue-600 rounded-full mr-4"></span> Lịch sử đặt Tour
                        </h3>
                        <div class="space-y-3">
                            <?php if (empty($my_bookings)): ?>
                                <p class="text-center text-slate-400 py-10 font-bold uppercase text-[10px] tracking-widest italic">Bạn chưa thực hiện chuyến đi nào</p>
                            <?php endif; ?>

                            <?php foreach($my_bookings as $b): ?>
                                <div class="bg-slate-50/40 rounded-[1.8rem] border border-slate-100 overflow-hidden transition-all hover:bg-white hover:shadow-md group">
                                    <div class="flex items-center gap-4 p-4 cursor-pointer" onclick="toggleDetails(<?= $b['id'] ?>)">
                                        <img src="assets/uploads/<?= $b['image'] ?: 'default.jpg' ?>" class="w-12 h-12 rounded-xl object-cover shadow-sm">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-0.5">
                                                <span class="text-[8px] font-black text-blue-500 uppercase">#<?= $b['id'] ?></span>
                                                <span class="text-[8px] font-bold text-slate-300 uppercase tracking-tighter"><?= date('d/m/Y', strtotime($b['booking_date'])) ?></span>
                                            </div>
                                            <h4 class="text-[11px] font-black text-slate-800 uppercase italic tracking-tighter truncate leading-tight"><?= htmlspecialchars($b['title']) ?></h4>
                                        </div>
                                        <div class="text-right flex flex-col items-end gap-1.5">
                                            <p class="text-[11px] font-black text-slate-900 leading-none"><?= number_format($b['total_price'], 0, ',', '.') ?>đ</p>
                                            <div class="flex items-center">
                                                <?php if ($b['status'] == 'pending'): ?>
                                                    <span class="px-2 py-0.5 bg-amber-50 text-amber-600 border border-amber-100 rounded-md text-[7px] font-black uppercase">Chờ xác nhận</span>
                                                <?php elseif ($b['status'] == 'balance_pending'): ?>
                                                    <span class="px-2 py-0.5 bg-amber-50 text-amber-600 border border-amber-100 rounded-md text-[7px] font-black uppercase">Chờ thanh toán</span>
                                                <?php elseif ($b['status'] == 'confirmed'): ?>
                                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-md text-[7px] font-black uppercase">Đã xác nhận</span>
                                                <?php elseif ($b['status'] == 'completed'): ?>
                                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-600 border border-blue-100 rounded-md text-[7px] font-black uppercase">Hoàn thành</span>
                                                <?php elseif ($b['status'] == 'refunded'): ?>
                                                    <span class="px-2 py-0.5 bg-red-500 text-white border border-red-600 rounded-md text-[7px] font-black uppercase shadow-sm shadow-red-100">Đã hoàn tiền</span>
                                                <?php else: ?>
                                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-400 border border-slate-200 rounded-md text-[7px] font-black uppercase">Đã hủy</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <i id="icon-<?= $b['id'] ?>" class="fas fa-chevron-down text-slate-300 text-[9px] transition-transform duration-300 ml-2 group-hover:text-blue-500"></i>
                                    </div>

                                    <!-- Phần chi tiết thu gọn thủ công -->
                                    <div id="details-<?= $b['id'] ?>" class="hidden border-t border-dashed border-slate-200 bg-white/50 p-5">
                                        <!-- Hướng dẫn chuyển khoản nếu vừa đặt xong -->
                                        <?php if (isset($_GET['payment']) && $_GET['payment'] === 'bank' && $_GET['booking_id'] == $b['id']): ?>
                                            <div class="mb-6 p-6 bg-blue-50 rounded-[2rem] border border-blue-100 shadow-inner">
                                                <h5 class="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-3 flex items-center"><i class="fas fa-university mr-2"></i> Thông tin chuyển khoản</h5>
                                                <div class="flex flex-col md:flex-row gap-6 items-center">
                                                    <div class="flex-1 space-y-2 text-[11px] font-bold text-slate-600">
                                                        <p>Ngân hàng: <span class="text-slate-900 uppercase">MB BANK (Quân Đội)</span></p>
                                                        <p>Số tài khoản: <span class="text-blue-600 font-black">0777454550</span></p>
                                                        <p>Chủ tài khoản: <span class="text-slate-900 uppercase">NGO VAN NHO</span></p>
                                                        <p>Nội dung: <span class="text-red-500 font-black">LILY TRAVEL #<?= $b['id'] ?></span></p>
                                                        <p>Số tiền: <span class="text-blue-600 font-black"><?= number_format($b['total_price'], 0, ',', '.') ?>đ</span></p>
                                                    </div>
                                                    <div class="w-32 h-32 bg-white p-2 rounded-2xl shadow-sm border border-blue-100">
                                                        <img src="https://img.vietqr.io/image/MB-0777454550-compact2.png?amount=<?= (int)$b['total_price'] ?>&addInfo=LILY TRAVEL #<?= $b['id'] ?>&accountName=NGO VAN NHO" alt="QR Thanh toán" class="w-full h-full object-contain">
                                                    </div>
                                                </div>
                                                <p class="mt-4 text-[9px] text-slate-400 italic leading-relaxed">* Hệ thống sẽ tự động cập nhật trạng thái sau khi Admin xác nhận giao dịch thành công (thường từ 5-15 phút).</p>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Tiến trình hoàn tiền - MỚI -->
                                        <?php if ($b['status'] == 'refunded'): ?>
                                            <div class="mb-8 p-6 bg-red-50/50 rounded-[2rem] border border-red-100 relative overflow-hidden">
                                                <div class="absolute top-0 right-0 w-24 h-24 bg-red-100/30 rounded-full -mr-12 -mt-12"></div>
                                                <h5 class="text-[10px] font-black text-red-600 uppercase tracking-widest mb-6 flex items-center relative z-10">
                                                    <i class="fas fa-sync-alt mr-2 animate-spin" style="animation-duration: 3s;"></i> Tiến trình hoàn trả tiền
                                                </h5>
                                                
                                                <div class="relative flex justify-between items-center px-4 max-w-md mx-auto">
                                                    <!-- Đường kẻ nối -->
                                                    <div class="absolute left-10 right-10 h-0.5 bg-red-500 top-4 z-0"></div>
                                                    
                                                    <div class="relative z-10 flex flex-col items-center">
                                                        <div class="w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg shadow-red-200">
                                                            <i class="fas fa-check text-[10px]"></i>
                                                        </div>
                                                        <p class="text-[8px] font-black text-slate-800 uppercase mt-2 tracking-tighter">Yêu cầu hủy</p>
                                                    </div>

                                                    <div class="relative z-10 flex flex-col items-center">
                                                        <div class="w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg shadow-red-200">
                                                            <i class="fas fa-university text-[10px]"></i>
                                                        </div>
                                                        <p class="text-[8px] font-black text-slate-800 uppercase mt-2 tracking-tighter">Xác thực GD</p>
                                                    </div>

                                                    <div class="relative z-10 flex flex-col items-center">
                                                        <div class="w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg shadow-red-200 border-2 border-white">
                                                            <i class="fas fa-hand-holding-usd text-[10px]"></i>
                                                        </div>
                                                        <p class="text-[8px] font-black text-slate-800 uppercase mt-2 tracking-tighter">Thành công</p>
                                                    </div>
                                                </div>
                                                <p class="text-[9px] text-red-400 font-bold italic mt-6 text-center relative z-10">* Tiền đã được hoàn lại ví MoMo thành công. Vui lòng kiểm tra số dư của bạn.</p>
                                            </div>
                                        <?php endif; ?>

                                        <div class="flex flex-wrap gap-x-10 gap-y-3 mb-5">
                                            <div class="flex items-center gap-2">
                                                <i class="far fa-calendar-alt text-[10px] text-blue-500"></i>
                                                <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Khởi hành:</span>
                                                <p class="text-[10px] font-bold text-slate-700 uppercase italic"><?= $b['departure_date'] ?: 'Đang cập nhật' ?></p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-users text-[10px] text-blue-500"></i>
                                                <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Hành khách:</span>
                                                <p class="text-[10px] font-bold text-slate-700 uppercase italic"><?= $b['num_adults'] ?> NL, <?= $b['num_children'] ?> TE</p>
                                            </div>
                                        </div>

                                        <?php if (in_array($b['status'], ['pending', 'confirmed', 'completed'])): ?>
                                            <div class="mt-4 pt-4 border-t border-slate-100 flex justify-end">
                                                <button onclick="requestCancel(<?= $b['id'] ?>, <?= (float)$b['total_price'] ?>, '<?= $b['status'] ?>', '<?= $b['departure_date'] ?>')" class="px-4 py-2 bg-red-50 text-red-500 rounded-xl border border-red-100 text-[9px] font-black uppercase tracking-widest hover:bg-red-500 hover:text-white hover:shadow-lg hover:shadow-red-200 transition-all duration-300 flex items-center active:scale-95">
                                                    <i class="fas fa-times-circle mr-1"></i> Yêu cầu hủy tour
                                                </button>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($b['status'] == 'completed'): ?>
                                            <div class="mb-5">
                                                <a href="export_invoice.php?id=<?= $b['id'] ?>" target="_blank" 
                                                   class="inline-flex items-center px-4 py-2 bg-slate-900 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-blue-600 hover:shadow-lg transition-all duration-300">
                                                    <i class="fas fa-file-invoice-dollar mr-2"></i> Xuất hóa đơn VAT
                                                </a>
                                            </div>

                                            <?php if ($b['rating']): ?>
                                                <div class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                                                    <div class="flex text-amber-400 gap-0.5 mb-2">
                                                        <?php for($i=1; $i<=5; $i++): ?>
                                                            <i class="<?= $i <= $b['rating'] ? 'fas' : 'far' ?> fa-star text-[9px]"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <p class="text-[10px] font-bold text-slate-600 italic leading-tight">"<?= htmlspecialchars($b['comment']) ?>"</p>
                                                </div>
                                            <?php else: ?>
                                                <form action="submit_review.php" method="POST" class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                                    <input type="hidden" name="tour_id" value="<?= $b['tour_id'] ?>">
                                                    <div class="flex flex-col gap-4">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-[9px] font-black text-slate-400 uppercase italic">Đánh giá:</span>
                                                                <span id="star-hint" class="text-[8px] font-black text-amber-500 uppercase tracking-widest transition-opacity duration-300"></span>
                                                            </div>
                                                            <div class="flex flex-row-reverse justify-end gap-1.5 text-slate-200 rating-stars">
                                                                <?php for($i=5; $i>=1; $i--): ?>
                                                                    <label class="cursor-pointer">
                                                                        <input type="radio" name="rating" value="<?= $i ?>" required class="hidden" onmouseover="updateStarHint(<?= $i ?>)" onmouseout="updateStarHint(0)">
                                                                        <i class="fas fa-star text-[11px] transition-colors"></i>
                                                                    </label>
                                                                <?php endfor; ?>
                                                            </div>
                                                        </div>
                                                        <div class="flex gap-2">
                                                            <input type="text" name="comment" placeholder="Cảm nhận của bạn..." required class="flex-1 px-4 py-2 bg-slate-50 rounded-xl text-[10px] font-bold outline-none focus:ring-1 focus:ring-blue-500">
                                                            <button type="submit" class="bg-slate-900 text-white px-4 py-2 rounded-xl text-[8px] font-black uppercase tracking-widest hover:bg-blue-600">Gửi</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Phân trang thông minh -->
                        <?= renderCompactPagination($page, ceil($total_bookings / $limit), 'tours') ?>
                    </div>
                <?php elseif ($tab == 'promos'):
                    $p_limit = 4; // Hiển thị 4 mã mỗi trang (2 hàng x 2 cột)
                    $p_offset = ($page - 1) * $p_limit;

                    $p_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM user_promos WHERE user_id = ?");
                    $p_count_stmt->execute([$user_id]);
                    $p_total_pages = ceil($p_count_stmt->fetchColumn() / $p_limit);

                    $promos_stmt = $pdo->prepare("SELECT p.*, up.is_used FROM user_promos up JOIN promos p ON up.promo_id = p.id WHERE up.user_id = ? ORDER BY up.is_used ASC, p.expiry_date ASC LIMIT $p_limit OFFSET $p_offset");
                    $promos_stmt->execute([$user_id]);
                    $promo_list = $promos_stmt->fetchAll();
                ?>
                    <div class="bg-white rounded-[3rem] p-8 md:p-12 shadow-xl border border-white">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                            <div>
                                <h3 class="text-2xl font-black italic uppercase text-slate-800 flex items-center tracking-tighter">
                                    <span class="w-10 h-1.5 bg-emerald-500 rounded-full mr-4"></span> Kho ưu đãi của bạn
                                </h3>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1 ml-14">Sử dụng mã để nhận ưu đãi đặc biệt khi đặt tour</p>
                            </div>
                            <div class="bg-slate-50 px-6 py-3 rounded-2xl border border-slate-100 flex items-center gap-3">
                                <i class="fas fa-ticket-alt text-emerald-500"></i>
                                <span class="text-xs font-black text-slate-600 uppercase tracking-tighter"><?= count($promo_list) ?> Mã giảm giá</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <?php if (empty($promo_list)): ?>
                                <div class="md:col-span-2 py-20 text-center bg-slate-50 rounded-[2.5rem] border border-dashed border-slate-200">
                                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm text-slate-200">
                                        <i class="fas fa-ticket-alt text-2xl"></i>
                                    </div>
                                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest italic">Bạn chưa có mã giảm giá nào trong kho</p>
                                    <a href="tours.php" class="inline-block mt-4 text-[10px] font-black text-blue-600 uppercase hover:underline">Săn mã ngay <i class="fas fa-arrow-right ml-1"></i></a>
                                </div>
                            <?php endif; ?>
                            
                            <?php foreach($promo_list as $p): 
                                $is_expired = $p['expiry_date'] && strtotime($p['expiry_date']) < time();
                                $is_inactive = $p['is_used'] || $is_expired;
                            ?>
                                <div class="relative flex h-32 rounded-3xl overflow-hidden border transition-all duration-300 <?= $is_inactive ? 'bg-slate-50 border-slate-100 opacity-60' : 'bg-white border-emerald-100 hover:shadow-xl hover:shadow-emerald-100/50 hover:-translate-y-1' ?>">
                                    <div class="w-24 md:w-32 flex flex-col items-center justify-center p-4 relative <?= $is_inactive ? 'bg-slate-200 text-slate-500' : 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white' ?>">
                                        <span class="text-2xl md:text-3xl font-black italic tracking-tighter leading-none">-<?= $p['percent'] ?>%</span>
                                        <span class="text-[8px] font-black uppercase tracking-widest opacity-80 mt-1">Ưu đãi</span>
                                        <div class="absolute -right-2 top-0 w-4 h-4 bg-[#f8fafc] rounded-full -mt-2"></div>
                                        <div class="absolute -right-2 bottom-0 w-4 h-4 bg-[#f8fafc] rounded-full -mb-2"></div>
                                        <div class="absolute right-0 top-4 bottom-4 w-px border-r border-dashed border-white/30"></div>
                                    </div>
                                    <div class="flex-1 p-5 flex flex-col justify-between relative bg-white">
                                        <div>
                                            <div class="flex justify-between items-start mb-1">
                                                <h4 class="text-xs font-black text-slate-800 uppercase italic tracking-tighter truncate pr-2"><?= htmlspecialchars($p['code']) ?></h4>
                                                <span class="text-[7px] font-black px-2 py-0.5 rounded uppercase flex-shrink-0 <?= $p['is_used'] ? 'bg-slate-100 text-slate-400' : ($is_expired ? 'bg-red-50 text-red-400' : 'bg-emerald-50 text-emerald-600') ?>">
                                                    <?= $p['is_used'] ? 'Đã dùng' : ($is_expired ? 'Hết hạn' : 'Sẵn sàng') ?>
                                                </span>
                                            </div>
                                            <p class="text-[9px] font-bold text-slate-400 line-clamp-2 leading-tight"><?= htmlspecialchars($p['description']) ?></p>
                                        </div>
                                        <div class="flex items-center justify-between pt-2 mt-auto">
                                            <span class="text-[8px] font-black text-slate-300 uppercase italic">
                                                <i class="far fa-calendar-alt mr-1"></i> <?= $p['expiry_date'] ? date('d/m/Y', strtotime($p['expiry_date'])) : 'Vô hạn' ?>
                                            </span>
                                            <?php if(!$is_inactive): ?>
                                                <button onclick="copyToClipboard('<?= $p['code'] ?>')" class="text-[9px] font-black text-blue-600 uppercase hover:text-slate-900 transition-colors flex items-center">Sao chép</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Phân trang thông minh -->
                        <?= renderCompactPagination($page, $p_total_pages, 'promos', 'emerald') ?>
                    </div>
                <?php elseif ($tab == 'wishlist'): ?>
                    <div class="bg-white rounded-[3rem] p-10 shadow-xl border border-white">
                        <h3 class="text-2xl font-black italic uppercase text-slate-800 mb-8 flex items-center tracking-tighter">
                            <span class="w-10 h-1.5 bg-red-500 rounded-full mr-4"></span> Tour bạn yêu thích
                        </h3>
                        
                        <?php if (empty($my_wishlist)): ?>
                            <div class="py-20 text-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-200">
                                    <i class="fas fa-heart text-2xl"></i>
                                </div>
                                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest italic">Bạn chưa lưu tour nào vào danh sách yêu thích</p>
                                <a href="tours.php" class="inline-block mt-4 text-[10px] font-black text-blue-600 uppercase hover:underline">Khám phá các tour hot <i class="fas fa-arrow-right ml-1"></i></a>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <?php foreach($my_wishlist as $wt): ?>
                                    <a href="tour-detail.php?id=<?= $wt['id'] ?>" class="group bg-slate-50/50 p-4 rounded-[2rem] border border-slate-100 hover:bg-white hover:shadow-xl transition-all flex gap-4">
                                        <div class="w-24 h-24 rounded-2xl overflow-hidden flex-shrink-0">
                                            <img src="assets/uploads/<?= $wt['image'] ?: 'default.jpg' ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                        </div>
                                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                                            <span class="text-[8px] font-black text-blue-500 uppercase tracking-widest mb-1"><?= $wt['cat_name'] ?></span>
                                            <h4 class="text-[11px] font-black text-slate-800 uppercase italic tracking-tighter truncate leading-tight mb-2"><?= htmlspecialchars($wt['title']) ?></h4>
                                            <div class="flex items-center justify-between mt-1">
                                                <p class="text-[10px] font-black text-blue-600"><?= number_format($wt['price_base'], 0, ',', '.') ?>đ</p>
                                                <button onclick="event.preventDefault(); toggleWishlistInProfile(<?= $wt['id'] ?>)" class="text-red-400 hover:text-red-600">
                                                    <i class="fas fa-heart-broken text-xs"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Hàm xử lý phân trang gọn (1 2 ... 10)
function renderCompactPagination($current, $total, $tab, $color = 'blue') {
    if ($total <= 1) return '';
    $html = '<div class="flex justify-center items-center gap-2 mt-12">';
    $range = 1; 
    for ($i = 1; $i <= $total; $i++) {
        if ($i == 1 || $i == $total || ($i >= $current - $range && $i <= $current + $range)) {
            $activeClass = $i == $current ? "bg-$color-600 text-white shadow-lg" : "bg-slate-100 text-slate-400 hover:bg-slate-200";
            $html .= "<a href='?tab=$tab&page=$i' class='w-10 h-10 flex items-center justify-center rounded-xl font-black text-[10px] transition-all $activeClass'>$i</a>";
        } elseif ($i == $current - $range - 1 || $i == $current + $range + 1) {
            $html .= '<span class="text-slate-300 font-black">...</span>';
        }
    }
    $html .= '</div>';
    return $html;
}
?>

<script>
// Hàm đóng/mở chi tiết vé
function toggleDetails(id) {
    const el = document.getElementById('details-' + id);
    const icon = document.getElementById('icon-' + id);
    if (el.classList.contains('hidden')) {
        el.classList.remove('hidden');
        icon.classList.add('rotate-180');
        el.style.animation = 'fadeIn 0.3s ease-out';
    } else {
        el.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: `<span class="text-xs font-black uppercase">Đã sao chép mã: ${text}</span>`,
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            customClass: { popup: 'swal2-toast-small rounded-2xl border border-slate-100 shadow-2xl' }
        });
    });
}

function toggleWishlistInProfile(tourId) {
    const formData = new FormData();
    formData.append('tour_id', tourId);

    fetch('ajax_wishlist.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            window.location.reload();
        } else {
            Swal.fire({ icon: 'error', title: 'Lỗi', text: data.message });
        }
    });
}

function requestCancel(id, amount, status, depDateStr) {
    // Tự động tính toán số tiền hoàn trả dự kiến
    let refundValue = 0;
    let note = "Tour chưa thực hiện thanh toán";
    let refundPercent = 100;

    // Tính số ngày còn lại đến ngày khởi hành (định dạng DD/MM)
    if (status === 'completed' || status === 'confirmed') {
        const parts = depDateStr.split('/');
        if (parts.length === 2) {
            const day = parseInt(parts[0]);
            const month = parseInt(parts[1]) - 1;
            const now = new Date();
            let depDate = new Date(now.getFullYear(), month, day);
            
            // Xử lý nếu ngày khởi hành thuộc năm sau
            if (depDate < now && (now - depDate) > 30 * 24 * 60 * 60 * 1000) {
                depDate.setFullYear(now.getFullYear() + 1);
            }

            const diffTime = depDate - now;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays >= 2) {
                refundPercent = 100;
                note = "Hủy trước 2 ngày: Hoàn trả 100% số tiền đã thanh toán";
            } else if (diffDays >= 0) {
                refundPercent = 50;
                note = "Hủy trong vòng 2 ngày: Phí hủy 50%, hoàn trả 50% số tiền đã thanh toán";
            } else {
                refundPercent = 0;
                note = "Tour đã khởi hành, không thể hoàn tiền qua hệ thống";
            }
        }
    }
    
    if (status === 'completed' || status === 'confirmed') {
        refundValue = amount * (refundPercent / 100);
    }

    const refundFormatted = refundValue.toLocaleString('vi-VN') + 'đ';

    Swal.fire({
        title: '<span class="text-sm font-black uppercase italic">Xác nhận hủy tour?</span>',
        html: `
            <div class="text-center">
                <p class="text-[11px] font-bold text-slate-500 mb-4 uppercase tracking-tighter">Bạn chắc chắn muốn hủy hành trình này?</p>
                <div class="p-5 bg-red-50 rounded-[1.8rem] border border-red-100 shadow-inner">
                    <p class="text-[8px] font-black text-red-400 uppercase tracking-widest mb-1">Số tiền hoàn trả dự kiến</p>
                    <p class="text-3xl font-black text-red-600 tracking-tighter">${refundFormatted}</p>
                    <p class="text-[9px] text-red-400 font-bold uppercase mt-2 italic">* ${note}</p>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'ĐÚNG, HỦY NGAY',
        cancelButtonText: 'QUAY LẠI',
        customClass: { popup: 'rounded-[2rem] border-0 shadow-2xl' }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('booking_id', id);

            fetch('ajax_cancel_booking.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '<span class="text-sm font-black uppercase italic">Thành công</span>',
                        text: data.message,
                        customClass: { popup: 'rounded-[2rem]' }
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '<span class="text-sm font-black uppercase italic">Lỗi</span>',
                        text: data.message,
                        customClass: { popup: 'rounded-[2rem]' }
                    });
                }
            });
        }
    });
}

// Tự động mở chi tiết đơn hàng nếu có tham số booking_id trong URL (đến từ Mail)
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const bookingId = urlParams.get('booking_id');
    if (bookingId) {
        const detailsEl = document.getElementById('details-' + bookingId);
        if (detailsEl) {
            toggleDetails(bookingId);
            detailsEl.parentElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
});

function updateStarHint(rating) {
    const hintEl = document.getElementById('star-hint');
    const hints = {
        0: '',
        1: 'Rất tệ',
        2: 'Không hài lòng',
        3: 'Bình thường',
        4: 'Hài lòng',
        5: 'Tuyệt vời'
    };
    
    if (rating === 0) {
        const checked = document.querySelector('input[name="rating"]:checked');
        hintEl.innerText = checked ? hints[checked.value] : '';
    } else {
        hintEl.innerText = hints[rating];
    }
}
</script>

<?php include 'footer.php'; ?>