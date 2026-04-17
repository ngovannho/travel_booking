<?php
require_once 'config.php';
include 'header.php';

$resultCode = $_GET['resultCode'] ?? -1;
$orderId = $_GET['orderId'] ?? '';
$amount = $_GET['amount'] ?? 0;
$isSuccess = ($resultCode == 0);

// Tách lấy booking_id từ orderId (format: id_timestamp)
$booking_id = explode('_', $orderId)[0];
?>

<main class="bg-[#f8fafc] min-h-[90vh] flex items-center justify-center py-20 px-4">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-[3.5rem] shadow-2xl shadow-slate-200/50 border border-white overflow-hidden relative">
            
            <!-- Header Decor -->
            <div class="h-4 w-full flex">
                <div class="h-full flex-1 bg-blue-600"></div>
                <div class="h-full flex-1 bg-yellow-500"></div>
                <div class="h-full flex-1 bg-blue-600"></div>
            </div>

            <div class="p-10 md:p-16 text-center">
                <?php if ($isSuccess): ?>
                    <!-- Success State -->
                    <div class="w-24 h-24 bg-emerald-50 text-emerald-500 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-inner animate-bounce">
                        <i class="fas fa-check-double text-4xl"></i>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-black italic text-slate-900 uppercase tracking-tighter mb-4">Thanh toán thành công!</h1>
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-[0.2em] mb-10 leading-relaxed">Hành trình di sản của bạn đã sẵn sàng bắt đầu.</p>
                    
                    <div class="bg-slate-50 rounded-[2.5rem] p-8 mb-10 border border-slate-100 flex flex-col md:flex-row gap-6 justify-between items-center">
                        <div class="text-left">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Mã đơn hàng</p>
                            <p class="text-lg font-black text-slate-800 tracking-tighter italic">#<?= htmlspecialchars($booking_id) ?></p>
                        </div>
                        <div class="h-10 w-px bg-slate-200 hidden md:block"></div>
                        <div class="text-center md:text-right">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tổng thanh toán</p>
                            <p class="text-2xl font-black text-blue-600 tracking-tighter"><?= number_format($amount, 0, ',', '.') ?>đ</p>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Error State -->
                    <div class="w-24 h-24 bg-red-50 text-red-500 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-inner">
                        <i class="fas fa-exclamation-triangle text-4xl"></i>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-black italic text-slate-900 uppercase tracking-tighter mb-4">Giao dịch thất bại</h1>
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-[0.2em] mb-10 leading-relaxed">Đã có lỗi xảy ra hoặc bạn đã hủy thanh toán.</p>
                <?php endif; ?>

                <!-- Nút Đặt Tour Mới & CTAs -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="tours.php" class="bg-blue-600 text-white py-5 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl shadow-blue-200 hover:bg-slate-900 transition-all transform hover:-translate-y-1 flex items-center justify-center">
                        <i class="fas fa-search-location mr-3"></i> Khám phá tour khác
                    </a>
                    <a href="profile.php?tab=tours" class="bg-slate-100 text-slate-600 py-5 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-slate-200 transition-all flex items-center justify-center">
                        <i class="fas fa-ticket-alt mr-3"></i> Xem đơn hàng
                    </a>
                </div>
            </div>

            <!-- Footer Decor -->
            <div class="bg-slate-50 p-6 text-center border-t border-slate-100">
                <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.3em]">Cảm ơn bạn đã lựa chọn Lily Travel</p>
            </div>
        </div>
    </div>
</main>

<?php if ($isSuccess): ?>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
<script>
    // Hiệu ứng pháo giấy khi thành công
    var duration = 3 * 1000;
    var end = Date.now() + duration;

    (function frame() {
      confetti({ particleCount: 3, angle: 60, spread: 55, origin: { x: 0 }, colors: ['#2563eb', '#fbbf24'] });
      confetti({ particleCount: 3, angle: 120, spread: 55, origin: { x: 1 }, colors: ['#2563eb', '#fbbf24'] });

      if (Date.now() < end) {
        requestAnimationFrame(frame);
      }
    }());
</script>
<?php endif; ?>

<?php include 'footer.php'; ?>