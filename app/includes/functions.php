<?php
// app/includes/functions.php

function get_teaching_days_count($month, $year, $schedule_type) {
    $count = 0;
    $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
    for ($d = 1; $d <= $days_in_month; $d++) {
        $timestamp = mktime(0, 0, 0, $month, $d, $year);
        $weekday = date('N', $timestamp); // 1 (Mon) - 7 (Sun)

        // Count Odd Days (Mon, Wed, Fri)
        if ($schedule_type == 'odd' && in_array($weekday, [1, 3, 5])) $count++;

        // Count Even Days (Tue, Thu, Sat)
        if ($schedule_type == 'even' && in_array($weekday, [2, 4, 6])) $count++;

        // Count Every Day (Mon - Sat)
        if ($schedule_type == 'everyday' && $weekday <= 6) $count++;
    }
    return $count;
}

function render_currency($amount) {
    return number_format($amount, 0) . ' UZS';
}

function get_student_status_badge($status) {
    $status_uz = match($status) {
        'Present' => 'Keldi',
        'Absent' => 'Kelmadi',
        'Late' => 'Kechikdi',
        default => 'Belgilanmagan'
    };
    $classes = match($status) {
        'Present' => 'bg-emerald-50 text-emerald-600 border border-emerald-100',
        'Absent' => 'bg-rose-50 text-rose-600 border border-rose-100',
        'Late' => 'bg-amber-50 text-amber-600 border border-amber-100',
        default => 'bg-slate-50 text-slate-500 border border-slate-100'
    };
    return "<span class=\"px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wide border $classes\">$status_uz</span>";
}
?>
