<?php
// app/pages/reports.php

$curr_month = date('n');
$curr_year = date('Y');
$uz_months = [
    1 => 'Yanvar', 2 => 'Fevral', 3 => 'Mart', 4 => 'Aprel', 5 => 'May', 6 => 'Iyun',
    7 => 'Iyul', 8 => 'Avgust', 9 => 'Sentabr', 10 => 'Oktabr', 11 => 'Noyabr', 12 => 'Dekabr'
];
$month_name = $uz_months[$curr_month];
$financials = [];
$total_revenue = 0;

if ($is_admin) {
    $students = $db->query("SELECT s.*, sc.name as school_name FROM students s JOIN schools sc ON s.school_id = sc.id")->fetchAll();
    foreach($students as $st) {
        $teaching_days = get_teaching_days_count($curr_month, $curr_year, $st['schedule_type']);
        $stmt = $db->prepare("SELECT COUNT(*) FROM attendance WHERE student_id = ? AND status = 'Present' AND strftime('%m', date) = ? AND strftime('%Y', date) = ?");
        $stmt->execute([$st['id'], sprintf('%02d', $curr_month), $curr_year]);
        $attended = $stmt->fetchColumn();

        $earned = 0;
        if ($teaching_days > 0) {
            $earned = ($st['monthly_fee'] / $teaching_days) * $attended;
        }

        $total_revenue += $earned;
        $financials[] = [
            'name' => $st['name'],
            'school' => $st['school_name'],
            'fee' => $st['monthly_fee'],
            'schedule' => $st['schedule_type'],
            'days' => $teaching_days,
            'attended' => $attended,
            'earned' => $earned
        ];
    }
    $total_gov = $total_revenue * 0.8;
    $total_parent = $total_revenue * 0.2;
}

// Analytics Logic (Secured)
$start_date = $_POST['start_date'] ?? date('Y-m-01');
$end_date = $_POST['end_date'] ?? date('Y-m-t');

// Prepare base filter SQL and parameters
$filter_sql = "status != '' AND date BETWEEN :start AND :end";
$params = [':start' => $start_date, ':end' => $end_date];

if (!$is_admin) {
    $filter_sql .= " AND student_id IN (SELECT id FROM students WHERE school_id = :uid)";
    $params[':uid'] = $uid;
}

