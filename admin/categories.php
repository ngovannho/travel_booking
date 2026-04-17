<?php 
require_once '../config.php';
include 'header.php'; 

$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(*) FROM categories WHERE name LIKE ?";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute(["%$search%"]);
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT * FROM categories WHERE name LIKE ? ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$search%"]);
$categories = $stmt->fetchAll();
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <h3 class="text-2xl font-bold text-gray-800">Danh mục Tour</h3>
    <button onclick="openModal('add')" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:bg-blue-700 transition flex items-center justify-center">
        <i class="fas fa-plus mr-2"></i> Thêm danh mục
    </button>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-4 border-b border-gray-100">
        <form method="GET" class="relative max-w-sm">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm kiếm danh mục..." 
                   class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm">
            <span class="absolute left-3 top-2.5 text-gray-400"><i class="fas fa-search"></i></span>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-[10px] uppercase font-bold tracking-widest">
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Tên danh mục</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3 text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach($categories as $cat): ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-xs font-medium text-gray-900"><?= $cat['id'] ?></td>
                    <td class="px-4 py-3 text-xs text-gray-700 font-bold uppercase tracking-tighter italic"><?= htmlspecialchars($cat['name']) ?></td>
                    <td class="px-4 py-3 text-[10px] text-gray-400 font-mono"><?= $cat['slug'] ?></td>
                    <td class="px-4 py-3">
                        <div class="flex justify-center space-x-2">
                            <button onclick='openModal("edit", <?= json_encode($cat) ?>)' class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md transition text-xs">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="confirmDelete(<?= $cat['id'] ?>)' class="p-1.5 text-red-600 hover:bg-red-50 rounded-md transition text-xs">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?= renderAdminPagination($page, $total_pages, $_GET) ?>
</div>

<div id="categoryModal" class="fixed inset-0 bg-black/50 z-[60] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all scale-95 opacity-0 duration-300" id="modalContent">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h4 id="modalTitle" class="text-xl font-bold text-gray-800"></h4>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        <form id="categoryForm" action="category_process.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="id" id="cat_id">
            <input type="hidden" name="action" id="cat_action">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Tên danh mục</label>
                <input type="text" name="name" id="cat_name" required onkeyup="createSlug(this.value)"
                       class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" id="cat_slug" readonly
                       class="w-full p-3 bg-gray-100 border border-gray-200 rounded-xl outline-none text-gray-500 font-mono">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold shadow-lg hover:bg-blue-700 transition">LƯU DỮ LIỆU</button>
        </form>
    </div>
</div>

<script>
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});

<?php if(isset($_SESSION['success'])): ?>
    Toast.fire({ icon: 'success', title: '<?= $_SESSION['success'] ?>' });
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('hidden');
        overlay.classList.toggle('active');
    } else {
        sidebar.classList.toggle('collapsed');
    }
}

function openModal(mode, data = null) {
    const modal = document.getElementById('categoryModal');
    const content = document.getElementById('modalContent');
    document.getElementById('cat_action').value = mode;
    document.getElementById('modalTitle').innerText = mode === 'add' ? 'Thêm danh mục mới' : 'Chỉnh sửa danh mục';
    
    if(data) {
        document.getElementById('cat_id').value = data.id;
        document.getElementById('cat_name').value = data.name;
        document.getElementById('cat_slug').value = data.slug;
    } else {
        document.getElementById('categoryForm').reset();
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
    setTimeout(() => {
        document.getElementById('categoryModal').classList.add('hidden');
    }, 300);
}

function createSlug(val) {
    let slug = val.toLowerCase().trim()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[đĐ]/g, 'd')
        .replace(/([^0-9a-z-\s])/g, '')
        .replace(/(\s+)/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-+|-+$/g, '');
    document.getElementById('cat_slug').value = slug;
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Xác nhận xóa?',
        text: "Dữ liệu sẽ không thể khôi phục!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Đồng ý',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'category_process.php?action=delete&id=' + id;
        }
    })
}
</script>