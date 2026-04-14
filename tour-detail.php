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

$stmt_reviews = $pdo->prepare("SELECT r.*, u.fullname FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.tour_id = ? ORDER BY r.created_at DESC");
$stmt_reviews->execute([$id]);
$reviews = $stmt_reviews->fetchAll();
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
                    <h1 class="text-4xl md:text-6xl font-black-italic text-slate-900 leading-[1.1] uppercase tracking-tighter mb-6">
                        <?= htmlspecialchars($tour['title']) ?>
                    </h1>
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
                                <!-- CHỈ MỞ LẠI form đặt tour khi không có đơn hàng HOẶC đơn hàng gần nhất đã hoàn thành -->
                                <?php if ($user_booking && trim($user_booking['status']) == 'completed'): ?>
                                    <div class="mb-6 p-5 bg-emerald-50 rounded-3xl border border-emerald-100 flex items-center gap-3">
                                        <i class="fas fa-check-circle text-emerald-500"></i>
                                        <p class="text-[10px] font-black text-emerald-800 uppercase italic">Thanh toán hoàn tất! Bạn có thể đặt thêm tour mới.</p>
                                    </div>
                                <?php endif; ?>

                                <form id="bookingForm" action="booking_process.php" method="POST" class="space-y-5">
                                    <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
                                    <input type="hidden" name="total_price" id="final_price_input" value="0">
                                    
                                    <div class="space-y-3">
                                        <div class="space-y-1">
                                            <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Ngày khởi hành</label>
                                            <select name="departure_date" required class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500">
                                                <?php 
                                                $dates = explode(',', $tour['departure_dates'] ?? '');
                                                foreach($dates as $date): $date = trim($date); if(!$date) continue;
                                                ?>
                                                    <option value="<?= htmlspecialchars($date) ?>"><?= htmlspecialchars($date) ?></option>
                                                <?php endforeach; ?>
                                            </select>
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
                                        <button type="button" onclick="handleBooking()" class="w-full bg-slate-900 text-white py-5 rounded-[1.8rem] font-black uppercase text-[11px] tracking-[0.2em] shadow-2xl shadow-slate-300 hover:bg-blue-600 transition-all active:scale-95 flex items-center justify-center">
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
        Swal.fire({
            title: 'Chuyển sang thanh toán MoMo',
            text: 'Bạn sẽ được chuyển hướng tới cổng thanh toán an toàn của MoMo.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'THANH TOÁN NGAY',
            confirmButtonColor: '#a50064'
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

    // Chạy khi DOM sẵn sàng
    document.addEventListener('DOMContentLoaded', initSliders);
</script>

<?php include 'footer.php'; ?>