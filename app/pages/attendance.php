<?php
// app/pages/attendance.php
$att_q = "SELECT s.*, sc.name as s_name, a.status as today_status FROM students s JOIN schools sc ON s.school_id = sc.id LEFT JOIN attendance a ON s.id = a.student_id AND a.date = date('now')";
if (!$is_admin) $att_q .= " WHERE s.school_id = $uid";

$students = $db->query($att_q)->fetchAll();
?>
<div class="bg-white rounded-[3.5rem] p-12 border border-slate-200/50 shadow-sm relative overflow-hidden">
    <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-50 rounded-full blur-3xl -z-10 opacity-50"></div>

    <div class="flex justify-between items-center mb-10">
        <div>
            <h3 class="text-3xl font-black text-slate-900 tracking-tight">Daily Roster</h3>
            <p class="text-slate-400 font-medium mt-1">Mark attendance for today's session.</p>
        </div>
        <div class="flex items-center gap-3 bg-indigo-50 px-6 py-3 rounded-2xl border border-indigo-100">
            <i data-lucide="calendar" class="text-indigo-600 w-5 h-5"></i>
            <span class="text-indigo-900 font-bold"><?= date('M d, Y') ?></span>
        </div>
    </div>

    <form method="POST">
        <div class="overflow-hidden rounded-3xl border border-slate-100 shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-widest border-b border-slate-100">
                        <th class="p-6 pl-10">Student Identity</th>
                        <th class="p-6 text-center">Status Assignment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 bg-white">
                    <?php if(empty($students)): ?>
                        <tr><td colspan="2" class="p-10 text-center text-slate-400 font-bold">No students found.</td></tr>
                    <?php endif; ?>

                    <?php foreach($students as $row):
                        $current_status = $row['today_status'] ?? 'Present';
                    ?>
                    <tr class="group hover:bg-slate-50/80 transition duration-200">
                        <td class="p-6 pl-10">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-100 to-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm border border-indigo-100/50">
                                    <?= htmlspecialchars(substr($row['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="font-black text-slate-800 text-base mb-0.5"><?= htmlspecialchars($row['name']) ?></p>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide bg-slate-100 px-2 py-0.5 rounded-md"><?= htmlspecialchars($row['s_name']) ?></span>
                                        <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-wide"><?= htmlspecialchars($row['group_name']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="p-6">
                            <div class="flex justify-center gap-3">
                                <?php if($is_admin): ?>
                                    <?php foreach(['Present', 'Absent', 'Late'] as $status): ?>
                                    <label class="cursor-pointer relative group/label">
                                        <input type="radio" name="att[<?=$row['id']?>]" value="<?=$status?>" class="hidden peer" <?= $status == $current_status ? 'checked' : '' ?>>
                                        <span class="px-6 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-500 font-bold text-xs transition-all duration-300 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 peer-checked:shadow-lg peer-checked:shadow-indigo-500/30 hover:bg-slate-50 block text-center min-w-[90px]">
                                            <?=$status?>
                                        </span>
                                    </label>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?= get_student_status_badge($row['today_status'] ?? 'Pending') ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if($is_admin && !empty($students)): ?>
        <div class="mt-10 text-center">
            <button name="save_att" class="bg-slate-900 text-white px-12 py-4 rounded-2xl font-black text-sm hover:scale-105 hover:bg-indigo-600 transition-all duration-300 shadow-xl shadow-slate-900/10 flex items-center gap-3 mx-auto">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Publish Attendance Data</span>
            </button>
        </div>
        <?php endif; ?>
    </form>
</div>
