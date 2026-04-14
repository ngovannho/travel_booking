<?php
require_once 'config.php';
include 'header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    header("Location: news.php");
    exit;
}

$related_stmt = $pdo->prepare("SELECT * FROM news WHERE id != ? ORDER BY id DESC LIMIT 4");
$related_stmt->execute([$id]);
$related_news = $related_stmt->fetchAll();
?>

<style>
    .news-content img { border-radius: 2rem; margin: 2rem auto; display: block; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1); }
    .news-content p { margin-bottom: 1.5rem; line-height: 1.8; }
    .news-content h2, .news-content h3 { font-weight: 900; font-style: italic; color: #0f172a; margin-top: 2rem; margin-bottom: 1rem; text-transform: uppercase; }
</style>

<main class="bg-gray-50 min-h-screen pb-20">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <div class="flex flex-col lg:flex-row gap-12">
            
            <article class="w-full lg:w-2/3">
                <nav class="flex text-[10px] font-black uppercase tracking-[0.2em] text-blue-600 mb-6 items-center">
                    <a href="index.php" class="hover:opacity-70">Trang chủ</a>
                    <i class="fas fa-chevron-right mx-3 text-[8px] text-gray-300"></i>
                    <a href="news.php" class="hover:opacity-70">Tin tức</a>
                </nav>

                <h1 class="text-3xl md:text-5xl font-black italic text-slate-900 leading-[1.2] uppercase tracking-tighter mb-8">
                    <?= htmlspecialchars($article['title']) ?>
                </h1>

                <div class="flex items-center gap-6 mb-10 pb-8 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name=Admin&background=0f172a&color=fff" class="w-10 h-10 rounded-full">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase leading-none">Tác giả</p>
                            <p class="text-xs font-bold text-slate-800 uppercase">Ban biên tập</p>
                        </div>
                    </div>
                    <div class="h-8 w-[1px] bg-gray-200"></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase leading-none mb-1">Ngày đăng</p>
                        <p class="text-xs font-bold text-slate-800"><?= date('d/m/Y', strtotime($article['created_at'])) ?></p>
                    </div>
                </div>

                <div class="rounded-[3rem] overflow-hidden shadow-2xl mb-12 border-[10px] border-white">
                    <img src="assets/uploads/<?= $article['image'] ?: 'default-news.jpg' ?>" class="w-full h-auto object-cover">
                </div>

                <div class="news-content text-lg text-slate-600 font-medium px-2">
                    <div class="italic text-xl text-slate-900 font-bold border-l-4 border-blue-600 pl-6 mb-10 leading-relaxed">
                        <?= htmlspecialchars($article['summary']) ?>
                    </div>
                    
                    <div class="prose prose-slate max-w-none">
                        <?= $article['content'] ?>
                    </div>
                </div>

                <div class="mt-16 p-10 bg-slate-900 rounded-[3rem] text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-2xl">
                    <div>
                        <h4 class="text-xl font-black italic tracking-tighter mb-2">Bạn thấy bài viết này hữu ích?</h4>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Chia sẻ cho bạn bè ngay</p>
                    </div>
                    <div class="flex gap-4">
                        <button class="w-12 h-12 bg-white/10 hover:bg-blue-600 rounded-2xl flex items-center justify-center transition-all"><i class="fab fa-facebook-f"></i></button>
                        <button class="w-12 h-12 bg-white/10 hover:bg-blue-400 rounded-2xl flex items-center justify-center transition-all"><i class="fab fa-twitter"></i></button>
                        <button class="w-12 h-12 bg-white/10 hover:bg-red-500 rounded-2xl flex items-center justify-center transition-all"><i class="fas fa-link"></i></button>
                    </div>
                </div>
            </article>

            <aside class="w-full lg:w-1/3">
                <div class="sticky top-28 space-y-10">
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-[0.3em] text-slate-400 mb-8 flex items-center">
                            <span class="w-10 h-1 bg-blue-600 rounded-full mr-4"></span> Tin tức liên quan
                        </h3>
                        <div class="space-y-6">
                            <?php foreach($related_news as $rel): ?>
                            <a href="news-detail.php?id=<?= $rel['id'] ?>" class="flex gap-4 group">
                                <div class="w-24 h-24 flex-shrink-0 rounded-2xl overflow-hidden shadow-md">
                                    <img src="assets/uploads/<?= $rel['image'] ?: 'default-news.jpg' ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-black text-slate-800 leading-tight group-hover:text-blue-600 transition-colors line-clamp-2 uppercase italic tracking-tighter"><?= htmlspecialchars($rel['title']) ?></h4>
                                    <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase"><?= date('d.m.Y', strtotime($rel['created_at'])) ?></p>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-[3rem] p-10 text-white shadow-2xl relative overflow-hidden group">
                        <div class="relative z-10">
                            <h4 class="text-2xl font-black italic tracking-tighter mb-4 leading-none uppercase">Tour đang hot!</h4>
                            <p class="text-[10px] font-bold text-blue-100 uppercase tracking-widest mb-8 italic opacity-80 leading-relaxed">Khám phá các điểm đến được yêu thích nhất tháng này.</p>
                            <a href="tours.php" class="inline-block bg-white text-blue-600 px-8 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:scale-105 transition-transform shadow-xl shadow-blue-900/20">Xem ngay</a>
                        </div>
                        <i class="fas fa-fire absolute -bottom-6 -right-6 text-9xl text-white/10 -rotate-12 transition-transform duration-700 group-hover:rotate-0"></i>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>