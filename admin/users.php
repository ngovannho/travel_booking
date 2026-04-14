<?php 
require_once '../config.php';
include 'header.php'; 

$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(*) FROM users WHERE fullname LIKE ? OR email LIKE ?";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute(["%$search%", "%$search%"]);
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT * FROM users WHERE fullname LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$search%", "%$search%"]);
$users = $stmt->fetchAll();
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <h3 class="text-xl font-black text-gray-800 uppercase tracking-tight">Thành viên hệ thống</h3>
    <button onclick="openUserModal('add')" class="w-full sm:w-auto bg-blue-600 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 transition flex items-center justify-center">
        <i class="fas fa-user-plus mr-2"></i> Thêm thành viên
    </button>
</div>

<div class="mb-6">
    <form method="GET" class="relative w-full">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm kiếm tên, email..." 
               class="w-full pl-12 pr-4 py-4 bg-white border-0 shadow-sm rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none text-sm">
        <span class="absolute left-4 top-4 text-gray-400"><i class="fas fa-search fa-lg"></i></span>
    </form>
</div>

<div class="block md:hidden space-y-4">
    <?php foreach($users as $user): ?>
    <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 relative">
        <div class="flex items-center mb-4">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['fullname']) ?>&background=random" class="w-12 h-12 rounded-2xl mr-4 border">
            <div class="flex-1 min-w-0">
                <div class="text-base font-bold text-gray-900 truncate"><?= htmlspecialchars($user['fullname']) ?></div>
                <div class="text-xs text-gray-500 truncate"><?= htmlspecialchars($user['email']) ?></div>
            </div>
            <div class="text-right">
                <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase <?= $user['role'] == 'admin' ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600' ?>">
                    <?= $user['role'] ?>
                </span>
            </div>
        </div>
        <div class="flex items-center justify-between pt-4 border-t border-dashed border-gray-100">
            <div class="text-sm font-medium text-gray-600 tracking-tighter">ID: <?= $user['id'] ?></div>
            <div class="flex space-x-2">
                <button onclick='openUserModal("edit", <?= json_encode($user) ?>)' class="w-10 h-10 flex items-center justify-center bg-gray-50 text-blue-600 rounded-xl">
                    <i class="fas fa-edit"></i>
                </button>
                <?php if($user['id'] != $_SESSION['user']['id']): ?>
                <button onclick="confirmDelete(<?= $user['id'] ?>)" class="w-10 h-10 flex items-center justify-center bg-red-50 text-red-600 rounded-xl">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="hidden md:block bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left">
        <thead>
            <tr class="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-widest border-b border-gray-100">
                <th class="px-8 py-5">ID</th>
                <th class="px-8 py-5">Thành viên</th>
                <th class="px-8 py-5">SĐT</th>
                <th class="px-8 py-5">Vai trò</th>
                <th class="px-8 py-5 text-center">Thao tác</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach($users as $user): ?>
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-8 py-5 text-sm font-bold text-gray-300"><?= $user['id'] ?></td>
                <td class="px-8 py-5">
                    <div class="flex items-center">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['fullname']) ?>&background=random" class="w-10 h-10 rounded-xl mr-4">
                        <div>
                            <div class="text-sm font-bold text-gray-900 leading-tight"><?= htmlspecialchars($user['fullname']) ?></div>
                            <div class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars($user['email']) ?></div>
                        </div>
                    </div>
                </td>
                <td class="px-8 py-5 text-sm font-medium text-gray-600"><?= $user['phone'] ?: 'N/A' ?></td>
                <td class="px-8 py-5">
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase border <?= $user['role'] == 'admin' ? 'bg-purple-50 text-purple-600 border-purple-100' : 'bg-blue-50 text-blue-600 border-blue-100' ?>">
                        <?= $user['role'] ?>
                    </span>
                </td>
                <td class="px-8 py-5">
                    <div class="flex justify-center space-x-3">
                        <button onclick='openUserModal("edit", <?= json_encode($user) ?>)' class="text-blue-500 hover:text-blue-700"><i class="fas fa-edit"></i></button>
                        <?php if($user['id'] != $_SESSION['user']['id']): ?>
                        <button onclick="confirmDelete(<?= $user['id'] ?>)" class="text-red-400 hover:text-red-600"><i class="fas fa-trash-alt"></i></button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if($total_pages > 1): ?>
