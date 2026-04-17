<?php
require_once 'config.php';
include 'header.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$user_id = $_SESSION['user']['id'];

// Lấy thông tin đơn hàng vừa đặt
$stmt = $pdo->prepare("SELECT b.*, t.title FROM bookings b JOIN tours t ON b.tour_id = t.id WHERE b.id = ? AND b.user_id = ?");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch();

if (!$booking) {
    exit('Đơn hàng không tồn tại.');
}
?>

<main class="bg-slate-50 min-h-screen py-20 px-4">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-[3.5rem] shadow-2xl border border-white overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-700">
            
            <!-- Header -->
            <div class="bg-slate-900 p-10 text-center relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-2xl font-black text-white uppercase italic tracking-tighter mb-2">Thanh toán chuyển khoản</h2>
                    <p class="text-[10px] font-black text-blue-400 uppercase tracking-[0.3em]">Mã đơn hàng: #<?= $booking['id'] ?></p>
                </div>
                <i class="fas fa-university absolute -bottom-4 -right-4 text-8xl text-white/5 -rotate-12"></i>
            </div>

            <div class="p-10 md:p-14">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                    <!-- Khối mã QR VietQR -->
                    <div class="text-center space-y-4">
                        <div class="bg-slate-50 p-5 rounded-[2.5rem] border-2 border-dashed border-slate-200 relative group">
                            <!-- Tự động tạo QR với VietQR API -->
                            <img src="https://img.vietqr.io/image/MB-0777454550-compact2.png?amount=<?= (int)$booking['total_price'] ?>&addInfo=LILY TRAVEL #<?= $booking['id'] ?>&accountName=NGO VAN NHO" 
                                 alt="QR Thanh toán" class="w-full aspect-square object-contain rounded-2xl shadow-sm">
                            
                            <div class="absolute inset-0 bg-blue-600/5 opacity-0 group-hover:opacity-100 transition-opacity rounded-[2.5rem] flex items-center justify-center pointer-events-none">
                                <i class="fas fa-qrcode text-blue-600 text-3xl animate-pulse"></i>
                            </div>
                        </div>
                        <div class="flex flex-col items-center">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic mb-2">Mở ứng dụng Ngân hàng để quét</span>
                            <div class="flex gap-2">
                                <img src="https://vietqr.net/portal-v2/images/img/Logo-VietQR.png" class="h-4">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/25/Logo_MB_Bank.svg/1200px-Logo_MB_Bank.svg.png" class="h-4">
                            </div>
                        </div>
                    </div>

                    <!-- Khối thông tin chi tiết -->
                    <div class="space-y-6">
                        <div class="p-6 bg-blue-50 rounded-[2rem] border border-blue-100 shadow-inner">
                            <h5 class="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-5 flex items-center">
                                <i class="fas fa-receipt mr-2"></i> Thông tin giao dịch
                            </h5>
                            <div class="space-y-4">
                                <div class="flex justify-between border-b border-blue-100 pb-2 items-center">
                                    <span class="text-[9px] font-black text-slate-400 uppercase">Số tài khoản</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-black text-blue-600">0777454550</span>
                                        <button onclick="copyToClipboard('0777454550')" class="text-slate-300 hover:text-blue-600 transition-colors" title="Sao chép số tài khoản">
                                            <i class="far fa-copy text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex justify-between border-b border-blue-100 pb-2">
                                    <span class="text-[9px] font-black text-slate-400 uppercase">Chủ tài khoản</span>
                                    <span class="text-[11px] font-black text-slate-800 uppercase">NGO VAN NHO</span>
                                </div>
                                <div class="flex justify-between border-b border-blue-100 pb-2 items-center">
                                    <span class="text-[9px] font-black text-slate-400 uppercase">Số tiền</span>
                                    <span class="text-[13px] font-black text-slate-900"><?= number_format($booking['total_price'], 0, ',', '.') ?>đ</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[9px] font-black text-slate-400 uppercase">Nội dung</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] font-black text-red-500 uppercase italic">LILY TRAVEL #<?= $booking['id'] ?></span>
                                        <button onclick="copyToClipboard('LILY TRAVEL #<?= $booking['id'] ?>')" class="text-slate-300 hover:text-blue-600 transition-colors" title="Sao chép nội dung">
                                            <i class="far fa-copy text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-[9px] text-slate-400 font-bold leading-relaxed italic text-center">
                            * Hệ thống sẽ tự động cập nhật trạng thái sau khi xác nhận chuyển khoản thành công.
                        </p>
                    </div>
                </div>

                <div class="mt-12 pt-8 border-t border-dashed border-slate-100 flex flex-col items-center">
                    <button onclick="confirmDone()" class="w-full sm:w-auto px-12 py-5 bg-slate-900 text-white rounded-[1.8rem] font-black text-[11px] uppercase tracking-widest shadow-2xl hover:bg-blue-600 transition-all transform hover:-translate-y-1 active:scale-95">
                        Tôi đã thực hiện thanh toán thành công
                    </button>
                    <a href="profile.php?tab=tours" class="mt-6 text-[10px] font-black text-slate-300 uppercase hover:text-slate-500 transition-colors">Để sau, quay lại trang cá nhân</a>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            customClass: { popup: 'swal2-toast-small rounded-2xl border border-slate-100 shadow-2xl' }
        });
        Toast.fire({
            icon: 'success',
            title: '<span class="text-[10px] font-black uppercase tracking-widest italic">Đã sao chép vào bộ nhớ!</span>'
        });
    });
}

function confirmDone() {
    Swal.fire({
        title: '<span class="text-sm font-black uppercase italic tracking-widest">Đã ghi nhận!</span>',
        html: '<p class="text-xs font-bold text-slate-500 uppercase leading-relaxed">Cảm ơn bạn. Lily Travel sẽ kiểm tra và xác nhận đơn hàng của bạn trong giây lát.</p>',
        icon: 'success',
        confirmButtonColor: '#0f172a',
        confirmButtonText: 'ĐỒNG Ý',
        customClass: { popup: 'rounded-[3rem]', confirmButton: 'rounded-xl px-12 py-4 font-black uppercase text-[10px]' }
    }).then(() => {
        window.location.href = 'profile.php?tab=tours&booking_id=<?= $booking_id ?>';
    });
}
</script>

<?php include 'footer.php'; ?>