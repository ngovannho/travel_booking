<?php
require_once '../config.php';
include 'header.php';

$user_id = $_SESSION['user']['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$curr_user = $stmt->fetch();
?>

<div class="mb-8">
    <h3 class="text-2xl font-black text-slate-800 uppercase italic tracking-tighter">Hồ sơ cá nhân</h3>
    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Cập nhật thông tin quản trị viên</p>
</div>

<div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-100 max-w-4xl">
    <form action="profile_process.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
            <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Họ và tên</label>
            <input type="text" name="fullname" value="<?= htmlspecialchars($curr_user['fullname']) ?>" class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="space-y-2">
            <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Email</label>
            <input type="email" value="<?= $curr_user['email'] ?>" readonly class="w-full p-4 bg-slate-100 border-0 rounded-2xl text-xs font-black text-slate-400 cursor-not-allowed">
        </div>
        <div class="space-y-2">
            <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Số điện thoại</label>
            <input type="text" name="phone" value="<?= $curr_user['phone'] ?>" class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="space-y-2">
            <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Mật khẩu mới</label>
            <input type="password" name="new_password" placeholder="Bỏ trống nếu không đổi" class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="md:col-span-2 pt-4">
            <button type="submit" class="w-full md:w-auto bg-slate-900 text-white px-10 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-blue-600 shadow-xl transition-all">Lưu thay đổi</button>
        </div>
    </form>
</div>

<?php if(isset($_GET['success'])): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: '<span class="text-sm font-black uppercase italic">Cập nhật thành công!</span>',
        confirmButtonColor: '#2563eb',
        customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl px-8 font-black uppercase text-[10px]' }
    });
</script>
<?php endif; ?>

</main></div></div></body></html>