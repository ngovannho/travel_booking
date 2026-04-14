<?php 
require_once '../config.php';
include 'header.php'; 

$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(*) FROM tours WHERE title LIKE ?";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute(["%$search%"]);
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT t.*, c.name as cat_name FROM tours t LEFT JOIN categories c ON t.category_id = c.id WHERE t.title LIKE ? ORDER BY t.id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$search%"]);
$tours = $stmt->fetchAll();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

<style>
    .cke_notification { display: none !important; }
    .cke_chrome { border-radius: 1rem !important; border: 1px solid #f1f5f9 !important; }
    #modalContent { max-height: 90vh; display: flex; flex-direction: column; width: 100%; max-width: 600px; }
    .modal-body { overflow-y: auto; flex: 1; scrollbar-width: thin; }
</style>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Hệ thống Tour</h3>
        <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Quản lý hành trình du lịch</p>
    </div>
    <button onclick="openTourModal('add')" class="bg-blue-600 text-white px-8 py-4 rounded-2xl font-black text-sm shadow-xl shadow-blue-100 hover:bg-slate-900 transition-all duration-300">
        <i class="fas fa-plus-circle mr-2"></i> THÊM TOUR MỚI
    </button>
</div>

<div class="mb-8">
    <form method="GET" class="relative w-full">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm kiếm tên chuyến đi..." 
               class="w-full pl-14 pr-4 py-4 bg-white border-0 shadow-sm rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-bold">
        <span class="absolute left-5 top-4 text-slate-300"><i class="fas fa-search fa-lg"></i></span>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-1 gap-4">
    <div class="block lg:hidden space-y-4">
        <?php foreach($tours as $t): ?>
        <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100">
            <div class="flex gap-4">
                <img src="../assets/uploads/<?= $t['image'] ?: 'default.jpg' ?>" class="w-20 h-20 rounded-2xl object-cover border-4 border-slate-50">
                <div class="flex-1 min-w-0">
                    <span class="text-[9px] font-black text-blue-600 uppercase bg-blue-50 px-2 py-1 rounded-lg"><?= $t['cat_name'] ?></span>
                    <div class="text-sm font-bold text-slate-900 truncate mt-1"><?= htmlspecialchars($t['title']) ?></div>
                    <div class="text-base font-black text-blue-600 mt-1"><?= number_format($t['price_base'], 0, ',', '.') ?>đ</div>
                </div>
            </div>
            <div class="flex items-center justify-between mt-5 pt-4 border-t border-dashed border-slate-100">
                <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest italic"><?= $t['duration'] ?></div>
                <div class="flex space-x-2">
                    <button onclick='openTourModal("edit", <?= json_encode($t) ?>)' class="w-10 h-10 flex items-center justify-center bg-slate-50 text-blue-600 rounded-xl"><i class="fas fa-edit"></i></button>
                    <button onclick="confirmDelete(<?= $t['id'] ?>)" class="w-10 h-10 flex items-center justify-center bg-red-50 text-red-500 rounded-xl"><i class="fas fa-trash-alt"></i></button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="hidden lg:block bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50/50 text-slate-400 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                    <th class="px-8 py-6">Ảnh</th>
                    <th class="px-8 py-6">Chuyến đi</th>
                    <th class="px-8 py-6 text-center">Giá niêm yết</th>
                    <th class="px-8 py-6 text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach($tours as $t): ?>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-8 py-6"><img src="../assets/uploads/<?= $t['image'] ?: 'default.jpg' ?>" class="w-16 h-12 rounded-xl object-cover shadow-sm"></td>
                    <td class="px-8 py-6">
                        <div class="text-sm font-bold text-slate-900 leading-tight"><?= htmlspecialchars($t['title']) ?></div>
                        <div class="text-[9px] text-blue-500 font-black mt-1 uppercase tracking-tighter"><?= $t['cat_name'] ?> | <?= $t['duration'] ?></div>
                        <div class="text-[9px] text-slate-400 mt-1 uppercase">Lịch: <?= htmlspecialchars($t['departure_dates'] ?? 'Chưa cập nhật') ?></div>
                    </td>
                    <td class="px-8 py-6 text-center">
                        <div class="text-sm font-black text-blue-600"><?= number_format($t['price_base'], 0, ',', '.') ?>đ</div>
                        <div class="text-[9px] text-slate-400 font-bold">NCT: <?= number_format($t['price_infant'] ?? 0, 0, ',', '.') ?>đ | Trẻ: <?= number_format($t['price_child'] ?? 0, 0, ',', '.') ?>đ</div>
                        <?php if($t['discount_code']): ?><div class="text-[9px] text-emerald-500 font-black mt-1 uppercase">Code: <?= $t['discount_code'] ?> (-<?= $t['discount_percent'] ?>%)</div><?php endif; ?>
                    </td>
                    <td class="px-8 py-6 text-center">
                        <div class="flex justify-center space-x-3">
                            <button onclick='openTourModal("edit", <?= json_encode($t) ?>)' class="text-slate-400 hover:text-blue-600"><i class="fas fa-edit"></i></button>
                            <button onclick="confirmDelete(<?= $t['id'] ?>)" class="text-slate-400 hover:text-red-500"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if($total_pages > 1): ?>
<div class="mt-8 flex justify-center space-x-2">
    <?php for($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= $search ?>" class="w-10 h-10 flex items-center justify-center rounded-xl text-xs font-black transition-all <?= $i == $page ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'bg-white text-slate-400 border border-slate-100' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<div id="tourModal" class="fixed inset-0 bg-slate-900/60 z-[70] hidden flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-[2rem] shadow-2xl transform transition-all scale-95 opacity-0 duration-300" id="modalContent">
        <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-white rounded-t-[2rem]">
            <h4 id="modalTitle" class="text-sm font-black text-slate-800 uppercase tracking-widest"></h4>
            <button onclick="closeModal()" class="w-8 h-8 flex items-center justify-center bg-slate-50 text-slate-400 rounded-lg hover:text-red-500">&times;</button>
        </div>
        
        <form id="tourForm" action="tour_process.php" method="POST" enctype="multipart/form-data" class="flex flex-col overflow-hidden">
            <div class="modal-body p-6 space-y-5">
                <input type="hidden" name="id" id="t_id">
                <input type="hidden" name="action" id="t_action">
                
                <div class="space-y-1">
                    <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Tên chuyến đi</label>
                    <input type="text" name="title" id="t_title" required class="w-full p-3.5 bg-slate-50 border-0 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 font-bold text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Danh mục</label>
                        <select name="category_id" id="t_category_id" class="w-full p-3.5 bg-slate-50 border-0 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-xs font-bold">
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Giá Người lớn (Gốc)</label>
                        <input type="text" id="t_price_display" required class="w-full p-3.5 bg-slate-50 border-0 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-sm font-black text-blue-600">
                        <input type="hidden" name="price_base" id="t_price_base">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Giá Trẻ em</label>
                        <input type="text" id="t_price_child_display" class="w-full p-3.5 bg-slate-50 border-0 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold">
                        <input type="hidden" name="price_child" id="t_price_child">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Giá Người cao tuổi</label>
                        <input type="text" id="t_price_infant_display" class="w-full p-3.5 bg-slate-50 border-0 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold">
                        <input type="hidden" name="price_infant" id="t_price_infant">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Ngày khởi hành (Cách nhau dấu phẩy)</label>
                        <input type="text" name="departure_dates" id="t_dates" placeholder="15/10, 20/10, 25/10..." class="w-full p-3.5 bg-slate-50 border-0 rounded-xl outline-none text-xs font-bold">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Số chỗ tối đa</label>
                        <input type="number" name="max_people" id="t_max" placeholder="30" class="w-full p-3.5 bg-slate-50 border-0 rounded-xl outline-none text-xs font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Mã giảm giá (Nếu có)</label>
                        <input type="text" name="discount_code" id="t_discount_code" placeholder="Ví dụ: SUMMER20" class="w-full p-3.5 bg-slate-50 border-0 rounded-xl outline-none text-xs font-bold">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Phần trăm giảm (%)</label>
                        <input type="number" name="discount_percent" id="t_discount_percent" min="0" max="100" placeholder="10" class="w-full p-3.5 bg-slate-50 border-0 rounded-xl outline-none text-xs font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Thời gian & Điểm đi</label>
                        <div class="flex gap-2">
                            <input type="text" name="duration" id="t_duration" placeholder="3N2Đ" class="w-1/2 p-3.5 bg-slate-50 border-0 rounded-xl outline-none text-xs font-bold">
                            <input type="text" name="departure_location" id="t_departure" placeholder="Hà Nội" class="w-1/2 p-3.5 bg-slate-50 border-0 rounded-xl outline-none text-xs font-bold">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Ảnh bìa chính</label>
                        <input type="file" name="image" class="w-full text-[9px] mb-2">
                        
                        <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Ảnh bổ sung (Chọn nhiều)</label>
                        <input type="file" name="extra_images[]" multiple class="w-full text-[9px]">
                        <div id="current_extra_images" class="flex gap-2 mt-2 overflow-x-auto"></div>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Tóm tắt ngắn</label>
                    <textarea name="description" id="t_description" rows="2" class="w-full p-3.5 bg-slate-50 border-0 rounded-xl outline-none text-xs font-medium"></textarea>
                </div>

                <div class="space-y-1">
                    <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Lịch trình chi tiết</label>
                    <textarea name="content" id="t_content"></textarea>
                </div>
            </div>

            <div class="p-6 border-t border-slate-50 bg-slate-50 rounded-b-[2rem]">
                <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-xl font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl shadow-slate-200 text-xs">
                    LƯU THÔNG TIN TOUR
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    CKEDITOR.replace('t_content', {
        height: 180,
        filebrowserUploadUrl: 'upload_handler.php',
        filebrowserUploadMethod: 'form',
        on: {
            instanceReady: function(ev) {
                this.dataProcessor.htmlFilter.addRules({
                    elements: {
                        img: function(el) {
                            el.attributes.style = 'display:block; max-width:100%; height:auto; margin:10px auto; border-radius:1rem;';
                        }
                    }
                });
            }
        }
    });

    function setupPriceFormat(displayId, realId) {
        const display = document.getElementById(displayId);
        const real = document.getElementById(realId);
        display.addEventListener('input', function() {
            let value = this.value.replace(/[^0-9]/g, '');
            if (value) {
                real.value = value;
                this.value = parseInt(value).toLocaleString('vi-VN');
            } else {
                real.value = ''; this.value = '';
            }
        });
    }
    setupPriceFormat('t_price_display', 't_price_base');
    setupPriceFormat('t_price_child_display', 't_price_child');
    setupPriceFormat('t_price_infant_display', 't_price_infant');

    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
    <?php if(isset($_SESSION['success'])): ?>
        Toast.fire({ icon: 'success', title: '<?= $_SESSION['success'] ?>' });
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    function openTourModal(mode, data = null) {
        const modal = document.getElementById('tourModal');
        const content = document.getElementById('modalContent');
        document.getElementById('t_action').value = mode;
        document.getElementById('modalTitle').innerText = mode === 'add' ? 'Đăng Tour' : 'Sửa Tour';
        
        if(data) {
            document.getElementById('t_id').value = data.id;
            document.getElementById('t_title').value = data.title;
            document.getElementById('t_category_id').value = data.category_id;
            
            document.getElementById('t_price_base').value = data.price_base;
            document.getElementById('t_price_display').value = parseInt(data.price_base).toLocaleString('vi-VN');
            document.getElementById('t_price_child').value = data.price_child || '';
            document.getElementById('t_price_child_display').value = data.price_child ? parseInt(data.price_child).toLocaleString('vi-VN') : '';
            document.getElementById('t_price_infant').value = data.price_infant || '';
            document.getElementById('t_price_infant_display').value = data.price_infant ? parseInt(data.price_infant).toLocaleString('vi-VN') : '';
            
            document.getElementById('t_dates').value = data.departure_dates || '';
            document.getElementById('t_max').value = data.max_people || '';
            document.getElementById('t_duration').value = data.duration;
            document.getElementById('t_discount_code').value = data.discount_code || '';
            document.getElementById('t_discount_percent').value = data.discount_percent || 0;
            document.getElementById('t_departure').value = data.departure_location;
            document.getElementById('t_description').value = data.description;
            CKEDITOR.instances.t_content.setData(data.content);
        } else {
            document.getElementById('tourForm').reset();
            document.getElementById('t_price_base').value = '';
            document.getElementById('t_price_display').value = '';
            document.getElementById('t_price_child').value = '';
            document.getElementById('t_price_child_display').value = '';
            document.getElementById('t_price_infant').value = '';
            document.getElementById('t_price_infant_display').value = '';
            document.getElementById('t_discount_code').value = '';
            document.getElementById('t_discount_percent').value = 0;
            CKEDITOR.instances.t_content.setData('');
        }
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeModal() {
        const content = document.getElementById('modalContent');
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => { document.getElementById('tourModal').classList.add('hidden'); }, 300);
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Xóa Tour này?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0f172a',
            confirmButtonText: 'Đồng ý',
            customClass: { popup: 'rounded-[2rem]' }
        }).then((result) => {
            if (result.isConfirmed) window.location.href = 'tour_process.php?action=delete&id=' + id;
        });
    }
</script>

</main></div></div></body></html>