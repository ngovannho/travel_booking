<?php 
require_once '../config.php';
include 'header.php'; 

$rating_filter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;

// Lấy danh sách tour và trung bình cộng đánh giá
$sql = "SELECT t.id, t.title, t.image, 
               (SELECT AVG(rating) FROM reviews WHERE tour_id = t.id) as avg_rating, 
               (SELECT COUNT(id) FROM reviews WHERE tour_id = t.id) as total_reviews 
        FROM tours t 
        WHERE EXISTS (SELECT 1 FROM reviews r WHERE r.tour_id = t.id " . ($rating_filter > 0 ? " AND r.rating = ?" : "") . ")
        ORDER BY avg_rating DESC";

$stmt = $pdo->prepare($sql);
if ($rating_filter > 0) {
    $stmt->execute([$rating_filter]);
} else {
    $stmt->execute();
}
$tour_summaries = $stmt->fetchAll();
?>

<div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div>
        <h3 class="text-2xl font-black text-slate-800 uppercase italic tracking-tighter">Tổng hợp Đánh giá</h3>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Phản hồi từ khách hàng về chất lượng tour</p>
    </div>

    <!-- Bộ lọc số sao -->
    <div class="flex gap-2 bg-white p-2 rounded-2xl shadow-sm border border-slate-50">
        <a href="tour_reviews.php" class="px-3 py-2 rounded-xl text-[9px] font-black uppercase tracking-wider transition-all <?= $rating_filter == 0 ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50' ?>">
            <i class="fas fa-border-all mr-1"></i> Tất cả
        </a>
        <?php for($i=5; $i>=1; $i--): ?>
            <a href="?rating=<?= $i ?>" class="px-3 py-2 rounded-xl text-[9px] font-black uppercase tracking-wider transition-all <?= $rating_filter == $i ? 'bg-amber-500 text-white shadow-lg shadow-amber-200' : 'text-slate-400 hover:bg-slate-50' ?>">
                <?= $i ?> <i class="fas fa-star ml-0.5 text-[8px]"></i>
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

</main></div></div></body></html>