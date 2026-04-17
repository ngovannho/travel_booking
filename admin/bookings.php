<?php 
require_once '../config.php';
include 'header.php'; 

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$limit = 5;
$offset = ($page - 1) * $limit;

$where = "WHERE (b.customer_name LIKE ? OR b.customer_email LIKE ? OR b.id LIKE ?)";
$params = ["%$search%", "%$search%", "%$search%"];

if ($status_filter) {
    $where .= " AND b.status = ?";
    $params[] = $status_filter;
}

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings b $where");
$count_stmt->execute($params);
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT b.*, t.title as tour_title, t.image as tour_image, r.rating, r.comment 
        FROM bookings b 
        JOIN tours t ON b.tour_id = t.id 
        LEFT JOIN reviews r ON b.id = r.booking_id 
$where 
        ORDER BY b.id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();
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

<div class="mb-8 flex flex-col md:flex-row gap-4">
    <form method="GET" class="relative flex-1">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm tên khách, email hoặc mã đơn..." 
               class="w-full pl-12 pr-4 py-4 bg-white border-0 shadow-sm rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-bold">
        <span class="absolute left-4 top-4 text-slate-300"><i class="fas fa-search fa-lg"></i></span>
        <?php if($status_filter): ?><input type="hidden" name="status" value="<?= $status_filter ?>"><?php endif; ?>
    </form>
    
    <div class="flex bg-white p-1.5 rounded-2xl shadow-sm border border-slate-100 overflow-x-auto">
        <?php 
        $status_options = [
            '' => 'Tất cả',
            'pending' => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'completed' => 'Hoàn tất',
            'cancelled' => 'Đã hủy'
        ];
        foreach($status_options as $val => $label): ?>
            <a href="?status=<?= $val ?>&search=<?= $search ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-wider whitespace-nowrap transition-all <?= $status_filter === $val ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-400 hover:text-slate-900' ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>
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
        <thead class="bg-slate-50/50">
            <tr class="text-slate-400 text-[9px] font-black uppercase tracking-widest border-b border-slate-100">
                <th class="px-6 py-4">Tour</th>
                <th class="px-6 py-4">Khách hàng</th>
                <th class="px-6 py-4 text-center">Tổng tiền</th>
                <th class="px-6 py-4 text-center">Trạng thái</th>
                <th class="px-6 py-4 text-center"><i class="fas fa-star"></i></th>
                <th class="px-6 py-4 text-center">Thao tác</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach($bookings as $b): ?>
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-4">
                        <img src="../assets/uploads/<?= $b['tour_image'] ?>" class="w-10 h-7 rounded-lg object-cover">
                        <div>
                            <p class="text-[11px] font-black text-slate-800 uppercase tracking-tighter leading-none mb-1 truncate max-w-[150px]"><?= htmlspecialchars($b['tour_title']) ?></p>
                            <p class="text-[10px] font-bold text-slate-400">Mã: #<?= $b['id'] ?></p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-[11px] font-black text-slate-800 uppercase tracking-tighter leading-none"><?= htmlspecialchars($b['customer_name']) ?></div>
                    <div class="text-[10px] font-bold text-slate-500 italic"><?= $b['customer_email'] ?></div>
                </td>
                <td class="px-6 py-4 text-center text-xs font-black text-slate-900"><?= number_format($b['total_price'], 0, ',', '.') ?>đ</td>
                <td class="px-6 py-4 text-center"><?= getStatusBadge($b['status']) ?></td>
                <td class="px-6 py-4 text-center">
                    <?php if ($b['rating']): ?>
                        <div class="text-amber-400 text-[10px] font-black"><?= $b['rating'] ?> ★</div>
                    <?php else: ?>
                        <span class="text-[8px] text-slate-200">-</span>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4 text-center">
                    <div class="flex justify-center gap-3">
                        <?= getActionButtons($b) ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= renderAdminPagination($page, $total_pages, $_GET) ?>

<?php
function getStatusBadge($status) {
    $class = [
        'pending' => 'bg-amber-50 text-amber-500 border-amber-100',
        'confirmed' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
        'completed' => 'bg-emerald-600 text-white border-emerald-600',
        'cancelled' => 'bg-red-50 text-red-400 border-red-100'
    ];
    $text = [
        'pending' => 'Chờ xử lý',
        'confirmed' => 'Đã thanh toán',
        'completed' => 'Hoàn tất',
        'cancelled' => 'Đã hủy'
    ];
    return '<span class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase border '.($class[$status] ?? '').'">'.($text[$status] ?? '').'</span>';
}

function getActionButtons($b) {
    $html = '';
    if ($b['status'] == 'pending') {
        $html .= '<button onclick="processBooking('.$b['id'].', \'confirm\')" class="w-10 h-10 flex items-center justify-center bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all"><i class="fas fa-hand-holding-usd"></i></button>';
        $html .= '<button onclick="processBooking('.$b['id'].', \'cancel\')" class="w-10 h-10 flex items-center justify-center bg-red-50 text-red-400 rounded-xl hover:bg-red-500 hover:text-white transition-all"><i class="fas fa-times"></i></button>';
    } elseif ($b['status'] == 'confirmed') {
        $html .= '<button onclick="processBooking('.$b['id'].', \'complete\')" class="w-10 h-10 flex items-center justify-center bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-600 hover:text-white transition-all"><i class="fas fa-check"></i></button>';
    }
    return $html;
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function processBooking(id, action) {
        let title, text, icon, confirmColor;
        
        if (action === 'confirm') {
            title = 'Xác nhận thanh toán?';
            text = 'Hệ thống sẽ ghi nhận khách hàng đã thanh toán 100% giá trị tour.';
            icon = 'question';
            confirmColor = '#2563eb';
        } else if (action === 'cancel') {
            title = 'Hủy đơn hàng này?';
            text = 'Bạn chắc chắn muốn hủy yêu cầu đặt tour này?';
            icon = 'warning';
            confirmColor = '#ef4444';
        } else if (action === 'complete') {
            title = 'Xác nhận hoàn tất chuyến đi?';
            text = 'Tour đã kết thúc thành công. Hệ thống sẽ gửi yêu cầu khách hàng đánh giá dịch vụ.';
            icon = 'info';
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
