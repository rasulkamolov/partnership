<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title><?= get_setting('company_name') ?> | Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #f8fafc; }
        .custom-gradient { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); }
        .tab-active { background: #6366f1; color: white; box-shadow: 0 4px 12px -2px rgba(99, 102, 241, 0.3); }
        .sidebar-link:hover:not(.tab-active) { background: rgba(255,255,255,0.05); color: white; }
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 antialiased text-slate-900 overflow-x-hidden">

    <aside class="w-64 custom-gradient text-slate-400 p-6 flex flex-col fixed h-full shadow-2xl z-50">
        <div class="flex items-center gap-3 text-white mb-12 select-none px-2">
            <div class="bg-indigo-500 p-1.5 rounded-lg shadow-lg shadow-indigo-500/30"><i data-lucide="component" class="w-5 h-5"></i></div>
            <span class="text-xl font-black tracking-tight"><?= get_setting('company_name') ?></span>
        </div>

        <nav class="space-y-1.5 flex-1">
            <div class="px-3 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-3">Asosiy</div>
            <a href="?p=dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl transition sidebar-link text-sm <?= (!isset($_GET['p']) || $_GET['p']=='dashboard') ? 'tab-active' : '' ?>">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> <span class="font-semibold">Boshqaruv Paneli</span>
            </a>
            <a href="?p=attendance" class="flex items-center gap-3 px-4 py-3 rounded-xl transition sidebar-link text-sm <?= ($_GET['p'] ?? '')=='attendance' ? 'tab-active' : '' ?>">
                <i data-lucide="calendar-check" class="w-4 h-4"></i> <span class="font-semibold">Davomat</span>
            </a>
            <a href="?p=monthly" class="flex items-center gap-3 px-4 py-3 rounded-xl transition sidebar-link text-sm <?= ($_GET['p'] ?? '')=='monthly' ? 'tab-active' : '' ?>">
                <i data-lucide="calendar-days" class="w-4 h-4"></i> <span class="font-semibold">Oylik Ko'rinish</span>
            </a>

            <?php if($is_admin): ?>
                <div class="px-3 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mt-6 mb-3">Boshqaruv</div>
                <a href="?p=schools" class="flex items-center gap-3 px-4 py-3 rounded-xl transition sidebar-link text-sm <?= ($_GET['p'] ?? '')=='schools' ? 'tab-active' : '' ?>">
                    <i data-lucide="building-2" class="w-4 h-4"></i> <span class="font-semibold">Maktablar</span>
                </a>
                <a href="?p=students" class="flex items-center gap-3 px-4 py-3 rounded-xl transition sidebar-link text-sm <?= ($_GET['p'] ?? '')=='students' ? 'tab-active' : '' ?>">
                    <i data-lucide="users" class="w-4 h-4"></i> <span class="font-semibold">O'quvchilar</span>
                </a>
                <a href="?p=pricing" class="flex items-center gap-3 px-4 py-3 rounded-xl transition sidebar-link text-sm <?= ($_GET['p'] ?? '')=='pricing' ? 'tab-active' : '' ?>">
                    <i data-lucide="dollar-sign" class="w-4 h-4"></i> <span class="font-semibold">Narxlar</span>
                </a>
                <a href="?p=settings" class="flex items-center gap-3 px-4 py-3 rounded-xl transition sidebar-link text-sm <?= ($_GET['p'] ?? '')=='settings' ? 'tab-active' : '' ?>">
                    <i data-lucide="settings" class="w-4 h-4"></i> <span class="font-semibold">Sozlamalar</span>
                </a>
            <?php endif; ?>

            <div class="px-3 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mt-6 mb-3">Tahlillar</div>
            <a href="?p=reports" class="flex items-center gap-3 px-4 py-3 rounded-xl transition sidebar-link text-sm <?= ($_GET['p'] ?? '')=='reports' ? 'tab-active' : '' ?>">
                <i data-lucide="bar-chart-horizontal" class="w-4 h-4"></i> <span class="font-semibold">Analitika</span>
            </a>
        </nav>

        <div class="mt-auto pt-6 border-t border-white/5">
            <a href="?logout=1" class="flex items-center gap-3 px-4 py-3 rounded-xl text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 transition group text-sm">
                <i data-lucide="log-out" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform"></i> <span class="font-bold">Chiqish</span>
            </a>
        </div>
    </aside>

    <main class="ml-64 flex-1 p-8 min-h-screen">
        <header class="flex justify-between items-end mb-10 animate-fade-in-down">
            <div>
                <h2 class="text-slate-400 font-bold text-[10px] uppercase tracking-[0.2em] mb-1">Korporativ Konsol</h2>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">
                    <?php
                    $page_titles = [
                        'dashboard' => 'Boshqaruv Paneli',
                        'attendance' => 'Davomat',
                        'monthly' => 'Oylik Ko\'rinish',
                        'schools' => 'Maktablar',
                        'students' => 'O\'quvchilar',
                        'pricing' => 'Narxlar',
                        'settings' => 'Tizim Sozlamalari',
                        'reports' => 'Analitika'
                    ];
                    echo $page_titles[$_GET['p'] ?? 'dashboard'] ?? ucfirst($_GET['p'] ?? 'Boshqaruv Paneli');
                    ?>
                </h1>
            </div>

            <div class="flex items-center gap-4 bg-white py-1.5 pl-1.5 pr-4 rounded-full border border-slate-200/60 shadow-sm hover:shadow transition duration-300">
                <div class="w-9 h-9 bg-indigo-600 rounded-full flex items-center justify-center text-white font-black text-sm shadow-md shadow-indigo-500/20">
                    <?= strtoupper(substr($_SESSION['name'], 0, 1)) ?>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-slate-900 leading-none mb-0.5"><?= $_SESSION['name'] ?></span>
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider"><?= $is_admin ? 'Bosh Administrator' : 'Hamkor' ?></span>
                </div>
                <div class="h-4 w-px bg-slate-200 mx-1"></div>
                <div class="flex items-center gap-1.5">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wide">Jonli</span>
                </div>
            </div>
        </header>
