<?php
require_once 'config.php';
include 'header.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
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
$limit = 3;
$offset = ($page - 1) * $limit;

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
$count_stmt->execute([$user_id]);
$total_bookings = $count_stmt->fetchColumn();
$total_pages = ceil($total_bookings / $limit);

$bookings = $pdo->prepare("SELECT b.*, t.title, t.image, r.rating, r.comment 
                           FROM bookings b 
                           JOIN tours t ON b.tour_id = t.id 
                           LEFT JOIN reviews r ON b.id = r.booking_id 
                           WHERE b.user_id = ? 
                           ORDER BY b.id DESC LIMIT $limit OFFSET $offset");
$bookings->execute([$user_id]);
$my_bookings = $bookings->fetchAll();
?>

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

                    <nav class="mt-10 space-y-2">
                        <a href="?tab=info" class="flex items-center justify-between p-4 rounded-2xl transition-all <?= $tab == 'info' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-slate-50 text-slate-400 hover:bg-slate-100' ?>">
                            <div class="flex items-center">
                                <i class="fas fa-user-circle w-8"></i>
                                <span class="text-xs font-black uppercase tracking-widest">Thông tin cá nhân</span>
                            </div>
                            <i class="fas fa-chevron-right text-[10px]"></i>
                        </a>
                        <a href="?tab=tours" class="flex items-center justify-between p-4 rounded-2xl transition-all <?= $tab == 'tours' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-slate-50 text-slate-400 hover:bg-slate-100' ?>">
                            <div class="flex items-center">
                                <i class="fas fa-map-marked-alt w-8"></i>
                                <span class="text-xs font-black uppercase tracking-widest">Lịch sử chuyến đi</span>
                            </div>
                            <i class="fas fa-chevron-right text-[10px]"></i>
                        </a>
                        <a href="?tab=promos" class="flex items-center justify-between p-4 rounded-2xl transition-all <?= $tab == 'promos' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-slate-50 text-slate-400 hover:bg-slate-100' ?>">
                            <div class="flex items-center">
                                <i class="fas fa-ticket-alt w-8"></i>
                                <span class="text-xs font-black uppercase tracking-widest">Mã giảm giá</span>
                            </div>
                            <i class="fas fa-chevron-right text-[10px]"></i>
                        </a>
                        <a href="logout.php" class="flex items-center p-4 rounded-2xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all">
                            <i class="fas fa-sign-out-alt w-8"></i>
                            <span class="text-xs font-black uppercase tracking-widest">Đăng xuất</span>
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
                        <div class="space-y-4">
                            <?php if (empty($my_bookings)): ?>
                                <p class="text-center text-slate-400 py-10 font-bold uppercase text-[10px] tracking-widest italic">Bạn chưa thực hiện chuyến đi nào</p>
                            <?php endif; ?>

                            <?php foreach($my_bookings as $b): ?>
                                <div class="flex flex-col md:flex-row items-center gap-6 p-5 bg-slate-50 rounded-[2rem] border border-slate-100 hover:shadow-lg transition-all group">
                                    <img src="assets/uploads/<?= $b['image'] ?: 'default.jpg' ?>" class="w-24 h-24 rounded-2xl object-cover shadow-sm">
                                    <div class="flex-1 text-center md:text-left">
                                        <p class="text-[9px] font-black text-blue-600 uppercase tracking-widest mb-1"><?= date('d/m/Y', strtotime($b['booking_date'])) ?></p>
                                        <h4 class="text-sm font-black text-slate-800 uppercase italic tracking-tighter leading-none mb-2"><?= htmlspecialchars($b['title']) ?></h4>
                                        <p class="text-sm font-black text-slate-900"><?= number_format($b['total_price'], 0, ',', '.') ?>đ</p>
                                    </div>
                                    <div class="text-right">
                                        <?php if ($b['status'] == 'pending'): ?>
                                            <span class="inline-block px-4 py-2 bg-amber-100 text-amber-600 rounded-xl text-[9px] font-black uppercase tracking-widest">Đang chờ xác nhận</span>
                                        <?php elseif ($b['status'] == 'balance_pending'): ?>
                                            <span class="inline-block px-4 py-2 bg-amber-100 text-amber-600 rounded-xl text-[9px] font-black uppercase tracking-widest">Chờ xác nhận thanh toán</span>
                                        <?php elseif ($b['status'] == 'confirmed'): ?>
                                            <span class="inline-block px-4 py-2 bg-emerald-100 text-emerald-600 rounded-xl text-[9px] font-black uppercase tracking-widest">Đã xác nhận</span>
                                        <?php elseif ($b['status'] == 'completed'): ?>
                                            <span class="inline-block px-4 py-2 bg-blue-100 text-blue-600 rounded-xl text-[9px] font-black uppercase tracking-widest">Hoàn thành</span>
                                        <?php else: ?>
                                            <span class="inline-block px-4 py-2 bg-slate-200 text-slate-500 rounded-xl text-[9px] font-black uppercase tracking-widest">Đã hủy</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($b['status'] == 'completed'): ?>
                                    <div class="mt-4 p-6 bg-white rounded-2xl border border-dashed border-slate-200">
                                        <?php if ($b['rating']): ?>
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Đánh giá của bạn</p>
                                                    <div class="flex text-amber-400 gap-1 mb-2">
                                                        <?php for($i=1; $i<=5; $i++): ?>
                                                            <i class="<?= $i <= $b['rating'] ? 'fas' : 'far' ?> fa-star text-xs"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <p class="text-xs italic text-slate-600">"<?= htmlspecialchars($b['comment']) ?>"</p>
                                                </div>
                                                <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
                                            </div>
                                        <?php else: ?>
                                            <form action="submit_review.php" method="POST" class="space-y-4">
                                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                                <input type="hidden" name="tour_id" value="<?= $b['tour_id'] ?>">
                                                <div class="flex items-center gap-4">
                                                    <p class="text-[10px] font-black text-slate-400 uppercase">Đánh giá tour:</p>
                                                    <div class="flex gap-2 text-slate-300">
                                                        <?php for($i=1; $i<=5; $i++): ?>
                                                            <label class="cursor-pointer hover:text-amber-400 transition-colors">
                                                                <input type="radio" name="rating" value="<?= $i ?>" required class="hidden peer">
                                                                <i class="fas fa-star text-sm peer-checked:text-amber-400"></i>
                                                            </label>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                                <div class="flex gap-3">
                                                    <input type="text" name="comment" placeholder="Góp ý của bạn về chuyến đi..." required class="flex-1 p-3 bg-slate-50 border-0 rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500">
                                                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-xl text-[10px] font-black uppercase hover:bg-blue-700 transition-all">Gửi</button>
                                                </div>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($total_pages > 1): ?>
                        <div class="flex justify-center gap-2 mt-8">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?tab=tours&page=<?= $i ?>" class="w-10 h-10 flex items-center justify-center rounded-xl font-black text-xs transition-all <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-400 hover:bg-slate-200' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($tab == 'promos'): 
                    $promos_stmt = $pdo->prepare("SELECT p.*, up.is_used FROM user_promos up JOIN promos p ON up.promo_id = p.id WHERE up.user_id = ? ORDER BY up.is_used ASC, p.expiry_date ASC");
                    $promos_stmt->execute([$user_id]);
                    $promo_list = $promos_stmt->fetchAll();
                ?>
                    <div class="bg-white rounded-[3rem] p-10 shadow-xl border border-white">
                        <h3 class="text-2xl font-black italic uppercase text-slate-800 mb-8 flex items-center tracking-tighter">
                            <span class="w-10 h-1.5 bg-emerald-500 rounded-full mr-4"></span> Kho mã giảm giá
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php if (empty($promo_list)): ?>
                                <div class="md:col-span-2 text-center text-slate-400 py-10 font-bold uppercase text-[10px] tracking-widest italic">Bạn chưa có mã giảm giá nào</div>
                            <?php endif; ?>
                            
                            <?php foreach($promo_list as $p): ?>
                                <div class="relative p-6 rounded-[2rem] border <?= $p['is_used'] ? 'bg-slate-50 border-slate-100 grayscale' : 'bg-emerald-50 border-emerald-100' ?> overflow-hidden group">
                                    <div class="relative z-10">
                                        <div class="flex justify-between items-start mb-4">
                                            <span class="px-3 py-1 bg-white rounded-lg text-[10px] font-black <?= $p['is_used'] ? 'text-slate-400' : 'text-emerald-600' ?> shadow-sm"><?= $p['code'] ?></span>
                                            <?php if($p['is_used']): ?>
                                                <span class="text-[9px] font-black text-slate-400 uppercase italic">Đã sử dụng</span>
                                            <?php else: ?>
                                                <span class="text-[9px] font-black text-emerald-600 uppercase italic">Sẵn sàng</span>
                                            <?php endif; ?>
                                        </div>
                                        <h4 class="text-lg font-black text-slate-800 tracking-tighter leading-none mb-2 italic">Giảm <?= $p['percent'] ?>%</h4>
                                        <p class="text-[10px] font-bold text-slate-500 mb-4"><?= htmlspecialchars($p['description']) ?></p>
                                        <p class="text-[9px] font-black text-slate-400 uppercase">Hết hạn: <?= $p['expiry_date'] ? date('d/m/Y', strtotime($p['expiry_date'])) : 'Không giới hạn' ?></p>
                                    </div>
                                    <i class="fas fa-ticket-alt absolute -bottom-4 -right-4 text-6xl <?= $p['is_used'] ? 'text-slate-200' : 'text-emerald-200' ?> -rotate-12 opacity-50"></i>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Kiểm tra kết quả trả về từ MoMo
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
                // Tự động chuyển sang tab lịch sử chuyến đi và làm sạch URL để tránh hiện lại khi F5
                window.history.replaceState({}, document.title, window.location.pathname + "?tab=tours");
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