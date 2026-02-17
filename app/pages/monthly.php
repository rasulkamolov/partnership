<?php
// app/pages/monthly.php

// 1. Get current month/year or selected month/year
$curr_month = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');
$curr_year = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');
$selected_school = isset($_GET['school_id']) ? (int)$_GET['school_id'] : 0;

$days_in_month = date('t', mktime(0, 0, 0, $curr_month, 1, $curr_year));
// Uzbek Month Names
$uz_months = [
    1 => 'Yanvar', 2 => 'Fevral', 3 => 'Mart', 4 => 'Aprel', 5 => 'May', 6 => 'Iyun',
    7 => 'Iyul', 8 => 'Avgust', 9 => 'Sentabr', 10 => 'Oktabr', 11 => 'Noyabr', 12 => 'Dekabr'
];
$month_name = $uz_months[$curr_month];

// 2. Fetch students
$sql = "SELECT s.*, sc.name as sname FROM students s JOIN schools sc ON s.school_id = sc.id WHERE 1=1";
$params = [];

if (!$is_admin) {
    // Partner restriction
    $sql .= " AND s.school_id = ?";
    $params[] = $uid;
} elseif ($selected_school > 0) {
    // Admin filtering by school
    $sql .= " AND s.school_id = ?";
    $params[] = $selected_school;
}

$sql .= " ORDER BY sname, s.name";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

// 3. Fetch all attendance records for this month (and optionally school)
$start_date = sprintf('%04d-%02d-01', $curr_year, $curr_month);
$end_date = sprintf('%04d-%02d-%02d', $curr_year, $curr_month, $days_in_month);

$att_sql = "SELECT a.student_id, strftime('%d', a.date) as day, a.status
            FROM attendance a
            JOIN students s ON a.student_id = s.id
            WHERE a.date BETWEEN ? AND ?";
$att_params = [$start_date, $end_date];

if (!$is_admin) {
    $att_sql .= " AND s.school_id = ?";
    $att_params[] = $uid;
} elseif ($selected_school > 0) {
    $att_sql .= " AND s.school_id = ?";
    $att_params[] = $selected_school;
}

$att_stmt = $db->prepare($att_sql);
$att_stmt->execute($att_params);
$attendance_records = $att_stmt->fetchAll(PDO::FETCH_GROUP);
// Result: [student_id => [ [day=>'01', status=>'Present'], ... ]]

// Helper to check status
function get_day_status($sid, $day, $records) {
    $day_str = sprintf('%02d', $day);
    if (isset($records[$sid])) {
        foreach ($records[$sid] as $rec) {
            if ($rec['day'] == $day_str) return $rec['status'];
        }
    }
    return null;
}
?>

