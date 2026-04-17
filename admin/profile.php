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
    <form action="profile_process.php" method="POST" enctype="multipart/form-data" class="space-y-10">
        <div class="flex flex-col items-center sm:flex-row gap-8">
            <div class="relative group">
                <?php 
                $avatar_url = $curr_user['avatar'] ? "../assets/uploads/" . $curr_user['avatar'] : "https://ui-avatars.com/api/?name=" . urlencode($curr_user['fullname']) . "&background=random";
                ?>
                <img id="avatarPreview" src="<?= $avatar_url ?>" class="w-32 h-32 rounded-[2.5rem] object-cover border-4 border-white shadow-xl transition-transform group-hover:scale-[1.02]">
                <label for="avatarInput" class="absolute -bottom-2 -right-2 bg-blue-600 text-white w-10 h-10 rounded-xl flex items-center justify-center cursor-pointer shadow-lg hover:bg-slate-900 transition-colors border-4 border-white">
                    <i class="fas fa-camera text-sm"></i>
                </label>
                <input type="file" name="avatar" id="avatarInput" class="hidden" accept="image/*" onchange="previewImage(this)">
            </div>
            <div class="text-center sm:text-left">
                <h4 class="text-lg font-black text-slate-800 uppercase italic tracking-tighter"><?= htmlspecialchars($curr_user['fullname']) ?></h4>
                <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest mt-1">Quản trị viên hệ thống</p>
                <div class="flex items-center justify-center sm:justify-start gap-2 mt-3">
                    <span class="px-3 py-1 bg-slate-50 text-slate-400 rounded-lg text-[9px] font-black uppercase border border-slate-100 italic">ID: #<?= $curr_user['id'] ?></span>
                    <span class="px-3 py-1 bg-emerald-50 text-emerald-500 rounded-lg text-[9px] font-black uppercase border border-emerald-100 italic">Hoạt động</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-8 border-t border-dashed border-slate-100">
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
        </div>
        <div class="pt-4">
            <button type="submit" class="w-full md:w-auto bg-slate-900 text-white px-12 py-5 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-blue-600 shadow-2xl shadow-slate-200 transition-all transform hover:-translate-y-1">Lưu thay đổi</button>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

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