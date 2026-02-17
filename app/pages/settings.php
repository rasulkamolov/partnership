<?php
// app/pages/settings.php
if (!$is_admin) { header("Location: ?p=dashboard"); exit; }

$company_name = get_setting('company_name');
?>

<div class="space-y-8">
    <!-- General Settings -->
    <div class="bg-white rounded-3xl p-8 border border-slate-200/60 shadow-sm">
        <div class="flex items-center gap-3 mb-6">
            <div class="bg-indigo-50 p-2.5 rounded-xl text-indigo-600"><i data-lucide="settings" class="w-6 h-6"></i></div>
            <h3 class="text-xl font-black text-slate-900">Umumiy Sozlamalar</h3>
        </div>
        <form method="POST" class="max-w-md">
            <div class="mb-4">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Kompaniya Nomi</label>
                <input type="text" name="settings[company_name]" value="<?= htmlspecialchars($company_name) ?>" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-indigo-500/50 border border-slate-100 focus:bg-white transition" required>
            </div>
            <button name="save_settings" class="bg-indigo-600 text-white font-bold py-3 px-6 rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/30 flex items-center gap-2 text-sm">
                <span>Saqlash</span> <i data-lucide="save" class="w-4 h-4"></i>
            </button>
        </form>
    </div>

    <!-- Admin Profile Settings -->
    <div class="bg-white rounded-3xl p-8 border border-slate-200/60 shadow-sm">
        <div class="flex items-center gap-3 mb-6">
            <div class="bg-slate-900 p-2.5 rounded-xl text-white"><i data-lucide="user-cog" class="w-6 h-6"></i></div>
            <h3 class="text-xl font-black text-slate-900">Admin Login & Parol</h3>
        </div>
        <form method="POST" class="max-w-md space-y-4">
            <input type="hidden" name="update_admin_profile" value="1">

            <div>
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Yangi Login (Username)</label>
                <input type="text" name="admin_user" value="<?= htmlspecialchars($_SESSION['username']) ?>" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-slate-500/50 border border-slate-100 focus:bg-white transition" required>
            </div>

            <div>
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Yangi Parol</label>
                <input type="password" name="admin_pass" placeholder="O'zgartirish uchun yangi parol kiriting" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-slate-500/50 border border-slate-100 focus:bg-white transition">
            </div>

            <button type="submit" class="bg-slate-900 text-white font-bold py-3 px-6 rounded-xl hover:bg-slate-800 transition shadow-lg shadow-slate-900/30 flex items-center gap-2 text-sm">
                <span>Yangilash</span> <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            </button>
        </form>
    </div>

    <!-- Groups Management -->
    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Add Group Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-3xl p-8 border border-slate-200/60 shadow-sm sticky top-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-emerald-50 p-2.5 rounded-xl text-emerald-600"><i data-lucide="layers" class="w-6 h-6"></i></div>
                    <h3 class="text-xl font-black text-slate-900">Guruh Qo'shish</h3>
                </div>

                <form method="POST" class="space-y-4">
                    <input type="hidden" name="add_group" value="1">

                    <div class="space-y-3">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Guruh Nomi</label>
                            <input type="text" name="name" placeholder="Masalan: IELTS 1" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-emerald-500/50 border border-slate-100 focus:bg-white transition" required>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Narx (SO'M)</label>
                            <input type="number" name="price" placeholder="0" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-emerald-500/50 border border-slate-100 focus:bg-white transition" required>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Darslar Soni (Oyiga)</label>
                            <input type="number" name="lessons" value="12" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-emerald-500/50 border border-slate-100 focus:bg-white transition" required>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-3.5 rounded-xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-500/30 flex justify-center gap-2 items-center text-sm mt-4">
                        <span>Qo'shish</span> <i data-lucide="plus" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Groups List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-3xl p-8 border border-slate-200/60 shadow-sm min-h-[500px]">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-2xl font-black text-slate-900 tracking-tight">Guruhlar Ro'yxati</h3>
                    <div class="bg-slate-50 px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-500">
                        <?= $db->query("SELECT COUNT(*) FROM groups")->fetchColumn() ?> Guruh
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200/60 shadow-sm">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/80 text-slate-500 text-[10px] font-bold uppercase tracking-widest border-b border-slate-200/60 backdrop-blur-sm">
                            <tr>
                                <th class="p-4 pl-6">Guruh</th>
                                <th class="p-4">Narx</th>
                                <th class="p-4 text-center">Dars/Oy</th>
                                <th class="p-4 text-right pr-6">Amallar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php foreach($db->query("SELECT * FROM groups ORDER BY name") as $g): ?>
                            <tr class="group hover:bg-slate-50/50 transition duration-200">
                                <td class="p-4 pl-6 font-bold text-slate-800 text-sm"><?= htmlspecialchars($g['name']) ?></td>
                                <td class="p-4">
                                    <span class="font-bold text-slate-700 text-xs"><?= number_format($g['price'], 0) ?> UZS</span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="font-bold text-indigo-600 text-xs bg-indigo-50 px-2 py-1 rounded"><?= $g['lessons_per_month'] ?? 12 ?> ta</span>
                                </td>
                                <td class="p-4 text-right pr-6">
                                    <form method="POST" onsubmit="return confirm('Ushbu guruhni o\'chirmoqchimisiz?');" class="inline">
                                        <input type="hidden" name="delete_group" value="1">
                                        <input type="hidden" name="group_id" value="<?= $g['id'] ?>">
                                        <button type="submit" class="text-slate-300 hover:text-rose-500 hover:bg-rose-50 p-2 rounded-lg transition duration-300 inline-flex items-center justify-center cursor-pointer">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
