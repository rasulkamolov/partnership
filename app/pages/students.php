<?php
// app/pages/students.php
if (!$is_admin) { header("Location: ?p=dashboard"); exit; }
?>
<div class="bg-white rounded-[3.5rem] p-12 shadow-sm border border-slate-200/50 hover:shadow-xl transition duration-500">
    <div class="flex justify-between items-center mb-12">
        <h3 class="text-3xl font-black text-slate-900 tracking-tight">Managed Students</h3>
        <div class="bg-slate-50 p-2 rounded-[20px] border border-slate-100 flex items-center shadow-inner w-80">
            <i data-lucide="search" class="text-slate-400 w-5 h-5 ml-4"></i>
            <input type="text" placeholder="Search Database..." class="p-3 bg-transparent rounded-xl border-none outline-none w-full font-bold text-sm text-slate-600 placeholder:text-slate-300">
        </div>
    </div>

    <div class="overflow-hidden rounded-[2.5rem] border border-slate-100">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-slate-400 text-[10px] font-black uppercase tracking-widest border-b border-slate-100">
                <tr><th class="p-8 pl-10">Name</th><th class="p-8">School</th><th class="p-8">Group</th><th class="p-8 text-right pr-10">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-50 bg-white">
                <?php foreach($db->query("SELECT s.*, sc.name as sname FROM students s JOIN schools sc ON s.school_id = sc.id") as $s): ?>
                <tr class="group hover:bg-slate-50/50 transition duration-200">
                    <td class="p-8 pl-10">
                        <p class="font-black text-slate-800 text-lg group-hover:text-indigo-600 transition"><?= htmlspecialchars($s['name']) ?></p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1"><?= htmlspecialchars($s['schedule_type']) == 'odd' ? 'Mon/Wed/Fri' : 'Tue/Thu/Sat' ?></p>
                    </td>
                    <td class="p-8">
                        <span class="bg-indigo-50 text-indigo-600 px-4 py-1.5 rounded-xl text-xs font-black uppercase tracking-wide border border-indigo-100/50">
                            <?= htmlspecialchars($s['sname']) ?>
                        </span>
                    </td>
                    <td class="p-8">
                        <span class="bg-slate-100 px-4 py-2 rounded-xl text-xs font-bold text-slate-600 border border-slate-200/50 shadow-sm">
                            <?= htmlspecialchars($s['group_name']) ?>
                        </span>
                    </td>
                    <td class="p-8 text-right pr-10">
                        <form method="POST" onsubmit="return confirm('Are you sure you want to remove this student? This action cannot be undone.');" class="inline">
                            <input type="hidden" name="delete_student" value="1">
                            <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                            <button type="submit" class="text-rose-300 hover:text-rose-500 hover:bg-rose-50 p-3 rounded-xl transition duration-300 inline-flex items-center justify-center cursor-pointer">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>