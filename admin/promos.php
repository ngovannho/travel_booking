<?php 
require_once '../config.php';
include 'header.php'; 

$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(*) FROM promos WHERE code LIKE ? OR description LIKE ?";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute(["%$search%", "%$search%"]);
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT * FROM promos WHERE code LIKE ? OR description LIKE ? ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$search%", "%$search%"]);
$promos = $stmt->fetchAll();
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h3 class="text-2xl font-black text-slate-800 uppercase italic tracking-tighter">Mã Giảm Giá VIP</h3>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Quản lý kho mã ưu đãi cho khách hàng thân thiết</p>
    </div>
    <button onclick="openPromoModal('add')" class="bg-blue-600 text-white px-8 py-4 rounded-2xl font-black text-sm shadow-xl shadow-blue-100 hover:bg-slate-900 transition-all duration-300">
        <i class="fas fa-plus-circle mr-2"></i> THÊM MÃ MỚI
    </button>
</div>

<div class="mb-8">
    <form method="GET" class="relative w-full">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm kiếm mã hoặc mô tả..." 
               class="w-full pl-14 pr-4 py-4 bg-white border-0 shadow-sm rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-bold">
        <span class="absolute left-5 top-4 text-slate-300"><i class="fas fa-search fa-lg"></i></span>
    </form>
</div>

<div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
    <table class="w-full text-left">
        <thead>
            <tr class="bg-slate-50/50 text-slate-400 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                <th class="px-8 py-5">Mã Code</th>
                <th class="px-8 py-5">Phần trăm giảm</th>
                <th class="px-8 py-5">Ngày hết hạn</th>
                <th class="px-8 py-5 text-center">Giới hạn</th>
                <th class="px-8 py-5">Mô tả</th>
                <th class="px-8 py-5 text-center">Thao tác</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach($promos as $p): ?>
            <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-8 py-6">
                    <span class="px-4 py-2 bg-blue-50 text-blue-600 rounded-xl text-xs font-black uppercase italic tracking-widest"><?= htmlspecialchars($p['code']) ?></span>
                </td>
                <td class="px-8 py-6">
                    <div class="text-lg font-black text-emerald-500 italic leading-none">-<?= $p['percent'] ?>%</div>
                </td>
                <td class="px-8 py-6">
                    <?php if($p['expiry_date']): ?>
                        <span class="text-[10px] font-bold <?= strtotime($p['expiry_date']) < time() ? 'text-red-500' : 'text-slate-600' ?>">
                            <i class="far fa-calendar-times mr-1"></i> <?= date('d/m/Y', strtotime($p['expiry_date'])) ?>
                        </span>
                    <?php else: ?>
                        <span class="text-[10px] text-slate-300 italic">Vĩnh viễn</span>
                    <?php endif; ?>
                </td>
                <td class="px-8 py-6">
                    <span class="text-[10px] font-bold text-slate-600">
                        <?= $p['usage_limit'] > 0 ? $p['usage_limit'] . ' lượt' : 'Vô hạn' ?>
                    </span>
                </td>
                <td class="px-8 py-6">
                    <p class="text-xs font-medium text-slate-500 italic"><?= htmlspecialchars($p['description']) ?></p>
                </td>
                <td class="px-8 py-6 text-center">
                    <div class="flex justify-center gap-2">
                        <button onclick='openPromoModal("edit", <?= json_encode($p) ?>)' class="w-10 h-10 flex items-center justify-center bg-blue-50 text-blue-500 rounded-xl hover:bg-blue-500 hover:text-white transition-all"><i class="fas fa-edit"></i></button>
                        <button onclick="confirmDelete(<?= $p['id'] ?>)" class="w-10 h-10 flex items-center justify-center bg-red-50 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition-all"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($promos)): ?>
                <tr>
                    <td colspan="6" class="px-8 py-20 text-center">
                        <p class="text-xs font-black text-slate-300 uppercase tracking-widest italic text-center w-full block">Chưa có mã giảm giá nào trong kho</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if($total_pages > 1): ?>
