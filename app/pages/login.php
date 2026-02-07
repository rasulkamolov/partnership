<div class="min-h-screen flex items-center justify-center p-6 bg-[#020617] relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
         <div class="absolute top-[-10%] right-[-10%] w-[50%] h-[50%] bg-indigo-600/20 rounded-full blur-[100px]"></div>
         <div class="absolute bottom-[-10%] left-[-10%] w-[50%] h-[50%] bg-purple-600/20 rounded-full blur-[100px]"></div>
    </div>
    <div class="w-full max-w-md bg-white/95 backdrop-blur-xl rounded-[3rem] shadow-2xl p-12 text-center border border-white/20 relative z-10 animate-fade-in-up">
        <div class="inline-flex p-5 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-3xl mb-8 shadow-xl shadow-indigo-500/30">
            <i data-lucide="zap" class="text-white w-10 h-10"></i>
        </div>
        <h1 class="text-5xl font-black text-slate-900 mb-2 tracking-tighter">Oxford LC</h1>
        <p class="text-slate-400 mb-10 font-bold uppercase text-xs tracking-[0.3em]">Enterprise Access</p>
        <form method="POST" class="space-y-4">
            <div class="group relative">
                <i data-lucide="user" class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 w-5 h-5 group-focus-within:text-indigo-500 transition duration-300"></i>
                <input type="text" name="user" placeholder="Identifier" class="w-full pl-14 p-5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-200 outline-none transition font-bold text-slate-700" required>
            </div>
            <div class="group relative">
                <i data-lucide="lock" class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 w-5 h-5 group-focus-within:text-indigo-500 transition duration-300"></i>
                <input type="password" name="pass" placeholder="Secret Key" class="w-full pl-14 p-5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-200 outline-none transition font-bold text-slate-700" required>
            </div>
            <button name="login" class="w-full bg-slate-900 text-white font-bold p-5 rounded-2xl hover:bg-indigo-600 transition shadow-xl shadow-indigo-500/20 mt-4 flex justify-center items-center gap-2 group">
                <span>Authorize Access</span>
                <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
            </button>
        </form>
    </div>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</div>