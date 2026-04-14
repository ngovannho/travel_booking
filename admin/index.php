<?php 
require_once '../config.php';
include 'header.php'; 

$revenue = $pdo->query("SELECT SUM(total_price) FROM bookings WHERE status = 'completed'")->fetchColumn() ?: 0;
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn() ?: 0;
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn() ?: 0;
$total_tours = $pdo->query("SELECT COUNT(*) FROM tours WHERE status = 1")->fetchColumn() ?: 0;

// Thống kê đánh giá
$avg_rating = $pdo->query("SELECT AVG(rating) FROM reviews")->fetchColumn() ?: 0;
$recent_reviews = $pdo->query("SELECT r.*, u.fullname, t.title 
                              FROM reviews r 
                              JOIN users u ON r.user_id = u.id 
                              JOIN tours t ON r.tour_id = t.id 
                              ORDER BY r.created_at DESC LIMIT 3")->fetchAll();

$monthly_rev = $pdo->query("SELECT DATE_FORMAT(booking_date, '%m') as m, SUM(total_price) as total FROM bookings WHERE status = 'completed' GROUP BY m ORDER BY m DESC LIMIT 6")->fetchAll();
$rev_labels = json_encode(array_reverse(array_column($monthly_rev, 'm')));
$rev_data = json_encode(array_reverse(array_column($monthly_rev, 'total')));

$cat_stats = $pdo->query("SELECT c.name, COUNT(b.id) as count FROM categories c LEFT JOIN tours t ON c.id = t.category_id LEFT JOIN bookings b ON t.id = b.tour_id GROUP BY c.id")->fetchAll();
$cat_labels = json_encode(array_column($cat_stats, 'name'));
$cat_data = json_encode(array_column($cat_stats, 'count'));
?>

<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-50">
        <p class="text-[10px] font-black text-slate-400 uppercase italic">Doanh thu</p>
        <h3 class="text-base font-black text-slate-800 mt-1"><?= number_format($revenue/1000000, 1) ?>M</h3>
        <div class="mt-3 w-8 h-8 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xs"><i class="fas fa-dollar-sign"></i></div>
    </div>
    <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-50">
        <p class="text-[10px] font-black text-slate-400 uppercase italic">Đơn hàng</p>
        <h3 class="text-lg font-black text-slate-800 mt-1"><?= $total_bookings ?></h3>
        <div class="mt-3 w-8 h-8 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-xs"><i class="fas fa-shopping-cart"></i></div>
    </div>
    <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-50">
        <p class="text-[10px] font-black text-slate-400 uppercase italic">Thành viên</p>
        <h3 class="text-lg font-black text-slate-800 mt-1"><?= $total_users ?></h3>
        <div class="mt-3 w-8 h-8 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xs"><i class="fas fa-users"></i></div>
    </div>
    <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-50">
        <p class="text-[10px] font-black text-slate-400 uppercase italic">Tours hoạt động</p>
        <h3 class="text-lg font-black text-slate-800 mt-1"><?= $total_tours ?></h3>
        <div class="mt-3 w-8 h-8 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center text-xs"><i class="fas fa-map-marked-alt"></i></div>
    </div>
    <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-50">
        <p class="text-[10px] font-black text-slate-400 uppercase italic">Đánh giá</p>
        <h3 class="text-lg font-black text-amber-500 mt-1"><?= number_format($avg_rating, 1) ?> ★</h3>
        <div class="mt-3 w-8 h-8 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center text-xs"><i class="fas fa-star"></i></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-50">
        <h4 class="text-xs font-black text-slate-800 mb-6 uppercase italic">Xu hướng doanh thu (VNĐ)</h4>
        <canvas id="revChart" height="200"></canvas>
    </div>
    <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-50">
        <h4 class="text-xs font-black text-slate-800 mb-6 uppercase italic">Phân bổ Tour</h4>
        <canvas id="catChart" height="250"></canvas>
    </div>

    <!-- Nhận xét mới nhất -->
    <div class="lg:col-span-3 bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-50 mt-4">
        <div class="flex justify-between items-center mb-6">
            <h4 class="text-xs font-black text-slate-800 uppercase italic">Nhận xét mới nhất</h4>
            <a href="tour_reviews.php" class="text-[9px] font-black text-blue-600 uppercase hover:underline">Tất cả</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach($recent_reviews as $rr): ?>
                <div class="p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-[10px] font-black text-slate-700 uppercase"><?= htmlspecialchars($rr['fullname']) ?></span>
                        <div class="flex text-amber-400 text-[8px] gap-0.5">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="<?= $i <= $rr['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <p class="text-[11px] font-bold text-blue-600 truncate mb-2"><?= htmlspecialchars($rr['title']) ?></p>
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
    new Chart(document.getElementById('revChart'), {
        type: 'line',
        data: {
            labels: <?= $rev_labels ?>,
            datasets: [{
                label: 'Doanh thu',
                data: <?= $rev_data ?>,
                borderColor: '#2563eb',
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(37,99,235,0.05)'
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });

    new Chart(document.getElementById('catChart'), {
        type: 'doughnut',
        data: {
            labels: <?= $cat_labels ?>,
            datasets: [{
                data: <?= $cat_data ?>,
                backgroundColor: ['#2563eb', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6']
            }]
        },
        options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10, weight: 'bold' } } } } }
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