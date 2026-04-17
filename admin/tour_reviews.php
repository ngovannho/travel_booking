<?php 
require_once '../config.php';
include 'header.php'; 

$rating_filter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Số lượng tour hiển thị trên mỗi trang
$offset = ($page - 1) * $limit;

// Đếm tổng số tour có đánh giá (áp dụng bộ lọc rating nếu có)
$count_sql = "SELECT COUNT(DISTINCT t.id) 
              FROM tours t 
              WHERE EXISTS (SELECT 1 FROM reviews r WHERE r.tour_id = t.id " . ($rating_filter > 0 ? " AND r.rating = ?" : "") . ")";
$count_stmt = $pdo->prepare($count_sql);
$count_params = [];
if ($rating_filter > 0) {
    $count_params[] = $rating_filter;
}
$count_stmt->execute($count_params);
$total_tours_with_reviews = $count_stmt->fetchColumn();
$total_pages = ceil($total_tours_with_reviews / $limit);

// Lấy danh sách tour và trung bình cộng đánh giá
$sql = "SELECT t.id, t.title, t.image, 
               (SELECT AVG(rating) FROM reviews WHERE tour_id = t.id) as avg_rating, 
               (SELECT COUNT(id) FROM reviews WHERE tour_id = t.id) as total_reviews 
        FROM tours t 
        WHERE EXISTS (SELECT 1 FROM reviews r WHERE r.tour_id = t.id " . ($rating_filter > 0 ? " AND r.rating = ?" : "") . ") ORDER BY avg_rating DESC LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
$params = [];
if ($rating_filter > 0) {
    $params[] = $rating_filter;
}
$params[] = $limit;
$params[] = $offset;
$stmt->execute($params);
$tour_summaries = $stmt->fetchAll();
?>

<div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div>
        <h3 class="text-2xl font-black text-slate-800 uppercase italic tracking-tighter">Tổng hợp Đánh giá</h3>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Phản hồi từ khách hàng về chất lượng tour</p>
    </div>

    <!-- Bộ lọc số sao -->
    <div class="flex bg-slate-200/50 p-1 rounded-[1.2rem] border border-slate-200/60 shadow-inner backdrop-blur-sm">
        <a href="tour_reviews.php" class="px-5 py-2.5 rounded-[1rem] text-[9px] font-black uppercase tracking-widest transition-all duration-500 <?= $rating_filter == 0 ? 'bg-white text-blue-600 shadow-md scale-100' : 'text-slate-500 hover:text-slate-800' ?>">
            <i class="fas fa-border-all mr-1"></i> Tất cả
        </a>
        <?php for($i=5; $i>=1; $i--): ?>
            <a href="?rating=<?= $i ?>" class="px-5 py-2.5 rounded-[1rem] text-[9px] font-black uppercase tracking-widest transition-all duration-500 <?= $rating_filter == $i ? 'bg-white text-amber-500 shadow-md scale-100' : 'text-slate-500 hover:text-slate-800' ?>">
                <?= $i ?> <i class="fas fa-star ml-1 text-[8px]"></i>
            </a>
        <?php endfor; ?>
    </div>
</div>

<div class="grid grid-cols-1 gap-8">
    <?php foreach($tour_summaries as $tour): ?>
        <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100 overflow-hidden">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Cột tóm tắt Tour -->
                <div class="md:w-1/3 border-r border-slate-50 pr-8">
                    <div class="flex items-center gap-4 mb-6">
                        <img src="../assets/uploads/<?= $tour['image'] ?>" class="w-16 h-16 rounded-2xl object-cover shadow-sm">
                        <div>
                            <h4 class="text-sm font-black text-slate-800 uppercase leading-tight"><?= htmlspecialchars($tour['title']) ?></h4>
                            <p class="text-[10px] font-bold text-slate-400 mt-1">Mã tour: #<?= $tour['id'] ?></p>
                        </div>
                    </div>
                    <div class="bg-blue-50 rounded-2xl p-6 text-center">
                        <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-2">Điểm trung bình</p>
                        <div class="text-3xl font-black text-blue-600 italic tracking-tighter"><?= number_format($tour['avg_rating'], 1) ?> / 5.0</div>
                        <p class="text-[10px] font-bold text-blue-400 mt-1"><?= $tour['total_reviews'] ?> lượt nhận xét</p>
                    </div>
                </div>

                <!-- Cột danh sách nhận xét -->
                <div class="md:w-2/3">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 italic">
                        Nhận xét chi tiết <?= $rating_filter > 0 ? "($rating_filter sao)" : "" ?>:
                    </p>
                    <div class="space-y-6 max-h-[300px] overflow-y-auto pr-4">
                        <?php
                        $rev_sql = "SELECT r.*, u.fullname FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.tour_id = ?";
                        $rev_params = [$tour['id']];
                        if ($rating_filter > 0) {
                            $rev_sql .= " AND r.rating = ?";
                            $rev_params[] = $rating_filter;
                        }
                        $rev_sql .= " ORDER BY r.created_at DESC";
                        $stmt_rev = $pdo->prepare($rev_sql);
                        $stmt_rev->execute($rev_params);
                        $details = $stmt_rev->fetchAll();
                        foreach($details as $rev):
                        ?>
                            <div class="bg-slate-50/50 p-5 rounded-2xl border border-slate-100">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-[11px] font-black text-slate-700 uppercase italic"><?= htmlspecialchars($rev['fullname']) ?></span>
                                    <div class="flex text-amber-400 text-[10px] gap-0.5">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="<?= $i <= $rev['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="text-xs font-medium text-slate-600 italic">"<?= htmlspecialchars($rev['comment']) ?>"</p>
                                <p class="text-[9px] text-slate-300 font-bold uppercase mt-3"><?= date('d/m/Y', strtotime($rev['created_at'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if(empty($tour_summaries)): ?>
    <div class="bg-white rounded-[3rem] p-20 text-center border border-slate-100">
        <div class="w-20 h-20 bg-slate-50 text-slate-200 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl"><i class="fas fa-star-half-alt"></i></div>
        <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic">Chưa có đánh giá nào từ khách hàng</p>
    </div>
<?php endif; ?>

<?= renderAdminPagination($page, $total_pages, $_GET) ?>

</main></div></div></body></html>