<div class="mt-8 flex justify-center space-x-2">
    <?php for($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= $search ?>" class="w-10 h-10 flex items-center justify-center rounded-xl text-[10px] font-black <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-white text-slate-400 border border-slate-100' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<!-- Modal Thêm Mã -->
<div id="promoModal" class="fixed inset-0 bg-slate-900/60 z-[70] hidden flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md transform transition-all scale-95 opacity-0 duration-300" id="modalContent">
        <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-white rounded-t-[2.5rem]">
            <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Thêm mã giảm giá mới</h4>
            <button onclick="closeModal()" class="w-10 h-10 flex items-center justify-center bg-slate-50 text-slate-400 rounded-full hover:text-red-500 transition-colors">&times;</button>
        </div>
        <form action="promo_process.php" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="id" id="p_id">
            <input type="hidden" name="action" id="p_action">
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Mã Code (VIP)</label>
                <input type="text" name="code" id="p_code" required placeholder="Ví dụ: VIP50" class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500 uppercase">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Phần trăm giảm (%)</label>
                    <input type="number" name="percent" id="p_percent" required min="1" max="100" placeholder="35" class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Ngày hết hạn</label>
                    <input type="date" name="expiry_date" id="p_expiry_date" class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Số lượng tối đa</label>
                    <input type="number" name="usage_limit" id="p_usage_limit" min="0" placeholder="Để trống = Vô hạn" class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Mô tả ngắn</label>
                <textarea name="description" id="p_description" rows="3" required placeholder="Ưu đãi dành cho..." class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-medium outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            </div>
            <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl shadow-slate-200 mt-4 text-[10px]">
                XÁC NHẬN LƯU MÃ
            </button>
        </form>
    </div>
</div>

<script>
const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
<?php if(isset($_SESSION['success'])): ?> Toast.fire({ icon: 'success', title: '<?= $_SESSION['success'] ?>' }); <?php unset($_SESSION['success']); endif; ?>
<?php if(isset($_SESSION['error'])): ?> Toast.fire({ icon: 'error', title: '<?= $_SESSION['error'] ?>' }); <?php unset($_SESSION['error']); endif; ?>

function openPromoModal(mode, data = null) {
    const modal = document.getElementById('promoModal');
    const content = document.getElementById('modalContent');
    document.getElementById('p_action').value = mode;
    document.querySelector('#modalContent h4').innerText = mode === 'add' ? 'Thêm mã giảm giá mới' : 'Chỉnh sửa mã giảm giá';

    if(data) {
        document.getElementById('p_id').value = data.id;
        document.getElementById('p_code').value = data.code;
        document.getElementById('p_percent').value = data.percent;
        document.getElementById('p_expiry_date').value = data.expiry_date || '';
        document.getElementById('p_usage_limit').value = data.usage_limit || '';
        document.getElementById('p_description').value = data.description;
    } else {
        document.getElementById('p_id').value = '';
        document.getElementById('p_code').value = '';
        document.getElementById('p_percent').value = '';
        document.getElementById('p_expiry_date').value = '';
        document.getElementById('p_usage_limit').value = '';
        document.getElementById('p_description').value = '';
    }

    modal.classList.remove('hidden');
    setTimeout(() => { content.classList.remove('scale-95', 'opacity-0'); content.classList.add('scale-100', 'opacity-100'); }, 10);
}
function closeModal() {
    const content = document.getElementById('modalContent');
    content.classList.remove('scale-100', 'opacity-100'); content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => { document.getElementById('promoModal').classList.add('hidden'); }, 300);
}
function confirmDelete(id) {
    Swal.fire({ title: '<span class="text-sm font-black uppercase italic tracking-widest">Xóa mã này?</span>', text: "Hành động này không thể hoàn tác!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#64748b', confirmButtonText: 'Xóa ngay', cancelButtonText: 'Hủy bỏ', customClass: { popup: 'rounded-[2.5rem]', confirmButton: 'rounded-xl px-8 font-black uppercase text-[10px]', cancelButton: 'rounded-xl px-8 font-black uppercase text-[10px]' }
    }).then((result) => { if (result.isConfirmed) window.location.href = `promo_process.php?action=delete&id=${id}`; });
}
</script>
</main></div></div></body></html>