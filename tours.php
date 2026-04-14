<?php
require_once 'config.php';
include 'header.php';

$cat_id = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$departure_date_filter = $_GET['departure_date'] ?? '';
$min_price_filter = $_GET['min_price'] ?? null;
$max_price_filter = $_GET['max_price'] ?? null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

$price_range = $pdo->query("SELECT MIN(price_base) as min_p, MAX(price_base) as max_p FROM tours WHERE status = 1")->fetch();
$db_min = $price_range['min_p'] ?? 0;
$db_max = 50000000; // Thiết lập mức giá tối đa là 50 triệu đồng

$min_price = $min_price_filter ?? $db_min;
$max_price = $max_price_filter ?? $db_max;

$where = "WHERE status = 1";
$params = [];

if ($cat_id) { $where .= " AND category_id = ?"; $params[] = $cat_id; }
if ($search) { $where .= " AND title LIKE ?"; $params[] = "%$search%"; }
if ($departure_date_filter) { 
    $where .= " AND departure_dates LIKE ?"; 
    $params[] = "%$departure_date_filter%"; 
}
$where .= " AND price_base BETWEEN ? AND ?";
$params[] = $min_price;
$params[] = $max_price;

$count_sql = "SELECT COUNT(*) FROM tours $where";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT t.*, c.name as cat_name FROM tours t LEFT JOIN categories c ON t.category_id = c.id $where ORDER BY t.id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tours = $stmt->fetchAll();

// LOGIC GỢI Ý TOUR TƯƠNG TỰ (RECOMMENDATIONS)
$displayed_ids = !empty($tours) ? array_column($tours, 'id') : [0];
$placeholders = implode(',', array_fill(0, count($displayed_ids), '?'));

$rec_params = $displayed_ids;
$rec_conditions = [];

if ($cat_id) { $rec_conditions[] = "t.category_id = ?"; $rec_params[] = $cat_id; }
if ($search) { 
    $rec_conditions[] = "t.title LIKE ?"; $rec_params[] = "%$search%"; 
    $rec_conditions[] = "t.departure_location LIKE ?"; $rec_params[] = "%$search%";
}
if ($departure_date_filter) { $rec_conditions[] = "t.departure_dates LIKE ?"; $rec_params[] = "%$departure_date_filter%"; }

// Gợi ý tầm giá tương đương (+/- 20% so với mức giá khách tìm)
$rec_conditions[] = "t.price_base BETWEEN ? AND ?";
$rec_params[] = $min_price * 0.8;
$rec_params[] = $max_price * 1.2;

$rec_where = "WHERE t.status = 1 AND t.id NOT IN ($placeholders)";
if (!empty($rec_conditions)) {
    $rec_where .= " AND (" . implode(" OR ", $rec_conditions) . ")";
}

// Thêm các tham số cho phần ORDER BY để thực hiện logic ưu tiên
$rec_params[] = "%$search%";      // Ưu tiên 1: Địa điểm khởi hành
$rec_params[] = (int)$cat_id;     // Ưu tiên 2: Danh mục
$rec_params[] = $min_price * 0.8; // Ưu tiên 3: Tầm giá (Min)
$rec_params[] = $max_price * 1.2; // Ưu tiên 3: Tầm giá (Max)

