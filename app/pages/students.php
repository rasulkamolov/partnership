<?php
// app/pages/students.php
if (!$is_admin) { header("Location: ?p=dashboard"); exit; }
?>
<div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200/60 hover:shadow-lg transition duration-500 min-h-[600px]">
    <div class="flex justify-between items-center mb-8">
        <h3 class="text-2xl font-black text-slate-900 tracking-tight">O'quvchilar Ro'yxati</h3>
        <div class="bg-slate-50 p-1.5 rounded-xl border border-slate-200/60 flex items-center shadow-inner w-72 transition focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-300">
            <i data-lucide="search" class="text-slate-400 w-4 h-4 ml-3"></i>
            <input type="text" placeholder="Qidiruv..." class="p-2 bg-transparent rounded-lg border-none outline-none w-full font-bold text-sm text-slate-600 placeholder:text-slate-400">
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200/60 shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50/80 text-slate-500 text-[10px] font-bold uppercase tracking-widest border-b border-slate-200/60 backdrop-blur-sm">
                <tr><th class="p-4 pl-6">Ism</th><th class="p-4">Maktab</th><th class="p-4">Guruh</th><th class="p-4 text-right pr-6">Amallar</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                <?php foreach($db->query("SELECT s.*, sc.name as sname FROM students s JOIN schools sc ON s.school_id = sc.id ORDER BY s.id DESC") as $s): ?>
                <tr class="group hover:bg-slate-50/50 transition duration-200">
                    <td class="p-4 pl-6">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-xs border border-slate-200">
                                <?= htmlspecialchars(substr($s['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm group-hover:text-indigo-600 transition"><?= htmlspecialchars($s['name']) ?></p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    <?php
                                    $sch = htmlspecialchars($s['schedule_type']);
                                    if ($sch == 'odd') echo 'Toq Kunlar (Du/Chor/Ju)';
                                    elseif ($sch == 'even') echo 'Juft Kunlar (Se/Pay/Sha)';
                                    else echo 'Har Kuni (Du-Shan)';
                                    ?>
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="p-4">
                        <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wide border border-indigo-100">
                            <?= htmlspecialchars($s['sname']) ?>
                        </span>
                    </td>
                    <td class="p-4">
                        <span class="bg-white px-3 py-1.5 rounded-lg text-[11px] font-bold text-slate-600 border border-slate-200 shadow-sm">
                            <?= htmlspecialchars($s['group_name']) ?>
                        </span>
                    </td>
                    <td class="p-4 text-right pr-6">
                        <form method="POST" onsubmit="return confirm('Haqiqatan ham bu o\'quvchini o\'chirmoqchimisiz? Bu amalni ortga qaytarib bo\'lmaydi.');" class="inline">
                            <input type="hidden" name="delete_student" value="1">
                            <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
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
