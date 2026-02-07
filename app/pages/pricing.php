<?php
// app/pages/pricing.php
if (!$is_admin) { header("Location: ?p=dashboard"); exit; }
?>
<div class="bg-white rounded-[3.5rem] p-12 shadow-sm border border-slate-200/50 hover:shadow-xl transition duration-500">
    <div class="flex justify-between items-center mb-12">
        <h3 class="text-3xl font-black text-slate-900 tracking-tight">Tuition & Scheduling</h3>
        <p class="text-slate-400 font-bold text-sm bg-indigo-50 px-6 py-2 rounded-2xl border border-indigo-100/50 text-indigo-500">
            <?= $db->query("SELECT COUNT(*) FROM students")->fetchColumn() ?> Active Contracts
        </p>
    </div>
    <form method="POST">
        <div class="overflow-hidden rounded-[2.5rem] border border-slate-100 shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 text-slate-400 text-[10px] font-black uppercase tracking-widest border-b border-slate-100 sticky top-0 z-10">
                    <tr><th class="p-8 pl-10">Student</th><th class="p-8">Group</th><th class="p-8">Monthly Fee</th><th class="p-8 pr-10">Schedule</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-50 bg-white">
                    <?php foreach($db->query("SELECT * FROM students") as $s): ?>
                    <tr class="group hover:bg-slate-50/50 transition duration-200">
                        <td class="p-8 pl-10">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-black text-sm">
                                    <?= htmlspecialchars(substr($s['name'], 0, 1)) ?>
                                </div>
                                <p class="font-bold text-slate-800 text-lg group-hover:text-indigo-600 transition"><?= htmlspecialchars($s['name']) ?></p>
                            </div>
                        </td>
                        <td class="p-8">
                            <span class="bg-slate-100 px-4 py-2 rounded-xl text-xs font-bold text-slate-600 border border-slate-200/50 shadow-sm">
                                <?= htmlspecialchars($s['group_name']) ?>
                            </span>
                        </td>
                        <td class="p-8">
                            <div class="relative group/input">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs group-focus-within/input:text-indigo-500 transition">UZS</span>
                                <input type="number" name="fee[<?=$s['id']?>]" value="<?= htmlspecialchars($s['monthly_fee']) ?>" class="pl-12 pr-4 py-3 bg-slate-50 rounded-xl border border-transparent outline-none w-40 font-bold text-slate-700 focus:bg-white focus:border-indigo-200 focus:ring-4 focus:ring-indigo-500/10 transition shadow-inner">
                            </div>
                        </td>
                        <td class="p-8 pr-10">
                            <div class="relative">
                                <select name="sch[<?=$s['id']?>]" class="appearance-none pl-4 pr-10 py-3 bg-slate-50 rounded-xl border border-transparent outline-none font-bold text-slate-700 w-full focus:bg-white focus:border-indigo-200 focus:ring-4 focus:ring-indigo-500/10 transition shadow-sm cursor-pointer hover:bg-slate-100">
                                    <option value="odd" <?= $s['schedule_type'] == 'odd' ? 'selected' : '' ?>>Odd Days (M/W/F)</option>
                                    <option value="even" <?= $s['schedule_type'] == 'even' ? 'selected' : '' ?>>Even Days (T/T/S)</option>
                                </select>
                                <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="p-8 bg-slate-50 border-t border-slate-100 text-center sticky bottom-0 z-20 backdrop-blur-md bg-opacity-90">
                <button name="save_pricing" class="bg-slate-900 text-white px-16 py-5 rounded-[2rem] font-black text-lg hover:scale-105 hover:bg-indigo-600 transition-all duration-300 shadow-xl shadow-slate-900/10 flex items-center gap-3 mx-auto">
                    <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                    <span>Update Pricing Structure</span>
                </button>
            </div>
        </div>
    </form>
</div>
