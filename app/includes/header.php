<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Oxford LC Infinity | Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #f8fafc; }
        .custom-gradient { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); }
        .tab-active { background: #6366f1; color: white; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3); }
        .sidebar-link:hover { background: rgba(255,255,255,0.05); color: white; }
    </style>
</head>
<body class="bg-slate-50 antialiased text-slate-900">

    <aside class="w-80 custom-gradient text-slate-400 p-10 flex flex-col fixed h-full shadow-2xl z-50">
        <div class="flex items-center gap-4 text-white mb-20 select-none">
            <div class="bg-indigo-500 p-2 rounded-xl shadow-lg shadow-indigo-500/30"><i data-lucide="component" class="w-6 h-6"></i></div>
            <span class="text-2xl font-black tracking-tighter">INFINITY <span class="text-indigo-400">LC</span></span>
        </div>

        <nav class="space-y-3 flex-1">
            <div class="px-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-4">Core</div>
            <a href="?p=dashboard" class="flex items-center gap-4 p-4 rounded-2xl transition sidebar-link <?= (!isset($_GET['p']) || $_GET['p']=='dashboard') ? 'tab-active' : '' ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> <span class="font-semibold">Dashboard</span>
            </a>
            <a href="?p=attendance" class="flex items-center gap-4 p-4 rounded-2xl transition sidebar-link <?= ($_GET['p'] ?? '')=='attendance' ? 'tab-active' : '' ?>">
                <i data-lucide="calendar-check" class="w-5 h-5"></i> <span class="font-semibold">Attendance</span>
            </a>

            <?php if($is_admin): ?>
                <div class="px-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mt-8 mb-4">Management</div>
                <a href="?p=students" class="flex items-center gap-4 p-4 rounded-2xl transition sidebar-link <?= ($_GET['p'] ?? '')=='students' ? 'tab-active' : '' ?>">
                    <i data-lucide="users" class="w-5 h-5"></i> <span class="font-semibold">Students</span>
                </a>
                <a href="?p=pricing" class="flex items-center gap-4 p-4 rounded-2xl transition sidebar-link <?= ($_GET['p'] ?? '')=='pricing' ? 'tab-active' : '' ?>">
                    <i data-lucide="dollar-sign" class="w-5 h-5"></i> <span class="font-semibold">Pricing</span>
                </a>
            <?php endif; ?>

            <div class="px-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mt-8 mb-4">Insights</div>
            <a href="?p=reports" class="flex items-center gap-4 p-4 rounded-2xl transition sidebar-link <?= ($_GET['p'] ?? '')=='reports' ? 'tab-active' : '' ?>">
                <i data-lucide="bar-chart-horizontal" class="w-5 h-5"></i> <span class="font-semibold">Analytics</span>
            </a>
        </nav>

        <div class="mt-auto pt-8 border-t border-white/5">
            <a href="?logout=1" class="flex items-center gap-4 p-4 rounded-2xl text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 transition group">
                <i data-lucide="log-out" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i> <span class="font-bold text-sm">Sign Out</span>
            </a>
        </div>
    </aside>

    <main class="ml-80 flex-1 p-12 min-h-screen">
        <header class="flex justify-between items-start mb-16 animate-fade-in-down">
            <div>
                <h2 class="text-slate-400 font-bold text-xs uppercase tracking-[0.3em] mb-3">Enterprise Console</h2>
                <h1 class="text-5xl font-black text-slate-900 tracking-tight"><?= ucfirst($_GET['p'] ?? 'Dashboard') ?></h1>
            </div>
            <div class="flex items-center gap-6 bg-white p-2 pr-6 rounded-[2rem] border border-slate-200/60 shadow-sm hover:shadow-md transition duration-300">
                <div class="px-6 py-2 border-r border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Status</p>
                    <p class="text-xs font-black text-emerald-500 flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        Operational
                    </p>
                </div>
                <div class="flex items-center gap-4 pl-2">
                    <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-lg shadow-lg shadow-indigo-500/20">
                        <?= strtoupper(substr($_SESSION['name'], 0, 1)) ?>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900 leading-tight"><?= $_SESSION['name'] ?></p>
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider"><?= $is_admin ? 'Master Admin' : 'Partner' ?></p>
                    </div>
                </div>
            </div>
        </header>
