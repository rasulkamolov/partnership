<?php
// app/includes/functions.php

function get_teaching_days_count($month, $year, $schedule_type) {
    $count = 0;
    $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
    for ($d = 1; $d <= $days_in_month; $d++) {
        $timestamp = mktime(0, 0, 0, $month, $d, $year);
        $weekday = date('N', $timestamp); // 1 (Mon) - 7 (Sun)
        if ($schedule_type == 'odd' && in_array($weekday, [1, 3, 5])) $count++;
        if ($schedule_type == 'even' && in_array($weekday, [2, 4, 6])) $count++;
    }
    return $count;
}

function render_currency($amount) {
    return number_format($amount, 0) . ' UZS';
}

function get_student_status_badge($status) {
    $classes = match($status) {
        'Present' => 'bg-emerald-100 text-emerald-600',
        'Absent' => 'bg-rose-100 text-rose-600',
        'Late' => 'bg-amber-100 text-amber-600',
        default => 'bg-slate-100 text-slate-500'
    };
    return "<span class=\"px-4 py-1 rounded-full text-[10px] font-black uppercase $classes\">$status</span>";
}
?>
