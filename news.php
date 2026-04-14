<?php
require_once 'config.php';
include 'header.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$hero_stmt = $pdo->query("SELECT * FROM news ORDER BY id DESC LIMIT 3");
$hero_news = $hero_stmt->fetchAll();

$latest_news = $hero_news[0] ?? null;
$side_news = array_slice($hero_news, 1);

$count_stmt = $pdo->query("SELECT COUNT(*) FROM news");
$total_news = $count_stmt->fetchColumn();
$total_pages = ceil($total_news / $limit);

$list_stmt = $pdo->prepare("SELECT * FROM news ORDER BY id DESC LIMIT $limit OFFSET $offset");
$list_stmt->execute();
$all_news = $list_stmt->fetchAll();
?>

<main class="bg-[#f8fafc] min-h-screen pb-20">
    <div class="max-w-7xl mx-auto px-4 py-12">
        
        <?php if ($latest_news): ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-20">
            <div class="lg:col-span-2">
                <a href="news-detail.php?id=<?= $latest_news['id'] ?>" class="group block relative h-[500px] rounded-[3rem] overflow-hidden shadow-2xl">
                    <img src="assets/uploads/<?= $latest_news['image'] ?: 'default-news.jpg' ?>" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/20 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 p-10 w-full">
                        <span class="inline-block px-4 py-1.5 bg-blue-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl mb-4">Tin mới nhất</span>
                        <h2 class="text-3xl md:text-4xl font-black text-white uppercase italic tracking-tighter leading-tight mb-4 group-hover:text-blue-400 transition-colors">
                            <?= htmlspecialchars($latest_news['title']) ?>
                        </h2>
                        <p class="text-slate-300 text-sm line-clamp-2 max-w-2xl font-medium"><?= htmlspecialchars($latest_news['summary']) ?></p>
                    </div>
                </a>
            </div>

            <div class="space-y-8">
                <?php foreach($side_news as $sn): ?>
                <a href="news-detail.php?id=<?= $sn['id'] ?>" class="group block relative h-[234px] rounded-[2.5rem] overflow-hidden shadow-xl">
                    <img src="assets/uploads/<?= $sn['image'] ?: 'default-news.jpg' ?>" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-transparent opacity-80"></div>
                    <div class="absolute bottom-0 left-0 p-6">
                        <p class="text-[9px] font-black text-blue-400 uppercase tracking-widest mb-2"><?= date('d.m.Y', strtotime($sn['created_at'])) ?></p>
                        <h3 class="text-lg font-black text-white uppercase italic tracking-tighter leading-tight line-clamp-2 group-hover:text-blue-400 transition-colors">
                            <?= htmlspecialchars($sn['title']) ?>
                        </h3>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="mb-10 flex items-center justify-between">
            <h2 class="text-2xl font-black text-slate-900 uppercase italic tracking-tighter flex items-center">
                <span class="w-10 h-1.5 bg-slate-900 rounded-full mr-4"></span> Tất cả tin tức
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <?php foreach($all_news as $news): ?>
            <a href="news-detail.php?id=<?= $news['id'] ?>" class="bg-white p-6 rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 flex flex-col md:flex-row gap-8 items-center hover:shadow-2xl transition-all group">
                <div class="w-full md:w-72 h-48 flex-shrink-0 rounded-[2rem] overflow-hidden">
                    <img src="assets/uploads/<?= $news['image'] ?: 'default-news.jpg' ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-4 mb-3">
                        <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest"><?= date('d M, Y', strtotime($news['created_at'])) ?></span>
                        <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Cẩm nang</span>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 uppercase italic tracking-tighter mb-3 group-hover:text-blue-600 transition-colors">
                        <?= htmlspecialchars($news['title']) ?>
                    </h3>
                    <p class="text-sm text-slate-500 line-clamp-2 font-medium leading-relaxed">
                        <?= htmlspecialchars($news['summary']) ?>
                    </p>
                </div>
                <div class="pr-6">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-blue-600 group-hover:text-white transition-all">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="mt-16 flex justify-center gap-3">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>" 
                   class="w-12 h-12 flex items-center justify-center rounded-2xl font-black text-xs transition-all <?= $i == $page ? 'bg-blue-600 text-white shadow-xl shadow-blue-200' : 'bg-white text-slate-400 border border-slate-100 hover:text-blue-600' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

    </div>
</main>

<?php include 'footer.php'; ?>