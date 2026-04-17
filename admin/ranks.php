<?php 
require_once '../config.php';
include 'header.php'; 

$ranks = $pdo->query("SELECT * FROM ranks ORDER BY min_points ASC")->fetchAll();
$promos = $pdo->query("SELECT code FROM promos ORDER BY code ASC")->fetchAll();
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h3 class="text-2xl font-black text-slate-800 uppercase italic tracking-tighter">Cấu hình Hạng Thành Viên</h3>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Quản lý mốc điểm và ưu đãi thăng hạng</p>
    </div>
</div>

<div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
    <table class="w-full text-left">
        <thead>
            <tr class="bg-slate-50/50 text-slate-400 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                <th class="px-8 py-5">Tên Hạng</th>
                <th class="px-8 py-5">Biểu tượng</th>
                <th class="px-8 py-5">Mốc điểm tối thiểu</th>
                <th class="px-8 py-5">Mã giảm giá thăng hạng</th>
                <th class="px-8 py-5 text-center">Thao tác</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach($ranks as $r): ?>
            <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-8 py-6">
                    <span class="text-xs font-black uppercase <?= $r['color'] ?>"><?= htmlspecialchars($r['name']) ?></span>
                </td>
                <td class="px-8 py-6">
                    <i class="fas <?= $r['icon'] ?> <?= $r['color'] ?> fa-lg"></i>
                </td>
                <td class="px-8 py-6">
                    <div class="text-sm font-black text-slate-700"><?= number_format($r['min_points']) ?> <span class="text-[10px] text-slate-400">điểm</span></div>
                </td>
                <td class="px-8 py-6">
                    <?php if($r['rank_up_promo_code']): ?>
                        <span class="px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-lg text-[10px] font-black uppercase italic tracking-widest"><?= htmlspecialchars($r['rank_up_promo_code']) ?></span>
                    <?php else: ?>
                        <span class="text-[10px] text-slate-300 italic">Chưa cấu hình</span>
                    <?php endif; ?>
                </td>
                <td class="px-8 py-6 text-center">
                    <button onclick='openRankModal(<?= json_encode($r) ?>)' class="w-10 h-10 inline-flex items-center justify-center bg-blue-50 text-blue-500 rounded-xl hover:bg-blue-500 hover:text-white transition-all"><i class="fas fa-edit"></i></button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="rankModal" class="fixed inset-0 bg-slate-900/60 z-[70] hidden flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md transform transition-all scale-95 opacity-0 duration-300" id="modalContent">
        <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-white rounded-t-[2.5rem]">
            <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Chỉnh sửa cấu hình hạng</h4>
            <button onclick="closeModal()" class="w-10 h-10 flex items-center justify-center bg-slate-50 text-slate-400 rounded-full hover:text-red-500 transition-colors">&times;</button>
        </div>
        <form action="rank_process.php" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="id" id="r_id">
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Tên hạng</label>
                <input type="text" name="name" id="r_name" required class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Mốc điểm tối thiểu</label>
                    <input type="number" name="min_points" id="r_min_points" required min="0" class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Icon (FontAwesome)</label>
                    <input type="text" name="icon" id="r_icon" class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Mã màu (Tailwind Class)</label>
                <input type="text" name="color" id="r_color" class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase text-slate-400 ml-1">Mã giảm giá thăng hạng</label>
                <select name="rank_up_promo_code" id="r_promo" class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
                    <option value="">-- Không tặng mã --</option>
                    <?php foreach($promos as $p): ?>
                        <option value="<?= $p['code'] ?>"><?= $p['code'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl shadow-slate-200 mt-4 text-[10px]">
                LƯU THÔNG TIN HẠNG
            </button>
        </form>
    </div>
</div>

<script>
const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
<?php if(isset($_SESSION['success'])): ?> Toast.fire({ icon: 'success', title: '<?= $_SESSION['success'] ?>' }); <?php unset($_SESSION['success']); endif; ?>
<?php if(isset($_SESSION['error'])): ?> Toast.fire({ icon: 'error', title: '<?= $_SESSION['error'] ?>' }); <?php unset($_SESSION['error']); endif; ?>

function openRankModal(data) {
    const modal = document.getElementById('rankModal');
    const content = document.getElementById('modalContent');
    document.getElementById('r_id').value = data.id;
    document.getElementById('r_name').value = data.name;
    document.getElementById('r_min_points').value = data.min_points;
    document.getElementById('r_icon').value = data.icon;
    document.getElementById('r_color').value = data.color;
    document.getElementById('r_promo').value = data.rank_up_promo_code || '';
    modal.classList.remove('hidden');
    setTimeout(() => { content.classList.remove('scale-95', 'opacity-0'); content.classList.add('scale-100', 'opacity-100'); }, 10);
}
function closeModal() {
    const content = document.getElementById('modalContent');
    content.classList.remove('scale-100', 'opacity-100'); content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => { document.getElementById('rankModal').classList.add('hidden'); }, 300);
}
</script>
</main></div></div></body></html>