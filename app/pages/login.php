<?php
// app/pages/login.php
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oxford LC Infinity | Tizimga Kirish</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #f8fafc; }
        .custom-gradient { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); }
        .animate-fade-in-up { animation: fadeInUp 0.5s ease-out; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-slate-900 antialiased text-slate-900">

<div class="min-h-screen grid lg:grid-cols-2">
    <!-- Left: Branding & Visuals -->
    <div class="relative hidden lg:flex flex-col justify-between p-16 overflow-hidden">
        <!-- Abstract Background -->
        <div class="absolute inset-0 z-0">
            <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-900 via-slate-900 to-slate-950"></div>
            <div class="absolute top-[-20%] right-[-10%] w-[800px] h-[800px] bg-indigo-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[-10%] left-[-10%] w-[600px] h-[600px] bg-purple-600/10 rounded-full blur-[100px]"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 animate-fade-in-up">
            <div class="flex items-center gap-3 text-white mb-12">
                <div class="bg-indigo-500/20 backdrop-blur-md p-2 rounded-xl border border-indigo-500/30">
                    <i data-lucide="component" class="w-6 h-6 text-indigo-400"></i>
                </div>
                <span class="text-2xl font-black tracking-tight">INFINITY <span class="text-indigo-400">LC</span></span>
            </div>

            <div class="space-y-6 max-w-lg">
                <h1 class="text-5xl font-black text-white leading-tight tracking-tight">
                    Ta'lim Boshqaruvini <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400">Yangi Bosqichga Ko'taring</span>
                </h1>
                <p class="text-lg text-slate-400 leading-relaxed font-medium">
                    Zamonaviy o'quv markazlari uchun markazlashgan platforma. Davomatni kuzatib boring, hamkorlarni boshqaring va moliyaviy tahlillarni oling.
                </p>
            </div>
        </div>

        <!-- Footer Stats/Info -->
        <div class="relative z-10 grid grid-cols-2 gap-8 border-t border-white/10 pt-8 mt-12 animate-fade-in-up" style="animation-delay: 0.2s; animation-fill-mode: both;">
            <div>
                <p class="text-2xl font-bold text-white mb-1">99.9%</p>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Barqaror Ishlash</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-white mb-1">Xavfsiz</p>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Korporativ Shifrlash</p>
            </div>
        </div>
    </div>

    <!-- Right: Login Form -->
    <div class="flex items-center justify-center p-8 bg-white lg:rounded-l-[3rem] relative z-20 shadow-2xl shadow-black/50 animate-fade-in-up" style="animation-delay: 0.1s; animation-fill-mode: both;">
        <div class="w-full max-w-md space-y-8">
            <div class="text-center lg:text-left">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Xush Kelibsiz</h2>
                <p class="text-slate-500 font-medium">Iltimos, tizimga kirish uchun ma'lumotlaringizni kiriting.</p>
            </div>

            <?php if(isset($err)): ?>
            <div class="bg-rose-50 border border-rose-100 text-rose-600 p-4 rounded-xl flex items-center gap-3 text-sm font-bold animate-pulse">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                <?= htmlspecialchars($err) ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Login (Foydalanuvchi nomi)</label>
                    <div class="relative group">
                        <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5 group-focus-within:text-indigo-600 transition-colors duration-300"></i>
                        <input type="text" name="user" placeholder="Loginingizni kiriting" class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition font-semibold text-slate-700 placeholder:text-slate-400" required>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Parol</label>
                    <div class="relative group">
                        <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5 group-focus-within:text-indigo-600 transition-colors duration-300"></i>
                        <input type="password" name="pass" placeholder="Parolingizni kiriting" class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition font-semibold text-slate-700 placeholder:text-slate-400" required>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 transition cursor-pointer">
                        <span class="text-sm font-bold text-slate-500 group-hover:text-indigo-600 transition">Eslab qolish</span>
                    </label>
                    <a href="#" class="text-sm font-bold text-indigo-600 hover:text-indigo-700 transition">Parolni unutdingizmi?</a>
                </div>

                <button name="login" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl hover:bg-indigo-600 transition-all duration-300 shadow-xl shadow-slate-900/20 flex justify-center items-center gap-2 group transform active:scale-[0.98]">
                    <span>Tizimga Kirish</span>
                    <i data-lucide="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i>
                </button>
            </form>

            <div class="pt-6 border-t border-slate-100 text-center">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Korporativ darajadagi xavfsizlik bilan himoyalangan</p>
            </div>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
</body>
</html>
