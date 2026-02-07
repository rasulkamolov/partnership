<?php
// app/pages/pricing.php
if (!$is_admin) { header("Location: ?p=dashboard"); exit; }
?>
<div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200/60 hover:shadow-lg transition duration-500">
    <div class="flex justify-between items-center mb-8">
        <div class="flex items-center gap-3">
            <div class="bg-indigo-50 p-2.5 rounded-xl"><i data-lucide="tag" class="text-indigo-600 w-5 h-5"></i></div>
            <h3 class="text-2xl font-black text-slate-900 tracking-tight">Tuition & Scheduling</h3>
        </div>
        <p class="text-indigo-600 font-bold text-xs bg-indigo-50 px-4 py-2 rounded-xl border border-indigo-100">
            <?= $db->query("SELECT COUNT(*) FROM students")->fetchColumn() ?> Active Contracts
        </p>
    </div>
    <form method="POST">
        <div class="overflow-hidden rounded-2xl border border-slate-200/60 shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/80 text-slate-500 text-[10px] font-bold uppercase tracking-widest border-b border-slate-200/60 sticky top-0 z-10 backdrop-blur-sm">
                    <tr><th class="p-5 pl-6">Student</th><th class="p-5">Group</th><th class="p-5">Monthly Fee</th><th class="p-5 pr-6">Schedule</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php foreach($db->query("SELECT * FROM students") as $s): ?>
                    <tr class="group hover:bg-slate-50/50 transition duration-200">
                        <td class="p-5 pl-6">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-xs border border-slate-200">
                                    <?= htmlspecialchars(substr($s['name'], 0, 1)) ?>
                                </div>
                                <p class="font-bold text-slate-700 text-sm group-hover:text-indigo-600 transition"><?= htmlspecialchars($s['name']) ?></p>
                            </div>
                        </td>
                        <td class="p-5">
                            <span class="bg-slate-50 px-3 py-1.5 rounded-lg text-[11px] font-bold text-slate-500 border border-slate-200 shadow-sm">
                                <?= htmlspecialchars($s['group_name']) ?>
                            </span>
                        </td>
                        <td class="p-5">
                            <div class="relative group/input max-w-[140px]">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-[10px] group-focus-within/input:text-indigo-500 transition">UZS</span>
                                <input type="number" name="fee[<?=$s['id']?>]" value="<?= htmlspecialchars($s['monthly_fee']) ?>" class="pl-10 pr-3 py-2 bg-slate-50 rounded-lg border border-transparent outline-none w-full font-bold text-slate-700 text-xs focus:bg-white focus:border-indigo-200 focus:ring-2 focus:ring-indigo-500/10 transition shadow-inner">
                            </div>
                        </td>
                        <td class="p-5 pr-6">
                            <div class="relative max-w-[180px]">
                                <select name="sch[<?=$s['id']?>]" class="appearance-none pl-3 pr-8 py-2 bg-slate-50 rounded-lg border border-transparent outline-none font-bold text-slate-600 text-xs w-full focus:bg-white focus:border-indigo-200 focus:ring-2 focus:ring-indigo-500/10 transition shadow-sm cursor-pointer hover:bg-slate-100">
                                    <option value="odd" <?= $s['schedule_type'] == 'odd' ? 'selected' : '' ?>>Odd Days (M/W/F)</option>
                                    <option value="even" <?= $s['schedule_type'] == 'even' ? 'selected' : '' ?>>Even Days (T/T/S)</option>
                                    <option value="everyday" <?= $s['schedule_type'] == 'everyday' ? 'selected' : '' ?>>Every Day</option>
                                </select>
                                <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-400 pointer-events-none"></i>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="p-6 bg-slate-50 border-t border-slate-200/60 text-center sticky bottom-0 z-20 backdrop-blur-md bg-opacity-90">
                <button name="save_pricing" class="bg-slate-900 text-white px-8 py-3 rounded-xl font-bold text-sm hover:scale-105 hover:bg-indigo-600 transition-all duration-300 shadow-lg shadow-slate-900/10 flex items-center gap-2 mx-auto">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    <span>Update Pricing Structure</span>
                </button>
            </div>
        </div>
    </form>
</div>
