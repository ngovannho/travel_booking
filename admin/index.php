<?php 
require_once '../config.php';
include 'header.php'; 

// Xử lý bộ lọc thời gian
$filter = $_GET['filter'] ?? 'month';
$date_cond = "1=1";
$prev_date_cond = "1=1";

if ($filter === 'today') {
    $date_cond = "DATE(booking_date) = CURDATE()";
    $prev_date_cond = "DATE(booking_date) = CURDATE() - INTERVAL 1 DAY";
} elseif ($filter === 'week') {
    $date_cond = "YEARWEEK(booking_date, 1) = YEARWEEK(CURDATE(), 1)";
    $prev_date_cond = "YEARWEEK(booking_date, 1) = YEARWEEK(CURDATE() - INTERVAL 1 WEEK, 1)";
} elseif ($filter === 'month') {
    $date_cond = "DATE_FORMAT(booking_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
    $prev_date_cond = "DATE_FORMAT(booking_date, '%Y-%m') = DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m')";
} else {
    $filter = 'all';
}

// Thống kê hiện tại
$revenue = $pdo->query("SELECT SUM(total_price) FROM bookings WHERE status = 'completed' AND $date_cond")->fetchColumn() ?: 0;
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE $date_cond")->fetchColumn() ?: 0;
$cancelled_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'cancelled' AND $date_cond")->fetchColumn() ?: 0;
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn() ?: 0;
$total_tours = $pdo->query("SELECT COUNT(*) FROM tours WHERE status = 1")->fetchColumn() ?: 0;

// Thống kê kỳ trước để tính tăng trưởng
$rev_prev = $pdo->query("SELECT SUM(total_price) FROM bookings WHERE status = 'completed' AND $prev_date_cond")->fetchColumn() ?: 0;
$book_prev = $pdo->query("SELECT COUNT(*) FROM bookings WHERE $prev_date_cond")->fetchColumn() ?: 0;

$growth = $rev_prev > 0 ? (($revenue - $rev_prev) / $rev_prev) * 100 : ($revenue > 0 ? 100 : 0);
$book_growth = $book_prev > 0 ? (($total_bookings - $book_prev) / $book_prev) * 100 : ($total_bookings > 0 ? 100 : 0);

// Giá trị đơn hàng trung bình (AOV)
$aov = $total_bookings > 0 ? $revenue / $total_bookings : 0;

// Tỷ lệ hủy đơn
$cancel_rate = $total_bookings > 0 ? ($cancelled_bookings / $total_bookings) * 100 : 0;

