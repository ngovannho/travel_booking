<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    exit('Vui lòng đăng nhập.');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user']['id'];

// Lấy thông tin đơn hàng và tour
$stmt = $pdo->prepare("
    SELECT b.*, t.title, t.duration, t.departure_location 
    FROM bookings b 
    JOIN tours t ON b.tour_id = t.id 
    WHERE b.id = ? AND b.user_id = ? AND b.status = 'completed'
");
$stmt->execute([$id, $user_id]);
$booking = $stmt->fetch();

if (!$booking) {
    exit('Đơn hàng không hợp lệ hoặc chưa thanh toán 100%.');
}

// Tính toán thuế VAT (giả sử giá niêm yết đã bao gồm 10% VAT)
$total = (float)$booking['total_price'];
$vat_rate = 0.1;
$before_vat = $total / (1 + $vat_rate);
$vat_amount = $total - $before_vat;

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn VAT - Lily Travel #<?= $id ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; padding: 0; }
            .invoice-box { box-shadow: none; border: none; border-radius: 0; padding: 0; }
        }
        .font-black-italic { font-weight: 900; font-style: italic; }
    </style>
</head>
<body class="bg-slate-50 p-4 md:p-10">
    <div class="invoice-box max-w-4xl mx-auto bg-white p-10 md:p-16 rounded-[3rem] shadow-2xl border border-slate-100 relative overflow-hidden">
        
        <!-- Header Công ty -->
        <div class="flex flex-col md:flex-row justify-between items-start gap-8 mb-16 border-b border-dashed border-slate-200 pb-12">
            <div>
                <h1 class="text-3xl font-black-italic text-blue-600 uppercase tracking-tighter mb-4">
                    <i class="fas fa-globe-asia mr-2"></i>LILY-<span class="text-yellow-500">TRAVEL</span>
                </h1>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest space-y-1">
                    <p>CÔNG TY TNHH LILY TRAVEL VIỆT NAM</p>
                    <p>MST: 14122003</p>
                    <p>Địa chỉ: 126 Huỳnh Tấn Phát, Hải Châu, Đà Nẵng</p>
                    <p>Hotline: 0777454550</p>
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-4xl font-black italic text-slate-900 uppercase tracking-tighter mb-2">Hóa đơn GTGT</h2>
                <p class="text-xs font-black text-blue-600 uppercase tracking-widest">Mẫu số: 01GTKT0/001</p>
                <p class="text-xs font-bold text-slate-400 mt-1 uppercase">Ký hiệu: LT/26P</p>
                <p class="text-xs font-bold text-slate-400 mt-1">Số: <span class="text-red-500 font-black"><?= str_pad($id, 7, '0', STR_PAD_LEFT) ?></span></p>
                <p class="text-[10px] font-bold text-slate-400 uppercase mt-4 italic">Ngày lập: <?= date('d/m/Y', strtotime($booking['booking_date'])) ?></p>
            </div>
        </div>

        <!-- Thông tin Khách hàng -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-16 bg-slate-50 p-8 rounded-[2rem] border border-slate-100">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 italic">Đơn vị mua hàng</p>
                <p class="text-sm font-black text-slate-800 uppercase"><?= htmlspecialchars($booking['customer_name']) ?></p>
                <p class="text-xs font-bold text-slate-500 mt-2">SĐT: <?= $booking['customer_phone'] ?></p>
                <p class="text-xs font-bold text-slate-500 mt-1">Email: <?= $booking['customer_email'] ?></p>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 italic">Hình thức thanh toán</p>
                <p class="text-sm font-black text-blue-600 uppercase">Chuyển khoản (MoMo)</p>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase italic">Mã GD tham chiếu:</p>
                <p class="text-xs font-mono font-bold text-slate-600"><?= $booking['momo_trans_id'] ?: 'N/A' ?></p>
            </div>
        </div>

        <!-- Bảng kê dịch vụ -->
        <table class="w-full mb-16">
            <thead>
                <tr class="text-[10px] font-black uppercase tracking-widest text-slate-400 border-b-2 border-slate-900 pb-4">
                    <th class="text-left py-4">Nội dung chuyến đi</th>
                    <th class="text-center py-4">Số lượng</th>
                    <th class="text-right py-4">Đơn giá (Gồm VAT)</th>
                    <th class="text-right py-4">Thành tiền</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr class="text-slate-700">
                    <td class="py-8">
                        <p class="text-sm font-black uppercase italic tracking-tighter text-slate-800"><?= htmlspecialchars($booking['title']) ?></p>
                        <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest italic">
                            Khởi hành: <?= $booking['departure_date'] ?> | <?= $booking['duration'] ?>
                        </p>
                    </td>
                    <td class="text-center py-8">
                        <p class="text-xs font-black"><?= $booking['num_adults'] + $booking['num_children'] + $booking['num_infants'] ?></p>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">Hành khách</p>
                    </td>
                    <td class="text-right py-8 text-xs font-bold"><?= number_format($total, 0, ',', '.') ?>đ</td>
                    <td class="text-right py-8 text-sm font-black text-slate-900"><?= number_format($total, 0, ',', '.') ?>đ</td>
                </tr>
            </tbody>
        </table>

        <!-- Tổng cộng -->
        <div class="flex justify-end mb-16">
            <div class="w-full md:w-1/2 space-y-4">
                <div class="flex justify-between text-xs font-bold text-slate-500 uppercase tracking-widest">
                    <span>Giá trị dịch vụ trước thuế:</span>
                    <span><?= number_format($before_vat, 0, ',', '.') ?>đ</span>
                </div>
                <div class="flex justify-between text-xs font-bold text-slate-500 uppercase tracking-widest">
                    <span>Thuế suất GTGT (10%):</span>
                    <span><?= number_format($vat_amount, 0, ',', '.') ?>đ</span>
                </div>
                <div class="pt-4 border-t border-slate-200 flex justify-between items-center">
                    <span class="text-xs font-black text-slate-900 uppercase italic tracking-widest">Tổng tiền thanh toán:</span>
                    <span class="text-2xl font-black text-blue-600 tracking-tighter"><?= number_format($total, 0, ',', '.') ?>đ</span>
                </div>
            </div>
        </div>

        <!-- Nút thao tác -->
        <div class="no-print flex justify-center gap-4 mt-10">
            <button onclick="window.print()" class="px-8 py-4 bg-slate-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-600 shadow-xl transition-all">
                <i class="fas fa-print mr-2"></i> In hóa đơn điện tử
            </button>
            <button onclick="window.close()" class="px-8 py-4 bg-slate-100 text-slate-400 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-red-50 hover:text-red-500 transition-all">
                Đóng
            </button>
        </div>
    </div>
</body>
</html>