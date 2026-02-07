<?php
// app/pages/attendance.php

// Determine today's schedule type
$dow = date('N'); // 1=Mon, 7=Sun
$day_type = ($dow % 2 != 0) ? 'odd' : 'even'; // Mon(1), Wed(3), Fri(5) -> odd; Tue(2), Thu(4), Sat(6) -> even
if ($dow == 7) $day_type = 'sunday'; // Handle Sunday separately if needed, or just treat as odd/even

// Build Query
$att_q = "SELECT s.*, sc.name as s_name, a.status as today_status
          FROM students s
          JOIN schools sc ON s.school_id = sc.id
          LEFT JOIN attendance a ON s.id = a.student_id AND a.date = date('now')
          WHERE (s.schedule_type = '$day_type' OR s.schedule_type = 'everyday')";

if (!$is_admin) $att_q .= " AND s.school_id = $uid";

$students = $db->query($att_q)->fetchAll();

// Translation Helper for Day Type
$day_type_uz = match($day_type) {
    'odd' => 'Toq',
    'even' => 'Juft',
    'sunday' => 'Yakshanba',
    default => ucfirst($day_type)
};

// Uzbek Day Names
$uz_days = [
    1 => 'Dushanba', 2 => 'Seshanba', 3 => 'Chorshanba', 4 => 'Payshanba',
    5 => 'Juma', 6 => 'Shanba', 7 => 'Yakshanba'
];
$day_name = $uz_days[$dow];
?>
<div class="bg-white rounded-3xl p-8 border border-slate-200/60 shadow-sm relative overflow-hidden min-h-[500px]">
    <!-- Decorative Background -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-50/50 rounded-full blur-3xl -z-10 opacity-60 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-64 h-64 bg-emerald-50/50 rounded-full blur-3xl -z-10 opacity-60 pointer-events-none"></div>

    <div class="flex justify-between items-end mb-8">
        <div>
            <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-1">Kunlik Davomat</h3>
            <p class="text-slate-500 text-sm font-medium">
                Ko'rsatilmoqda: <span class="text-indigo-600 font-bold uppercase"><?= $day_type_uz ?> Kunlar</span> & <span class="text-indigo-600 font-bold uppercase">Har Kuni</span>
            </p>
        </div>
        <div class="flex items-center gap-2 bg-slate-50 px-4 py-2 rounded-xl border border-slate-200/60 shadow-sm">
            <i data-lucide="calendar" class="text-indigo-500 w-4 h-4"></i>
            <span class="text-slate-700 font-bold text-sm"><?= date('d.m.Y') ?></span>
        </div>
    </div>

    <form method="POST">
        <?php if(empty($students)): ?>
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="bg-slate-50 p-6 rounded-full mb-4">
                    <i data-lucide="coffee" class="text-slate-300 w-10 h-10"></i>
                </div>
                <h4 class="text-slate-900 font-bold text-lg mb-1">Bugun Darslar Yo'q</h4>
                <p class="text-slate-500 text-sm max-w-xs">Bugungi kun (<?= $day_name ?>) uchun o'quvchilar rejalashtirilmagan.</p>
            </div>
        <?php else: ?>
        <div class="overflow-hidden rounded-2xl border border-slate-200/60 shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/80 backdrop-blur-sm text-slate-500 text-[10px] font-bold uppercase tracking-widest border-b border-slate-200/60">
                    <tr>
                        <th class="p-4 pl-6 w-1/3">O'quvchi</th>
                        <th class="p-4 w-1/6">Guruh</th>
                        <th class="p-4 text-center">Davomat Holati</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php foreach($students as $row):
                        $current_status = $row['today_status'] ?? 'Present';
                    ?>
                    <tr class="group hover:bg-indigo-50/30 transition duration-200">
                        <td class="p-4 pl-6">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-100 to-white border border-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs shadow-sm">
                                    <?= htmlspecialchars(substr($row['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 text-sm mb-0.5 group-hover:text-indigo-600 transition"><?= htmlspecialchars($row['name']) ?></p>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide bg-slate-100 px-2 py-0.5 rounded text-xs border border-slate-200/50"><?= htmlspecialchars($row['s_name']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="text-xs font-bold text-slate-500 bg-white border border-slate-200 px-2 py-1 rounded-md shadow-sm">
                                <?= htmlspecialchars($row['group_name']) ?>
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="flex justify-center gap-2">
                                <?php if($is_admin): ?>
                                    <?php
                                    $statuses = ['Present' => 'Keldi', 'Absent' => 'Kelmadi', 'Late' => 'Kechikdi'];
                                    foreach($statuses as $val => $label): ?>
                                    <label class="cursor-pointer relative">
                                        <input type="radio" name="att[<?=$row['id']?>]" value="<?=$val?>" class="hidden peer" <?= $val == $current_status ? 'checked' : '' ?>>
                                        <span class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-500 font-bold text-[11px] transition-all duration-200 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 peer-checked:shadow-md hover:bg-slate-50 block text-center min-w-[70px]">
                                            <?=$label?>
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

        <?php if($is_admin): ?>
        <div class="mt-8 text-center sticky bottom-0 z-20">
            <button name="save_att" class="bg-slate-900 text-white px-8 py-3 rounded-xl font-bold text-sm hover:scale-105 hover:bg-indigo-600 transition-all duration-300 shadow-xl shadow-slate-900/20 flex items-center gap-2 mx-auto">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Davomatni Saqlash</span>
            </button>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </form>
</div>