$rec_stmt = $pdo->prepare("SELECT t.*, c.name as cat_name FROM tours t LEFT JOIN categories c ON t.category_id = c.id $rec_where 
                            ORDER BY 
                                (t.departure_location LIKE ?) DESC, 
                                (t.category_id = ?) DESC, 
                                (t.price_base BETWEEN ? AND ?) DESC, 
                                RAND() 
                            LIMIT 3");
$rec_stmt->execute($rec_params);
$recommended_tours = $rec_stmt->fetchAll();

$categories = $pdo->query("
    SELECT c.*, COUNT(t.id) as tour_count 
    FROM categories c 
    LEFT JOIN tours t ON c.id = t.category_id AND t.status = 1 
    GROUP BY c.id
    ORDER BY tour_count DESC
")->fetchAll();
?>

<main class="bg-[#f8fafc] min-h-screen pb-20">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <div class="flex flex-col lg:flex-row gap-10">
            <aside class="w-full lg:w-1/4">
                <div class="sticky top-28 space-y-8">
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-white">
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] text-blue-600 mb-8 flex items-center">
                            <span class="w-6 h-1 bg-blue-600 rounded-full mr-3"></span> Bộ lọc tìm kiếm
                        </h3>

                        <form action="" method="GET" id="filterForm" class="space-y-8">
                            <div class="space-y-3">
                                <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Từ khóa</label>
                                <div class="relative">
                                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Nhập tên tour..." 
                                           class="tour-search-input w-full pl-10 pr-4 py-3.5 bg-slate-50 border-0 rounded-2xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500"
                                           autocomplete="off">
                                    <i class="fas fa-search absolute left-4 top-4 text-slate-300"></i>
                                    <div class="search-suggestions absolute top-full left-0 w-full bg-white mt-2 rounded-2xl shadow-xl z-50 hidden overflow-hidden border border-slate-100"></div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Danh mục</label>
                                <select name="category" class="w-full p-3.5 bg-slate-50 border-0 rounded-2xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
                                    <option value="">Tất cả danh mục</option>
                                    <?php foreach($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= $cat_id == $cat['id'] ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="space-y-3">
                                <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Ngày khởi hành</label>
                                <div class="relative">
                                    <input type="date" name="departure_date" value="<?= htmlspecialchars($departure_date_filter) ?>" 
                                           class="w-full pl-10 pr-4 py-3.5 bg-slate-50 border-0 rounded-2xl text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500 uppercase">
                                    <i class="far fa-calendar-alt absolute left-4 top-4 text-slate-300"></i>
                                </div>
                            </div>

                            <div class="space-y-5">
                                <div class="flex justify-between items-center">
                                    <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Khoảng giá</label>
                                    <span class="text-[10px] font-black text-blue-600 uppercase italic" id="priceLabel"></span>
                                </div>
                                <input type="range" id="priceRange" min="<?= $db_min ?>" max="<?= $db_max ?>" value="<?= $max_price ?>" step="500000"
                                       class="w-full h-1.5 bg-slate-100 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                <input type="hidden" name="max_price" id="maxPriceInput" value="<?= $max_price ?>">
                                <div class="flex justify-between text-[9px] font-black text-slate-300 uppercase">
                                    <span><?= number_format($db_min) ?>đ</span>
                                    <span><?= number_format($db_max) ?>đ</span>
                                </div>
                            </div>

                            <div class="pt-4 flex gap-2">
                                <button type="submit" class="flex-1 bg-slate-900 text-white py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-xl hover:bg-blue-600 transition-all">Lọc kết quả</button>
                                <a href="tours.php" class="w-12 h-12 flex items-center justify-center bg-slate-100 text-slate-400 rounded-2xl hover:bg-red-50 hover:text-red-500 transition-all"><i class="fas fa-undo-alt text-xs"></i></a>
                            </div>
                        </form>
                    </div>

                    <div class="bg-blue-600 rounded-[2.5rem] p-8 text-white shadow-2xl shadow-blue-200 relative overflow-hidden group">
                        <div class="relative z-10">
                            <h4 class="text-xl font-black italic tracking-tighter mb-2 leading-none">Hotline hỗ trợ</h4>
                            <p class="text-[10px] font-bold text-blue-100 uppercase tracking-widest mb-6 italic opacity-70">Tư vấn miễn phí 24/7</p>
                            <a href="tel:0777454550" class="text-2xl font-black tracking-tighter">0777454550</a>
                        </div>
                        <i class="fas fa-headset absolute -bottom-6 -right-6 text-9xl text-white/10 -rotate-12 transition-transform duration-700 group-hover:rotate-0"></i>
                    </div>
                </div>
            </aside>

            <section class="w-full lg:w-3/4">
                <div class="flex justify-between items-center mb-10">
                    <h2 class="text-2xl font-black text-slate-900 uppercase italic tracking-tighter">Danh sách tour du lịch</h2>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-white px-4 py-2 rounded-full border border-slate-100 shadow-sm">Tìm thấy <?= $total_rows ?> kết quả</span>
                </div>

                <?php if (empty($tours)): ?>
                    <div class="bg-white rounded-[3rem] p-20 text-center border border-slate-100">
                        <div class="w-20 h-20 bg-slate-50 text-slate-200 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl"><i class="fas fa-search"></i></div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic">Không tìm thấy chuyến đi phù hợp</p>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <?php foreach($tours as $t): ?>
                    <div class="bg-white rounded-[2.5rem] overflow-hidden border border-slate-50 hover:shadow-2xl transition-all duration-500 group relative">
                        <div class="relative h-60 overflow-hidden">
                            <img src="assets/uploads/<?= $t['image'] ?: 'default-tour.jpg' ?>" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                            <div class="absolute top-6 left-6 bg-white/90 backdrop-blur px-4 py-1.5 rounded-xl text-[9px] font-black text-blue-600 shadow-sm uppercase italic tracking-widest">
                                <i class="fas fa-clock mr-1"></i> <?= $t['duration'] ?>
                            </div>
                        </div>
                        <div class="p-8">
                            <span class="text-[9px] font-black text-slate-300 uppercase tracking-[0.3em] italic leading-none mb-2 block"><?= $t['cat_name'] ?></span>
                            <h3 class="text-lg font-bold text-slate-800 leading-tight mb-4 min-h-[3rem] line-clamp-2"><?= htmlspecialchars($t['title']) ?></h3>
                            
                            <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                                <?php
                                $p_child = $t['price_child'] ?? 0;
                                $p_adult = $t['price_base'];
                                $p_senior = $t['price_infant'] ?? 0;
                                
                                $all_prices = array_filter([$p_child, $p_adult, $p_senior]);
                                $min_p = min($all_prices);
                                $max_p = max($all_prices);
                                ?>
                                <div class="flex flex-col">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1 italic">Giá tour từ</p>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xl font-black text-blue-600 tracking-tighter"><?= number_format($min_p, 0, ',', '.') ?>đ</span>
                                        <span class="text-slate-300 font-black">-</span>
                                        <span class="text-xl font-black text-blue-600 tracking-tighter"><?= number_format($max_p, 0, ',', '.') ?>đ</span>
                                    </div>
                                </div>
                                <a href="tour-detail.php?id=<?= $t['id'] ?>" class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-slate-200 hover:bg-blue-600 transition-all">Chi tiết</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="mt-16 flex justify-center space-x-3">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>&category=<?= $cat_id ?>&search=<?= urlencode($search) ?>&departure_date=<?= urlencode($departure_date_filter) ?>&max_price=<?= $max_price ?>" 
                           class="w-12 h-12 flex items-center justify-center rounded-2xl text-xs font-black transition-all <?= $i == $page ? 'bg-blue-600 text-white shadow-xl shadow-blue-100' : 'bg-white text-slate-400 border border-slate-100 hover:text-blue-600' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>

                <!-- PHẦN GỢI Ý TOUR TƯƠNG TỰ -->
                <?php if (!empty($recommended_tours)): ?>
                <div class="mt-24">
                    <div class="flex items-center gap-4 mb-10">
                        <div class="h-px flex-1 bg-slate-200"></div>
                        <h3 class="text-lg font-black text-slate-400 uppercase italic tracking-widest">Có thể bạn cũng thích</h3>
                        <div class="h-px flex-1 bg-slate-200"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php foreach($recommended_tours as $rt): ?>
                        <a href="tour-detail.php?id=<?= $rt['id'] ?>" class="bg-white p-4 rounded-[2rem] border border-slate-50 hover:shadow-xl transition-all group flex flex-col">
                            <div class="relative h-40 rounded-[1.5rem] overflow-hidden mb-4">
                                <img src="assets/uploads/<?= $rt['image'] ?: 'default-tour.jpg' ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                <div class="absolute top-3 left-3 bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-[8px] font-black text-blue-600 uppercase tracking-widest shadow-sm">
                                    <?= $rt['cat_name'] ?>
                                </div>
                            </div>
                            <div class="px-2 flex-1">
                                <h4 class="text-xs font-black text-slate-800 uppercase italic leading-tight mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                    <?= htmlspecialchars($rt['title']) ?>
                                </h4>
                                
                                <div class="flex items-center justify-between mt-auto pt-3 border-t border-slate-50">
                                    <div>
                                        <p class="text-[8px] font-bold text-slate-400 uppercase italic leading-none mb-1">Giá từ</p>
                                        <p class="text-xs font-black text-blue-600 tracking-tighter"><?= number_format($rt['price_base'], 0, ',', '.') ?>đ</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[8px] font-bold text-slate-400 uppercase italic leading-none mb-1">Khởi hành</p>
                                        <p class="text-[9px] font-black text-slate-700 uppercase tracking-tighter italic leading-none truncate max-w-[80px]"><?= $rt['departure_location'] ?></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>

<script>
    const range = document.getElementById('priceRange');
    const label = document.getElementById('priceLabel');
    const input = document.getElementById('maxPriceInput');

    function updatePrice(val) {
        label.innerText = `${parseInt(val).toLocaleString('vi-VN')}đ`;
        input.value = val;
    }

    range.addEventListener('input', (e) => updatePrice(e.target.value));
    updatePrice(range.value);
</script>

<?php include 'footer.php'; ?>