<div class="bg-white rounded-3xl p-8 border border-slate-200/60 shadow-sm overflow-hidden min-h-[600px] flex flex-col">
    <!-- Header Controls -->
    <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
        <div>
            <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-1">Oylik Hisobot</h3>
            <p class="text-slate-500 text-sm font-medium">Davomat bo'yicha batafsil ma'lumot: <span class="text-indigo-600 font-bold"><?= $month_name ?> <?= $curr_year ?></span></p>
        </div>

        <form method="GET" class="flex items-center gap-2 bg-slate-50 p-1.5 rounded-xl border border-slate-200/60 shadow-sm flex-wrap">
            <input type="hidden" name="p" value="monthly">

            <?php if($is_admin): ?>
            <select name="school_id" class="px-3 py-2 bg-white rounded-lg font-bold text-xs border border-slate-200 text-slate-600 outline-none focus:border-indigo-500 cursor-pointer max-w-[150px]">
                <option value="0">Barcha Maktablar</option>
                <?php foreach($db->query("SELECT id, name FROM schools WHERE id > 1") as $sch): ?>
                    <option value="<?= $sch['id'] ?>" <?= $sch['id'] == $selected_school ? 'selected' : '' ?>><?= htmlspecialchars($sch['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>

            <select name="m" class="px-3 py-2 bg-white rounded-lg font-bold text-xs border border-slate-200 text-slate-600 outline-none focus:border-indigo-500 cursor-pointer">
                <?php foreach($uz_months as $num => $name): ?>
                    <option value="<?=$num?>" <?= $num==$curr_month ? 'selected' : '' ?>><?= $name ?></option>
                <?php endforeach; ?>
            </select>

            <select name="y" class="px-3 py-2 bg-white rounded-lg font-bold text-xs border border-slate-200 text-slate-600 outline-none focus:border-indigo-500 cursor-pointer">
                <?php for($y=date('Y')-1; $y<=date('Y')+1; $y++): ?>
                    <option value="<?=$y?>" <?= $y==$curr_year ? 'selected' : '' ?>><?=$y?></option>
                <?php endfor; ?>
            </select>

            <button class="bg-indigo-600 text-white p-2 rounded-lg hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/20">
                <i data-lucide="filter" class="w-4 h-4"></i>
            </button>
        </form>
    </div>

    <!-- Scrollable Table -->
    <div class="flex-1 overflow-auto custom-scroll relative rounded-2xl border border-slate-200/60">
        <table class="w-full text-left border-collapse min-w-[800px]">
            <thead class="bg-slate-50/80 text-slate-500 text-[10px] font-bold uppercase tracking-widest border-b border-slate-200 sticky top-0 z-20 backdrop-blur-sm shadow-sm">
                <tr>
                    <th class="p-3 pl-4 sticky left-0 z-30 bg-slate-50 border-r border-slate-200 min-w-[200px]">O'quvchi</th>
                    <?php for($d=1; $d<=$days_in_month; $d++):
                        $ts = mktime(0,0,0,$curr_month,$d,$curr_year);
                        $day_num = date('N', $ts); // 1=Mon, 7=Sun
                        $is_weekend = ($day_num >= 7); // Sunday
                        $bg_class = $is_weekend ? 'bg-slate-100/50 text-slate-300' : '';
                    ?>
                        <th class="p-2 text-center border-r border-slate-100 min-w-[32px] <?= $bg_class ?>">
                            <div class="flex flex-col items-center">
                                <span><?= $d ?></span>
                                <span class="text-[8px] opacity-60"><?= substr(date('D', $ts), 0, 1) ?></span>
                            </div>
                        </th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white text-xs">
                <?php if(empty($students)): ?>
                    <tr><td colspan="<?= $days_in_month + 1 ?>" class="p-10 text-center text-slate-400 font-bold">Tanlangan mezonlar bo'yicha o'quvchilar topilmadi.</td></tr>
                <?php endif; ?>

                <?php foreach($students as $s): ?>
                <tr class="group hover:bg-indigo-50/10 transition duration-150">
                    <td class="p-3 pl-4 sticky left-0 z-10 bg-white border-r border-slate-100 group-hover:bg-indigo-50/10 transition font-bold text-slate-700">
                        <div class="flex flex-col">
                            <span class="truncate max-w-[180px]"><?= htmlspecialchars($s['name']) ?></span>
                            <span class="text-[9px] text-slate-400 font-normal uppercase"><?= htmlspecialchars($s['group_name']) ?></span>
                        </div>
                    </td>
                    <?php for($d=1; $d<=$days_in_month; $d++):
                        $status = get_day_status($s['id'], $d, $attendance_records);
                        $ts = mktime(0,0,0,$curr_month,$d,$curr_year);
                        $is_weekend = (date('N', $ts) >= 7);
                        $bg_class = $is_weekend ? 'bg-slate-50' : '';

                        // Icon Logic
                        $icon = '<div class="w-1 h-1 bg-slate-200 rounded-full mx-auto"></div>';
                        if ($status == 'Present') {
                            $icon = '<div class="mx-auto w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shadow-sm"><i data-lucide="check" class="w-3 h-3"></i></div>';
                        } elseif ($status == 'Absent') {
                            $icon = '<div class="mx-auto w-5 h-5 rounded-full bg-rose-100 text-rose-500 flex items-center justify-center shadow-sm"><i data-lucide="x" class="w-3 h-3"></i></div>';
                        } elseif ($status == 'Late') {
                            $icon = '<div class="mx-auto w-5 h-5 rounded-full bg-amber-100 text-amber-500 flex items-center justify-center shadow-sm"><i data-lucide="clock" class="w-3 h-3"></i></div>';
                        }
                    ?>
                        <td class="p-1 text-center border-r border-slate-50 <?= $bg_class ?>">
                            <?= $icon ?>
                        </td>
                    <?php endfor; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex items-center gap-6 text-[10px] font-bold text-slate-500 uppercase tracking-wide justify-center border-t border-slate-100 pt-6">
        <div class="flex items-center gap-2"><div class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center"><i data-lucide="check" class="w-2.5 h-2.5"></i></div> Keldi</div>
        <div class="flex items-center gap-2"><div class="w-4 h-4 rounded-full bg-rose-100 text-rose-500 flex items-center justify-center"><i data-lucide="x" class="w-2.5 h-2.5"></i></div> Kelmadi</div>
        <div class="flex items-center gap-2"><div class="w-4 h-4 rounded-full bg-amber-100 text-amber-500 flex items-center justify-center"><i data-lucide="clock" class="w-2.5 h-2.5"></i></div> Kechikdi</div>
        <div class="flex items-center gap-2"><div class="w-1.5 h-1.5 bg-slate-300 rounded-full"></div> Dars Yo'q</div>
    </div>
</div>
