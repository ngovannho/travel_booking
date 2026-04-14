<?php
require_once '../config.php';
include 'header.php';

$logFile = '../momo_ipn_log.txt';

// Xử lý yêu cầu xóa nhật ký
if (isset($_POST['clear_log'])) {
    if (file_exists($logFile)) {
        file_put_contents($logFile, "");
        $_SESSION['success'] = "Đã xóa toàn bộ nhật ký log!";
    }
    echo "<script>window.location.href='momo_logs.php';</script>";
    exit;
}

$logs = "";
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
}
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h3 class="text-2xl font-black text-slate-800 uppercase italic tracking-tighter">Nhật ký MoMo IPN</h3>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Giám sát các phản hồi kỹ thuật từ cổng thanh toán MoMo</p>
    </div>
    <form method="POST">
        <button type="submit" name="clear_log" onclick="return confirm('Bạn có chắc chắn muốn xóa toàn bộ nhật ký này?')" class="bg-red-500 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-900 transition-all shadow-lg shadow-red-100">
            <i class="fas fa-trash-alt mr-2"></i> Xóa nhật ký
        </button>
    </form>
</div>

<div class="bg-slate-900 rounded-[2.5rem] p-8 shadow-2xl border border-slate-800 relative overflow-hidden">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-3 h-3 rounded-full bg-red-500"></div>
        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
        <div class="w-3 h-3 rounded-full bg-green-500"></div>
        <span class="ml-2 text-[10px] font-black text-slate-500 uppercase tracking-widest italic">Terminal - momo_ipn_log.txt</span>
    </div>

    <div class="bg-slate-950 p-6 rounded-2xl border border-white/5 max-h-[600px] overflow-y-auto custom-scrollbar">
        <?php if (!empty($logs)): ?>
            <pre class="text-emerald-400 font-mono text-[11px] leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($logs) ?></pre>
        <?php else: ?>
            <p class="text-slate-600 font-mono text-xs italic">// Hiện tại chưa có dữ liệu nhật ký nào được ghi lại...</p>
        <?php endif; ?>
    </div>
</div>

</main></div></div></body></html>