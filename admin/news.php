<?php 
require_once '../config.php';
include 'header.php'; 

$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(*) FROM news WHERE title LIKE ?";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute(["%$search%"]);
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT * FROM news WHERE title LIKE ? ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$search%"]);
$news = $stmt->fetchAll();
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
        <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tight text-center sm:text-left">Tin tức du lịch</h3>
        <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em] mt-1 text-center sm:text-left">Cập nhật cẩm nang & sự kiện</p>
    </div>
    <button onclick="openNewsModal('add')" class="bg-blue-600 text-white px-8 py-4 rounded-2xl font-black text-xs shadow-xl shadow-blue-100 hover:bg-slate-900 transition-all duration-300">
        <i class="fas fa-edit mr-2"></i> THÊM BÀI VIẾT
    </button>
</div>

<div class="mb-8">
    <form method="GET" class="relative w-full">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm tên bài viết..." 
               class="w-full pl-14 pr-4 py-4 bg-white border-0 shadow-sm rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-bold">
        <span class="absolute left-5 top-4 text-slate-300"><i class="fas fa-search fa-lg"></i></span>
    </form>
</div>

<div class="space-y-4">
    <?php foreach($news as $n): ?>
    <div class="bg-white p-4 rounded-[2rem] shadow-sm border border-slate-100 flex flex-col md:flex-row items-center gap-5">
        <img src="../assets/uploads/<?= $n['image'] ?: 'default-news.jpg' ?>" class="w-full md:w-32 h-32 rounded-3xl object-cover border-4 border-slate-50 shadow-sm">
        <div class="flex-1 min-w-0 text-center md:text-left">
            <div class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-1"><?= date('d/m/Y', strtotime($n['created_at'])) ?></div>
            <h4 class="text-base font-bold text-slate-900 leading-tight mb-2 truncate"><?= htmlspecialchars($n['title']) ?></h4>
            <p class="text-xs text-slate-400 line-clamp-2"><?= htmlspecialchars($n['summary']) ?></p>
        </div>
        <div class="flex md:flex-col gap-2 w-full md:w-auto">
            <button onclick='openNewsModal("edit", <?= json_encode($n) ?>)' class="flex-1 w-12 h-12 flex items-center justify-center bg-slate-50 text-blue-600 rounded-2xl hover:bg-blue-600 hover:text-white transition-all"><i class="fas fa-pen"></i></button>
            <button onclick="confirmDelete(<?= $n['id'] ?>)" class="flex-1 w-12 h-12 flex items-center justify-center bg-red-50 text-red-500 rounded-2xl hover:bg-red-500 hover:text-white transition-all"><i class="fas fa-trash-alt"></i></button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if($total_pages > 1): ?>
<div class="mt-8 flex justify-center space-x-2">
    <?php for($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= $search ?>" class="w-10 h-10 flex items-center justify-center rounded-xl text-xs font-black transition-all <?= $i == $page ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'bg-white text-slate-400 border border-slate-100' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<div id="newsModal" class="fixed inset-0 bg-slate-900/60 z-[70] hidden flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-[2rem] shadow-2xl transform transition-all scale-95 opacity-0 duration-300" id="modalContent">
        <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-white rounded-t-[2rem]">
            <h4 id="modalTitle" class="text-xs font-black text-slate-800 uppercase tracking-widest"></h4>
            <button onclick="closeModal()" class="w-8 h-8 flex items-center justify-center bg-slate-50 text-slate-400 rounded-lg hover:text-red-500">&times;</button>
        </div>
        
        <form id="newsForm" action="news_process.php" method="POST" enctype="multipart/form-data" class="flex flex-col overflow-hidden">
            <div class="modal-body p-6 space-y-5">
                <input type="hidden" name="id" id="n_id">
                <input type="hidden" name="action" id="n_action">
                
                <div class="space-y-1">
                    <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Tiêu đề bài viết</label>
                    <input type="text" name="title" id="n_title" required class="w-full p-3.5 bg-slate-50 border-0 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 font-bold text-sm">
                </div>

                <div class="space-y-1">
                    <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Ảnh đại diện bài viết</label>
                    <input type="file" name="image" class="w-full text-[9px] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[9px] file:font-black file:bg-blue-50 file:text-blue-700">
                </div>

                <div class="space-y-1">
                    <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Tóm tắt </label>
                    <textarea name="summary" id="n_summary" rows="2" class="w-full p-3.5 bg-slate-50 border-0 rounded-xl outline-none text-xs font-medium"></textarea>
                </div>

                <div class="space-y-1">
                    <label class="text-[9px] font-black uppercase text-slate-400 ml-1">Nội dung bài viết</label>
                    <textarea name="content" id="n_content"></textarea>
                </div>
            </div>

            <div class="p-6 border-t border-slate-50 bg-slate-50 rounded-b-[2rem]">
                <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-xl font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl shadow-slate-200 text-xs text-center">
                    XÁC NHẬN ĐĂNG BÀI
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    CKEDITOR.replace('n_content', {
        height: 200,
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

    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
    <?php if(isset($_SESSION['success'])): ?>
        Toast.fire({ icon: 'success', title: '<?= $_SESSION['success'] ?>' });
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    function openNewsModal(mode, data = null) {
        const modal = document.getElementById('newsModal');
        const content = document.getElementById('modalContent');
        document.getElementById('n_action').value = mode;
        document.getElementById('modalTitle').innerText = mode === 'add' ? 'Viết tin tức' : 'Sửa bài viết';
        
        if(data) {
            document.getElementById('n_id').value = data.id;
            document.getElementById('n_title').value = data.title;
            document.getElementById('n_summary').value = data.summary;
            CKEDITOR.instances.n_content.setData(data.content);
        } else {
            document.getElementById('newsForm').reset();
            CKEDITOR.instances.n_content.setData('');
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
        setTimeout(() => { document.getElementById('newsModal').classList.add('hidden'); }, 300);
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Xóa bài viết?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0f172a',
            confirmButtonText: 'Đồng ý',
            customClass: { popup: 'rounded-[2rem]' }
        }).then((result) => {
            if (result.isConfirmed) window.location.href = 'news_process.php?action=delete&id=' + id;
        });
    }
</script>

</main></div></div></body></html>