$stmt = $db->prepare("SELECT COUNT(*) as total,
                        SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present,
                        SUM(CASE WHEN status='Absent' THEN 1 ELSE 0 END) as absent,
                        SUM(CASE WHEN status='Late' THEN 1 ELSE 0 END) as late
                        FROM attendance WHERE $filter_sql");
$stmt->execute($params);
$att_stats = $stmt->fetch();

$total_recs = $att_stats['total'] > 0 ? $att_stats['total'] : 1;
$att_rate = round(($att_stats['present'] / $total_recs) * 100, 1);
?>

<?php if($is_admin): ?>
<div class="grid grid-cols-3 gap-6 mb-8 animate-fade-in-up">
    <div class="bg-indigo-600 p-8 rounded-3xl text-white shadow-xl shadow-indigo-500/20 relative overflow-hidden group hover:scale-[1.02] transition duration-500">
        <div class="absolute -right-10 -bottom-10 bg-white/10 w-40 h-40 rounded-full blur-[50px] group-hover:blur-[80px] transition duration-700"></div>
        <p class="font-bold text-indigo-200 uppercase text-[10px] mb-2 tracking-widest relative z-10"><?= $month_name ?> Tushumi</p>
        <h3 class="text-3xl font-black relative z-10 tracking-tight"><?= render_currency($total_revenue) ?></h3>
        <i data-lucide="wallet" class="absolute top-6 right-6 text-white/20 w-8 h-8"></i>
    </div>
    <div class="bg-white p-8 rounded-3xl border border-slate-200/60 shadow-sm hover:scale-[1.02] transition duration-500 group">
        <p class="font-bold text-slate-400 uppercase text-[10px] mb-2 tracking-widest">Davlat Subsidiyasi (80%)</p>
        <h3 class="text-3xl font-black text-emerald-600 tracking-tight group-hover:text-emerald-500 transition"><?= render_currency($total_gov) ?></h3>
    </div>
    <div class="bg-white p-8 rounded-3xl border border-slate-200/60 shadow-sm hover:scale-[1.02] transition duration-500 group">
        <p class="font-bold text-slate-400 uppercase text-[10px] mb-2 tracking-widest">Ota-ona To'lovi (20%)</p>
        <h3 class="text-3xl font-black text-slate-800 tracking-tight group-hover:text-indigo-600 transition"><?= render_currency($total_parent) ?></h3>
    </div>
</div>

<div class="bg-white rounded-3xl p-8 mb-8 border border-slate-200/60 shadow-sm overflow-hidden hover:shadow-lg transition duration-500">
    <div class="flex items-center gap-3 mb-6">
        <div class="bg-indigo-50 p-2 rounded-xl"><i data-lucide="table" class="text-indigo-600 w-5 h-5"></i></div>
        <h3 class="text-xl font-black text-slate-900">Moliyaviy Hisobot</h3>
    </div>
    <div class="max-h-80 overflow-y-auto custom-scroll pr-2">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50/80 backdrop-blur-sm text-slate-400 text-[10px] font-black uppercase tracking-widest sticky top-0 z-10 border-b border-slate-100">
                <tr><th class="p-4 pl-6">O'quvchi</th><th class="p-4">Qatnashdi</th><th class="p-4">Ishlab Topildi</th><th class="p-4">Davlat (80%)</th><th class="p-4 pr-6">Ota-ona (20%)</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php foreach($financials as $f): ?>
                <tr class="hover:bg-slate-50/50 transition duration-200">
                    <td class="p-4 pl-6">
                        <div class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($f['name']) ?></div>
                        <div class="text-[10px] uppercase font-bold text-slate-400 mt-0.5 flex items-center gap-2">
                            <span class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-500 border border-slate-200/50"><?= htmlspecialchars($f['school']) ?></span>
                            <span class="text-indigo-400">
                                <?php
                                $sch = htmlspecialchars($f['schedule']);
                                if ($sch == 'odd') echo 'Toq';
                                elseif ($sch == 'even') echo 'Juft';
                                else echo 'Har Kuni';
                                ?>
                            </span>
                        </div>
                    </td>
                    <td class="p-4 font-bold text-indigo-600 text-sm"><?= $f['attended'] ?> <span class="text-slate-300 font-normal">/</span> <?= $f['days'] ?></td>
                    <td class="p-4 font-bold text-slate-700 text-sm"><?= number_format($f['earned'], 0) ?></td>
                    <td class="p-4"><span class="text-emerald-600 font-bold text-xs bg-emerald-50/50 px-2 py-1 rounded border border-emerald-100/50"><?= number_format($f['earned'] * 0.8, 0) ?></span></td>
                    <td class="p-4"><span class="text-slate-500 font-bold text-xs bg-slate-100/50 px-2 py-1 rounded border border-slate-200/50"><?= number_format($f['earned'] * 0.2, 0) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="bg-white rounded-3xl p-8 mb-8 border border-slate-200/60 shadow-sm hover:shadow-lg transition duration-500">
    <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <div class="bg-rose-50 p-2 rounded-xl"><i data-lucide="pie-chart" class="text-rose-500 w-5 h-5"></i></div>
            <h3 class="text-xl font-black text-slate-900">Davomat Analitikasi</h3>
        </div>
        <form method="POST" class="flex gap-2 bg-slate-50 p-1.5 rounded-2xl border border-slate-200/60 shadow-inner">
            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" class="px-3 py-2 bg-white rounded-xl font-bold text-[11px] border border-slate-200 text-slate-600 outline-none focus:border-indigo-500 transition shadow-sm">
            <span class="self-center text-slate-300 font-bold">-</span>
            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" class="px-3 py-2 bg-white rounded-xl font-bold text-[11px] border border-slate-200 text-slate-600 outline-none focus:border-indigo-500 transition shadow-sm">
            <button class="bg-indigo-600 text-white px-4 rounded-xl font-bold text-[11px] hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/20">Filtrlash</button>
            <button type="submit" name="export_report" value="1" class="bg-emerald-500 text-white px-4 rounded-xl font-bold text-[11px] hover:bg-emerald-600 transition shadow-lg shadow-emerald-500/20 flex items-center gap-1.5">
                <i data-lucide="download" class="w-3 h-3"></i> Eksport
            </button>
        </form>
    </div>

    <div class="grid grid-cols-4 gap-4 mb-8">
        <div class="p-6 bg-gradient-to-br from-slate-50 to-white border border-slate-100 rounded-3xl text-center shadow-sm hover:shadow-md transition duration-300 group">
            <p class="text-[10px] font-black text-slate-400 uppercase mb-1 tracking-widest">Foiz</p>
            <p class="text-3xl font-black text-indigo-600 group-hover:scale-110 transition duration-300"><?= $att_rate ?>%</p>
        </div>
        <div class="p-6 bg-gradient-to-br from-emerald-50 to-white border border-emerald-100 rounded-3xl text-center shadow-sm hover:shadow-md transition duration-300 group">
            <p class="text-[10px] font-black text-emerald-400 uppercase mb-1 tracking-widest">Keldi</p>
            <p class="text-3xl font-black text-emerald-600 group-hover:scale-110 transition duration-300"><?= $att_stats['present'] ?></p>
        </div>
        <div class="p-6 bg-gradient-to-br from-rose-50 to-white border border-rose-100 rounded-3xl text-center shadow-sm hover:shadow-md transition duration-300 group">
            <p class="text-[10px] font-black text-rose-400 uppercase mb-1 tracking-widest">Kelmadi</p>
            <p class="text-3xl font-black text-rose-500 group-hover:scale-110 transition duration-300"><?= $att_stats['absent'] ?></p>
        </div>
        <div class="p-6 bg-gradient-to-br from-slate-100 to-slate-50 border border-slate-200 rounded-3xl text-center shadow-sm hover:shadow-md transition duration-300 group">
            <p class="text-[10px] font-black text-slate-500 uppercase mb-1 tracking-widest">Jami</p>
            <p class="text-3xl font-black text-slate-700 group-hover:scale-110 transition duration-300"><?= $att_stats['total'] ?></p>
        </div>
    </div>

    <div class="bg-slate-50/50 rounded-3xl p-6 border border-slate-200/60">
        <h4 class="font-bold text-slate-400 uppercase text-[10px] mb-4 tracking-widest ml-2">Batafsil Tahlil</h4>
        <div class="max-h-80 overflow-y-auto custom-scroll pr-2">
            <table class="w-full text-left">
                <thead class="text-slate-400 text-[10px] font-black uppercase tracking-widest sticky top-0 bg-slate-50/90 backdrop-blur-sm z-10">
                    <tr><th class="pb-3 pl-4">O'quvchi</th><th class="pb-3">Keldi</th><th class="pb-3">Kelmadi</th><th class="pb-3 pr-4 text-right">Foiz</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200/50">
                    <?php
                    $st_sql = "SELECT s.name, s.group_name, sc.name as school_name,
                                COUNT(a.id) as total,
                                SUM(CASE WHEN a.status='Present' THEN 1 ELSE 0 END) as present,
                                SUM(CASE WHEN a.status='Absent' THEN 1 ELSE 0 END) as absent
                                FROM students s
                                JOIN schools sc ON s.school_id = sc.id
                                LEFT JOIN attendance a ON s.id = a.student_id AND a.date BETWEEN :start AND :end
                                WHERE 1=1 " . ($is_admin ? "" : " AND s.school_id = :uid") .
                                " GROUP BY s.id ORDER BY present DESC";

                    $stmt = $db->prepare($st_sql);
                    $stmt->execute($params);

                    foreach($stmt->fetchAll() as $st):
                        $s_total = $st['total'] > 0 ? $st['total'] : 1;
                        $s_rate = round(($st['present'] / $s_total) * 100, 0);
                    ?>
                    <tr class="group hover:bg-white hover:shadow-sm transition duration-200 rounded-xl">
                        <td class="py-3 pl-4">
                            <p class="font-bold text-sm text-slate-900"><?= htmlspecialchars($st['name']) ?></p>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider"><?= htmlspecialchars($st['school_name']) ?></p>
                        </td>
                        <td class="py-3 font-bold text-emerald-600 text-sm"><?= $st['present'] ?></td>
                        <td class="py-3 font-bold text-rose-500 text-sm"><?= $st['absent'] ?></td>
                        <td class="py-3 pr-4 align-middle w-32">
                            <div class="flex items-center gap-2 justify-end">
                                <div class="w-16 bg-slate-200 h-1.5 rounded-full overflow-hidden shadow-inner">
                                    <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-full rounded-full transition-all duration-1000" style="width: <?= $s_rate ?>%"></div>
                                </div>
                                <p class="text-[10px] font-bold text-indigo-500 min-w-[24px] text-right"><?= $s_rate ?>%</p>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
