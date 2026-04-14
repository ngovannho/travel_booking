<?php 
require_once '../config.php';
include 'header.php'; 

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$count_stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT b.*, t.title as tour_title, t.image as tour_image, r.rating, r.comment 
        FROM bookings b 
        JOIN tours t ON b.tour_id = t.id 
        LEFT JOIN reviews r ON b.id = r.booking_id 
        ORDER BY b.id DESC LIMIT $limit OFFSET $offset";
$bookings = $pdo->query($sql)->fetchAll();
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h3 class="text-2xl font-black text-slate-800 uppercase italic tracking-tighter">Quản lý Đơn hàng</h3>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Xử lý thanh toán và xác nhận tour</p>
    </div>
    <a href="tour_reviews.php" class="bg-amber-500 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-amber-200 hover:bg-amber-600 transition-all text-center">
        <i class="fas fa-star mr-2"></i> Tổng hợp đánh giá
    </a>
</div>

<div class="block lg:hidden space-y-4">
    <?php foreach($bookings as $b): ?>
    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
        <div class="flex gap-4 mb-4">
            <img src="../assets/uploads/<?= $b['tour_image'] ?>" class="w-16 h-16 rounded-2xl object-cover">
            <div class="flex-1 min-w-0">
                <div class="flex justify-between items-start">
                    <div class="text-[10px] font-black text-blue-600 uppercase mb-1">ID: #<?= $b['id'] ?></div>
                    <div class="text-[9px] font-bold text-slate-400 uppercase italic"><?= date('d/m/Y H:i', strtotime($b['booking_date'])) ?></div>
                </div>
                <h4 class="text-sm font-bold text-slate-800 truncate uppercase tracking-tighter"><?= htmlspecialchars($b['tour_title']) ?></h4>
                <p class="text-xs font-black text-slate-900 mt-1"><?= number_format($b['total_price'], 0, ',', '.') ?>đ</p>
            </div>
        </div>
        <div class="mb-4 space-y-1 bg-slate-50/50 p-4 rounded-2xl border border-slate-50">
            <div class="text-xs font-black text-slate-700 uppercase tracking-tighter"><?= htmlspecialchars($b['customer_name']) ?></div>
            <div class="text-[10px] font-bold text-slate-400"><?= $b['customer_phone'] ?> | <?= $b['customer_email'] ?></div>
            <div class="text-[10px] font-black text-amber-600 uppercase italic mt-1"><i class="far fa-calendar-alt mr-1"></i> <?= $b['departure_date'] ?></div>
            <div class="text-[9px] font-black text-blue-500 uppercase"><?= $b['num_adults'] ?>NL, <?= $b['num_infants'] ?>NCT, <?= $b['num_children'] ?>TE</div>
        </div>
        <?php if ($b['rating']): ?>
            <div class="mb-4 p-3 bg-slate-50 rounded-xl border border-slate-100">
                <div class="flex text-amber-400 gap-1 mb-1">
                    <?php for($i=1; $i<=5; $i++): ?>
                        <i class="<?= $i <= $b['rating'] ? 'fas' : 'far' ?> fa-star text-[8px]"></i>
                    <?php endfor; ?>
                </div>
                <p class="text-[10px] italic text-slate-500 line-clamp-2">"<?= htmlspecialchars($b['comment']) ?>"</p>
            </div>
        <?php endif; ?>
        <div class="flex items-center justify-between pt-4 border-t border-dashed border-slate-100">
            <div><?= getStatusBadge($b['status']) ?></div>
            <div class="flex gap-2">
                <?= getActionButtons($b) ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="hidden lg:block bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
    <table class="w-full text-left">
        <thead>
            <tr class="bg-slate-50 text-slate-400 text-[10px] font-black uppercase tracking-widest border-b border-slate-100">
                <th class="px-8 py-5">Tour</th>
                <th class="px-8 py-5">Khách hàng</th>
                <th class="px-8 py-5 text-center">Tổng tiền</th>
                <th class="px-8 py-5 text-center">Trạng thái</th>
                <th class="px-8 py-5 text-center"><i class="fas fa-star mr-1"></i> Đánh giá</th>
                <th class="px-8 py-5 text-center">Thao tác</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach($bookings as $b): ?>
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-8 py-6">
                    <div class="flex items-center gap-4">
                        <img src="../assets/uploads/<?= $b['tour_image'] ?>" class="w-14 h-10 rounded-xl object-cover">
                        <div>
                            <p class="text-xs font-black text-slate-800 uppercase tracking-tighter leading-none mb-1"><?= htmlspecialchars($b['tour_title']) ?></p>
                            <p class="text-[10px] font-bold text-slate-400">Mã: #<?= $b['id'] ?></p>
                        </div>
                    </div>
                </td>
                <td class="px-8 py-6">
                    <div class="text-xs font-black text-slate-800 uppercase tracking-tighter leading-none mb-1"><?= htmlspecialchars($b['customer_name']) ?></div>
                    <div class="text-[10px] font-bold text-slate-500 italic"><?= $b['customer_email'] ?></div>
                    <div class="text-[10px] font-medium text-slate-400 mt-1"><?= $b['customer_phone'] ?></div>
                    <div class="text-[10px] font-bold text-amber-600 mt-1"><i class="far fa-calendar-alt mr-1"></i> <?= $b['departure_date'] ?></div>
                    <div class="text-[9px] font-black text-blue-500 uppercase mt-1">
                        <?= $b['num_adults'] ?>NL, <?= $b['num_infants'] ?>NCT, <?= $b['num_children'] ?>TE
                    </div>
                </td>
                <td class="px-8 py-6 text-center text-sm font-black text-slate-900"><?= number_format($b['total_price'], 0, ',', '.') ?>đ</td>
                <td class="px-8 py-6 text-center"><?= getStatusBadge($b['status']) ?></td>
                <td class="px-8 py-6 text-center">
                    <?php if ($b['rating']): ?>
                        <div class="flex flex-col items-center">
                            <div class="flex text-amber-400 gap-0.5 mb-1">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="<?= $i <= $b['rating'] ? 'fas' : 'far' ?> fa-star text-[8px]"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-[9px] text-slate-400 italic font-medium max-w-[120px] truncate" title="<?= htmlspecialchars($b['comment']) ?>"><?= htmlspecialchars($b['comment']) ?></span>
                        </div>
                    <?php else: ?>
                        <span class="text-[9px] text-slate-300 italic font-bold uppercase tracking-tighter">Chưa có</span>
                    <?php endif; ?>
                </td>
                <td class="px-8 py-6 text-center">
                    <div class="flex justify-center gap-3">
                        <?= getActionButtons($b) ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if($total_pages > 1): ?>