// Thống kê đánh giá
$avg_rating = $pdo->query("SELECT AVG(rating) FROM reviews")->fetchColumn() ?: 0;
$total_reviews = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn() ?: 0;
$recent_reviews = $pdo->query("SELECT r.*, u.fullname, t.title 
                              FROM reviews r 
                              JOIN users u ON r.user_id = u.id 
                              JOIN tours t ON r.tour_id = t.id 
                              ORDER BY r.created_at DESC LIMIT 3")->fetchAll();

$monthly_rev = $pdo->query("SELECT DATE_FORMAT(booking_date, '%m/%Y') as m, SUM(total_price) as total FROM bookings WHERE status = 'completed' GROUP BY DATE_FORMAT(booking_date, '%Y-%m') ORDER BY DATE_FORMAT(booking_date, '%Y-%m') DESC LIMIT 6")->fetchAll();
$rev_labels = json_encode(array_reverse(array_column($monthly_rev, 'm')));
$rev_data = json_encode(array_reverse(array_column($monthly_rev, 'total')));

// Thống kê phân bổ sản phẩm theo danh mục (Dựa trên số lượng tour đang hoạt động)
$cat_stats = $pdo->query("SELECT c.name, COUNT(t.id) as count 
                          FROM categories c 
                          LEFT JOIN tours t ON c.id = t.category_id AND t.status = 1 
                          GROUP BY c.id ORDER BY count DESC")->fetchAll();
$total_tours_count = array_sum(array_column($cat_stats, 'count')) ?: 1;

$status_counts = $pdo->query("SELECT status, COUNT(*) as count FROM bookings WHERE $date_cond GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$status_labels = json_encode(['Chờ xử lý', 'Hoàn tất', 'Đã hủy']);
$status_values = json_encode([$status_counts['pending'] ?? 0, $status_counts['completed'] ?? 0, $status_counts['cancelled'] ?? 0]);

$top_tours = $pdo->query("SELECT t.title, COUNT(b.id) as sales, SUM(b.total_price) as revenue FROM tours t JOIN bookings b ON t.id = b.tour_id WHERE b.status = 'completed' AND $date_cond GROUP BY t.id ORDER BY revenue DESC LIMIT 5")->fetchAll();

// Top khách hàng chi tiêu nhiều nhất
$top_customers = $pdo->query("SELECT u.fullname, SUM(b.total_price) as total_spent, COUNT(b.id) as total_bookings FROM users u JOIN bookings b ON u.id = b.user_id WHERE b.status = 'completed' AND $date_cond GROUP BY u.id ORDER BY total_spent DESC LIMIT 5")->fetchAll();
?>

<div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div>
        <h3 class="text-2xl font-black text-slate-800 uppercase italic tracking-tighter">Dashboard</h3>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Phân tích hiệu quả kinh doanh</p>
    </div>

    <!-- Bộ lọc thời gian -->
    <div class="flex bg-slate-200/50 p-1 rounded-[1.2rem] border border-slate-200/60 shadow-inner backdrop-blur-sm">
        <?php 
        $filters = ['all' => 'Tất cả', 'today' => 'Hôm nay', 'week' => 'Tuần này', 'month' => 'Tháng này'];
        foreach($filters as $key => $label): 
        ?>
            <a href="?filter=<?= $key ?>" class="px-5 py-2.5 rounded-[1rem] text-[9px] font-black uppercase tracking-widest transition-all duration-500 <?= $filter == $key ? 'bg-white text-blue-600 shadow-md scale-100' : 'text-slate-500 hover:text-slate-800' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-50 hover:shadow-md transition-shadow">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase italic tracking-widest">Doanh thu</p>
                <h3 class="text-xl font-black text-slate-800 mt-1"><?= number_format($revenue/1000000, 1) ?>M <span class="text-[9px] text-slate-300">VNĐ</span></h3>
            </div>
            <div class="w-10 h-10 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center border border-slate-100 shadow-sm"><i class="fas fa-wallet text-sm"></i></div>
        </div>
        <div class="mt-4 flex items-center text-[10px] font-black <?= $growth >= 0 ? 'text-emerald-500' : 'text-red-500' ?>">
            <i class="fas fa-caret-<?= $growth >= 0 ? 'up' : 'down' ?> mr-1"></i> <?= number_format(abs($growth), 1) ?>% <span class="text-slate-300 ml-1 font-bold">vs kỳ trước</span>
        </div>
    </div>
    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-50 hover:shadow-md transition-shadow">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase italic tracking-widest">Đơn hàng</p>
                <h3 class="text-xl font-black text-slate-800 mt-1"><?= $total_bookings ?></h3>
            </div>
            <div class="w-10 h-10 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center border border-slate-100 shadow-sm"><i class="fas fa-shopping-bag text-sm"></i></div>
        </div>
        <div class="mt-4 flex items-center text-[10px] font-black <?= $book_growth >= 0 ? 'text-emerald-500' : 'text-red-500' ?>">
            <i class="fas fa-caret-<?= $book_growth >= 0 ? 'up' : 'down' ?> mr-1"></i> <?= number_format(abs($book_growth), 1) ?>% <span class="text-slate-300 ml-1 font-bold">vs kỳ trước</span>
        </div>
    </div>
    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-50 hover:shadow-md transition-shadow">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase italic tracking-widest">Tỷ lệ hủy</p>
                <h3 class="text-xl font-black text-slate-800 mt-1"><?= number_format($cancel_rate, 1) ?>%</h3>
            </div>
            <div class="w-10 h-10 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center border border-slate-100 shadow-sm"><i class="fas fa-times-circle text-sm"></i></div>
        </div>
        <p class="mt-4 text-[10px] font-bold text-slate-400 italic">Rủi ro mất doanh thu</p>
    </div>
    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-50 hover:shadow-md transition-shadow">
        <p class="text-[10px] font-black text-slate-400 uppercase italic tracking-widest">Tours hoạt động</p>
        <h3 class="text-xl font-black text-slate-800 mt-1"><?= $total_tours ?></h3>
        <div class="mt-4 flex -space-x-2">
            <div class="w-6 h-6 rounded-full border-2 border-white bg-blue-100"></div>
            <div class="w-6 h-6 rounded-full border-2 border-white bg-amber-100"></div>
            <div class="w-6 h-6 rounded-full border-2 border-white bg-slate-100"></div>
        </div>
    </div>
    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-50 hover:shadow-md transition-shadow">
        <p class="text-[10px] font-black text-slate-400 uppercase italic tracking-widest">Đánh giá TB</p>
        <h3 class="text-xl font-black text-amber-500 mt-1"><?= number_format($avg_rating, 1) ?> ★</h3>
        <div class="mt-4 bg-slate-50 h-1.5 rounded-full overflow-hidden">
            <div class="bg-amber-400 h-full" style="width: <?= ($avg_rating/5)*100 ?>%"></div>
        </div>
        <p class="mt-1 text-[8px] font-bold text-slate-300 uppercase"><?= $total_reviews ?> lượt phản hồi</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-2 bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-50">
        <div class="flex justify-between items-center mb-6">
            <h4 class="text-xs font-black text-slate-800 uppercase italic">Xu hướng doanh thu (VNĐ)</h4>
            <span class="text-[9px] font-bold text-slate-400 uppercase">6 tháng gần nhất</span>
        </div>
        <canvas id="revChart" height="200"></canvas>
    </div>
    <div class="lg:col-span-1 bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-50 flex flex-col">
        <div class="flex items-center justify-between mb-8">
            <h4 class="text-[11px] font-black text-slate-800 uppercase italic tracking-widest">Phân bổ Tour</h4>
            <i class="fas fa-th-large text-slate-300 text-[10px]"></i>
        </div>
        <div class="space-y-5 flex-1">
            <?php foreach(array_slice($cat_stats, 0, 5) as $cs): 
                $percent = ($cs['count'] / $total_tours_count) * 100;
            ?>
            <div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-tighter truncate pr-2"><?= htmlspecialchars($cs['name']) ?></span>
                    <span class="text-[9px] font-black text-slate-900"><?= $cs['count'] ?></span>
                </div>
                <div class="w-full bg-slate-50 h-1.5 rounded-full overflow-hidden border border-slate-100/50">
                    <div class="bg-blue-600 h-full rounded-full transition-all duration-1000 shadow-sm shadow-blue-200" style="width: <?= $percent ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if(count($cat_stats) > 5): ?>
                <p class="text-center text-[8px] font-bold text-slate-300 uppercase tracking-widest pt-2">+ <?= count($cat_stats) - 5 ?> danh mục khác</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="lg:col-span-1 bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-50">
        <h4 class="text-xs font-black text-slate-800 mb-6 uppercase italic">Tình trạng đơn hàng</h4>
        <canvas id="statusChart" height="250"></canvas>
    </div>

    <!-- Top Tours -->
    <div class="lg:col-span-2 bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-50">
        <h4 class="text-[11px] font-black text-slate-800 mb-6 uppercase italic">Top Tour doanh thu cao</h4>
        <div class="space-y-4">
            <?php 
            $max_rev = !empty($top_tours) ? $top_tours[0]['revenue'] : 1;
            foreach($top_tours as $index => $tt): 
                $percent = ($tt['revenue'] / $max_rev) * 100;
            ?>
            <div class="p-4 bg-slate-50/40 rounded-2xl border border-slate-100 group hover:bg-white hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] font-black text-slate-300">0<?= $index + 1 ?></span>
                        <p class="text-[11px] font-black text-slate-800 uppercase truncate max-w-[200px]"><?= htmlspecialchars($tt['title']) ?></p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[11px] font-black text-blue-600 italic"><?= number_format($tt['revenue']/1000000, 1) ?>M</p>
                </div>
                <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-blue-600 h-full rounded-full transition-all duration-1000" style="width: <?= $percent ?>%"></div>
                </div>
                <div class="mt-2 text-[9px] font-bold text-slate-400 uppercase italic"><?= $tt['sales'] ?> lượt đặt từ khách hàng</div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($top_tours)): ?>
                <div class="text-center py-10">
                    <i class="fas fa-chart-bar text-slate-200 text-3xl mb-2"></i>
                    <p class="text-[10px] font-black text-slate-300 uppercase italic">Chưa có dữ liệu thống kê</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="lg:col-span-2 bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-50">
        <div class="flex justify-between items-center mb-6">
            <h4 class="text-[11px] font-black text-slate-800 uppercase italic">Khách hàng chi tiêu nhiều nhất</h4>
            <i class="fas fa-crown text-amber-400"></i>
        </div>
        <div class="space-y-4">
            <?php foreach($top_customers as $index => $cust): ?>
            <div class="flex items-center justify-between p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                <div class="flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($cust['fullname']) ?>&background=random" class="w-10 h-10 rounded-xl">
                    <div>
                        <p class="text-[11px] font-black text-slate-800 uppercase"><?= htmlspecialchars($cust['fullname']) ?></p>
                        <p class="text-[9px] text-slate-400 font-bold uppercase"><?= $cust['total_bookings'] ?> chuyến đi hoàn tất</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs font-black text-emerald-600"><?= number_format($cust['total_spent'], 0, ',', '.') ?>đ</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Nhận xét mới nhất -->
    <div class="lg:col-span-2 bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-50">
        <div class="flex justify-between items-center mb-6">
            <h4 class="text-[11px] font-black text-slate-800 uppercase italic">Nhận xét mới nhất</h4>
            <a href="tour_reviews.php" class="text-[9px] font-black text-blue-600 uppercase hover:underline">Tất cả</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach($recent_reviews as $rr): ?>
                <div class="p-4 bg-slate-50/50 rounded-2xl border border-slate-100 relative">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-[10px] font-black text-slate-700 uppercase"><?= htmlspecialchars($rr['fullname']) ?></span>
                        <div class="flex text-amber-400 text-[8px] gap-0.5">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="<?= $i <= $rr['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <p class="text-[11px] font-bold text-blue-600 truncate mb-2 italic">@<?= htmlspecialchars($rr['title']) ?></p>
                    <p class="text-[11px] text-slate-500 italic line-clamp-2">"<?= htmlspecialchars($rr['comment']) ?>"</p>
                </div>
            <?php endforeach; ?>
            <?php if(empty($recent_reviews)): ?>
                <p class="col-span-3 text-center py-4 text-[10px] font-bold text-slate-400 uppercase italic">Chưa có đánh giá nào</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const revCtx = document.getElementById('revChart').getContext('2d');
    const revGradient = revCtx.createLinearGradient(0, 0, 0, 300);
    revGradient.addColorStop(0, 'rgba(37, 99, 235, 0.2)');
    revGradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: <?= $rev_labels ?>,
            datasets: [{
                label: 'Doanh thu',
                data: <?= $rev_data ?>,
                borderColor: '#3b82f6',
                backgroundColor: revGradient,
                tension: 0.45,
                fill: true,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: { 
            plugins: { legend: { display: false } }, 
            scales: { 
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.03)' }, ticks: { font: { size: 9, weight: 'bold' } } },
                x: { grid: { display: false }, ticks: { font: { size: 9, weight: 'bold' } } }
            } 
        }
    });

    new Chart(document.getElementById('statusChart'), {
        type: 'pie',
        data: {
            labels: <?= $status_labels ?>,
            datasets: [{
                data: <?= $status_values ?>,
                backgroundColor: ['#f59e0b', '#10b981', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: { 
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 8, padding: 15, font: { size: 9, weight: 'bold' }, usePointStyle: true } } } 
        }
    });

    function toggleSidebar() {
        const s = document.getElementById('sidebar');
        const o = document.getElementById('overlay');
        if (window.innerWidth <= 768) {
            s.classList.toggle('mobile-open');
            o.classList.toggle('hidden');
        } else {
            s.classList.toggle('collapsed');
        }
    }
</script>

</main></div></div></body></html>