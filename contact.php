<?php
require_once 'config.php';
include 'header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="bg-[#f8fafc] min-h-screen pb-20">
    <div class="max-w-7xl mx-auto px-4 py-16">
        
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-6xl font-black italic text-slate-900 uppercase tracking-tighter mb-4">Kết nối với chúng tôi</h1>
            <p class="text-xs font-black text-slate-400 uppercase tracking-[0.4em]">Chúng tôi luôn sẵn sàng lắng nghe bạn</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            
            <div class="lg:col-span-7">
                <div class="bg-white rounded-[3rem] p-10 md:p-14 shadow-2xl shadow-slate-200/50 border border-white">
                    <h3 class="text-2xl font-black italic uppercase text-slate-800 mb-8 flex items-center tracking-tighter">
                        <span class="w-12 h-2 bg-blue-600 rounded-full mr-5 shadow-lg shadow-blue-200"></span> 
                        Gửi tin nhắn
                    </h3>
                    
                    <form id="contactForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2 italic">Họ và tên</label>
                                <input type="text" id="name" required class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-2 italic">Địa chỉ Email</label>
                                <input type="email" id="email" required class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 italic">Chủ đề</label>
                            <input type="text" id="subject" required class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 italic">Nội dung tin nhắn</label>
                            <textarea id="message" rows="5" required class="w-full p-4 bg-slate-50 border-0 rounded-2xl text-xs font-black outline-none focus:ring-2 focus:ring-blue-500 transition-all resize-none"></textarea>
                        </div>

                        <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-[2rem] font-black uppercase text-xs tracking-[0.2em] shadow-2xl shadow-slate-300 hover:bg-blue-600 transition-all active:scale-95 flex items-center justify-center">
                            Gửi yêu cầu ngay <i class="fas fa-paper-plane ml-3 text-[10px]"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-5 space-y-8">
                <div class="bg-white rounded-[3rem] p-10 shadow-xl border border-white">
                    <h3 class="text-xl font-black italic uppercase text-slate-800 mb-8 tracking-tighter">Thông tin cửa hàng</h3>
                    
                    <div class="space-y-8">
                        <div class="flex items-start group">
                            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mr-5 flex-shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Địa chỉ trụ sở</p>
                                <p class="text-sm font-bold text-slate-700 leading-relaxed"> 126 Huỳnh Tấn Phát, Quận Hải Châu, TP. Đà Nẫng</p>
                            </div>
                        </div>

                        <div class="flex items-start group">
                            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mr-5 flex-shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Hotline hỗ trợ</p>
                                <p class="text-lg font-black text-slate-800 tracking-tighter italic">0777454550</p>
                            </div>
                        </div>

                        <div class="flex items-start group">
                            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center mr-5 flex-shrink-0 group-hover:bg-purple-600 group-hover:text-white transition-all shadow-sm">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Email liên hệ</p>
                                <p class="text-sm font-bold text-slate-700 italic">contact@lilytravel.com</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 pt-10 border-t border-dashed border-slate-100 flex gap-4">
                        <a href="https://www.facebook.com/ngo.van.nho.22404" class="w-10 h-10 bg-slate-50 text-slate-400 rounded-xl flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all"><i class="fab fa-facebook-f text-xs"></i></a>
                        <a href="https://www.instagram.com/vawnhos/" class="w-10 h-10 bg-slate-50 text-slate-400 rounded-xl flex items-center justify-center hover:bg-pink-600 hover:text-white transition-all"><i class="fab fa-instagram text-xs"></i></a>
                        <a href="https://www.youtube.com/@nhovan8425" class="w-10 h-10 bg-slate-50 text-slate-400 rounded-xl flex items-center justify-center hover:bg-red-600 hover:text-white transition-all"><i class="fab fa-youtube text-xs"></i></a>
                    </div>
                </div>

                <div class="rounded-[3rem] overflow-hidden h-64 shadow-xl border-8 border-white relative group">
                    
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3470.150393013081!2d108.21948517459933!3d16.03219264042934!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x314219ee598df9c5%3A0xaadb53409be7c909!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBLaeG6v24gdHLDumMgxJDDoCBO4bq1bmc!5e1!3m2!1svi!2s!4v1773233600177!5m2!1svi!2s" 
                        class="w-full h-full grayscale group-hover:grayscale-0 transition-all duration-700" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Đang xử lý...',
            text: 'Vui lòng đợi trong giây lát',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        setTimeout(() => {
            Swal.fire({
                title: '<span class="uppercase font-black text-sm italic tracking-widest">Gửi thành công!</span>',
                html: `
                    <div class="text-center p-4">
                        <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-[2rem] flex items-center justify-center mx-auto mb-6 text-3xl shadow-inner">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <p class="text-xs font-bold text-slate-500 leading-relaxed uppercase tracking-tighter px-4">
                            Cảm ơn <b>${document.getElementById('name').value}</b>. Tin nhắn của bạn đã được chuyển tới Ban quản trị. Chúng tôi sẽ phản hồi qua email trong vòng 24h.
                        </p>
                    </div>
                `,
                showConfirmButton: true,
                confirmButtonText: 'TUYỆT VỜI',
                confirmButtonColor: '#0f172a',
                customClass: {
                    popup: 'rounded-[3rem] border-0',
                    confirmButton: 'rounded-2xl px-12 py-4 font-black uppercase text-[10px] tracking-widest'
                }
            }).then(() => {
                document.getElementById('contactForm').reset();
            });
        }, 1500);
    });
</script>

<?php include 'footer.php'; ?>