<div class="mt-10 flex justify-center space-x-2">
    <?php for($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>" class="w-10 h-10 flex items-center justify-center rounded-xl text-[10px] font-black <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-white text-slate-400 border' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php
function getStatusBadge($status) {
    $class = [
        'pending' => 'bg-slate-50 text-slate-400 border-slate-100',
        'completed' => 'bg-emerald-600 text-white border-emerald-600',
        'cancelled' => 'bg-slate-50 text-slate-400 border-slate-100'
    ];
    $text = [
        'pending' => 'Đang chờ TT',
        'completed' => 'Hoàn tất',
        'cancelled' => 'Đã hủy'
    ];
    return '<span class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase border '.($class[$status] ?? '').'">'.($text[$status] ?? '').'</span>';
}

function getActionButtons($b) {
    $html = '';
    if ($b['status'] == 'pending') {
        $html .= '<button onclick="processBooking('.$b['id'].', \'complete\')" class="w-10 h-10 flex items-center justify-center bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-600 hover:text-white transition-all"><i class="fas fa-check"></i></button>';
        $html .= '<button onclick="processBooking('.$b['id'].', \'cancel\')" class="w-10 h-10 flex items-center justify-center bg-red-50 text-red-400 rounded-xl hover:bg-red-500 hover:text-white transition-all"><i class="fas fa-times"></i></button>';
    }
    return $html;
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function processBooking(id, action) {
        let title, text, icon, confirmColor;
        
        if (action === 'confirm') {
            title = 'Xác nhận nhận cọc?';
            text = 'Hệ thống sẽ ghi nhận khách hàng đã chuyển 30% tiền cọc.';
            icon = 'question';
            confirmColor = '#2563eb';
        } else if (action === 'cancel') {
            title = 'Hủy đơn hàng này?';
            text = 'Bạn chắc chắn muốn hủy yêu cầu đặt tour này?';
            icon = 'warning';
            confirmColor = '#ef4444';
        } else if (action === 'complete') {
            title = 'Xác nhận thu đủ tiền?';
            text = 'Khách đã thanh toán 100% và tour đã sẵn sàng khởi hành.';
            icon = 'success';
            confirmColor = '#10b981';
        }

        Swal.fire({
            title: `<span class="text-sm font-black uppercase italic tracking-widest">${title}</span>`,
            text: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Bỏ qua',
            confirmButtonColor: confirmColor,
            customClass: { popup: 'rounded-[2.5rem]', confirmButton: 'rounded-xl px-8 font-black uppercase text-[10px]', cancelButton: 'rounded-xl px-8 font-black uppercase text-[10px]' }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `booking_process.php?id=${id}&action=${action}`;
            }
        });
    }
</script>
</main></div></div></body></html>