<div class="mt-8 flex justify-center items-center space-x-2">
    <?php for($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= $search ?>" 
           class="w-12 h-12 flex items-center justify-center rounded-2xl text-sm font-bold transition <?= $i == $page ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'bg-white text-gray-400 hover:text-blue-600 border border-gray-100' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<div id="userModal" class="fixed inset-0 bg-slate-900/60 z-[70] hidden flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden transform transition-all scale-95 opacity-0 duration-300" id="modalContent">
        <div class="p-8 border-b border-gray-50 flex justify-between items-center">
            <h4 id="modalTitle" class="text-xl font-black text-gray-800 uppercase tracking-tight"></h4>
            <button onclick="closeModal()" class="w-10 h-10 flex items-center justify-center bg-gray-50 text-gray-400 rounded-full hover:text-red-500 transition-colors">&times;</button>
        </div>
        <form id="userForm" action="user_process.php" method="POST" class="p-8 space-y-5">
            <input type="hidden" name="id" id="u_id">
            <input type="hidden" name="action" id="u_action">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Họ và tên</label>
                    <input type="text" name="fullname" id="u_fullname" required class="w-full p-4 bg-gray-50 border border-transparent rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Tên đăng nhập</label>
                    <input type="text" name="username" id="u_username" required class="w-full p-4 bg-gray-50 border border-transparent rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Email liên hệ</label>
                <input type="email" name="email" id="u_email" required class="w-full p-4 bg-gray-50 border border-transparent rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Số điện thoại</label>
                    <input type="text" name="phone" id="u_phone" class="w-full p-4 bg-gray-50 border border-transparent rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Vai trò</label>
                    <select name="role" id="u_role" class="w-full p-4 bg-gray-50 border border-transparent rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition appearance-none">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>

            <div id="passwordField" class="space-y-2">
                <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Mật khẩu mới</label>
                <input type="password" name="password" id="u_password" class="w-full p-4 bg-gray-50 border border-transparent rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="Để trống nếu không đổi">
            </div>

            <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-600 transition-all duration-300 shadow-xl shadow-slate-200 mt-4">
                Lưu cấu hình
            </button>
        </form>
    </div>
</div>

<script>
const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true });

<?php if(isset($_SESSION['success'])): ?>
    Toast.fire({ icon: 'success', title: '<?= $_SESSION['success'] ?>' });
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

function openUserModal(mode, data = null) {
    const modal = document.getElementById('userModal');
    const content = document.getElementById('modalContent');
    document.getElementById('u_action').value = mode;
    document.getElementById('modalTitle').innerText = mode === 'add' ? 'Thành viên mới' : 'Cập nhật tài khoản';
    
    if(data) {
        document.getElementById('u_id').value = data.id;
        document.getElementById('u_fullname').value = data.fullname;
        document.getElementById('u_username').value = data.username;
        document.getElementById('u_email').value = data.email;
        document.getElementById('u_phone').value = data.phone;
        document.getElementById('u_role').value = data.role;
        document.getElementById('u_password').placeholder = "Để trống nếu không đổi";
        document.getElementById('u_username').readOnly = true;
    } else {
        document.getElementById('userForm').reset();
        document.getElementById('u_username').readOnly = false;
        document.getElementById('u_password').placeholder = "Nhập mật khẩu";
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
    setTimeout(() => { document.getElementById('userModal').classList.add('hidden'); }, 300);
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Xác nhận?',
        text: "Tài khoản sẽ bị xóa vĩnh viễn!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0f172a',
        cancelButtonColor: '#f1f5f9',
        confirmButtonText: '<span class="text-white">Xóa</span>',
        cancelButtonText: '<span class="text-slate-900">Hủy</span>',
        customClass: { popup: 'rounded-[2rem]' }
    }).then((result) => {
        if (result.isConfirmed) window.location.href = 'user_process.php?action=delete&id=' + id;
    });
}
</script>

</main></div></div></body></html>