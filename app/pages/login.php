<div class="min-h-screen flex items-center justify-center p-4 bg-slate-950 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
         <div class="absolute top-[-10%] right-[-10%] w-[40%] h-[40%] bg-indigo-600/20 rounded-full blur-[120px]"></div>
         <div class="absolute bottom-[-10%] left-[-10%] w-[40%] h-[40%] bg-purple-600/20 rounded-full blur-[120px]"></div>
    </div>
    <div class="w-full max-w-sm bg-white/95 backdrop-blur-2xl rounded-3xl shadow-2xl p-8 text-center border border-white/20 relative z-10 animate-fade-in-up">
        <div class="inline-flex p-3 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-2xl mb-6 shadow-xl shadow-indigo-500/30 ring-4 ring-indigo-500/10">
            <i data-lucide="zap" class="text-white w-6 h-6"></i>
        </div>
        <h1 class="text-3xl font-black text-slate-900 mb-1 tracking-tight">Oxford LC</h1>
        <p class="text-slate-400 mb-8 font-bold uppercase text-[10px] tracking-[0.25em]">Enterprise Access</p>
        <form method="POST" class="space-y-3">
            <div class="group relative">
                <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 w-4 h-4 group-focus-within:text-indigo-500 transition duration-300"></i>
                <input type="text" name="user" placeholder="Identifier" class="w-full pl-11 p-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 outline-none transition font-semibold text-sm text-slate-700 placeholder:text-slate-400" required>
            </div>
            <div class="group relative">
                <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 w-4 h-4 group-focus-within:text-indigo-500 transition duration-300"></i>
                <input type="password" name="pass" placeholder="Secret Key" class="w-full pl-11 p-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 outline-none transition font-semibold text-sm text-slate-700 placeholder:text-slate-400" required>
            </div>
            <button name="login" class="w-full bg-slate-900 text-white font-bold p-3.5 rounded-xl hover:bg-indigo-600 transition-all duration-300 shadow-lg shadow-indigo-500/20 mt-2 flex justify-center items-center gap-2 group text-sm">
                <span>Authorize Access</span>
                <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
            </button>
        </form>
    </div>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</div>
