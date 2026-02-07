<?php
// app/pages/dashboard.php
$total_students = $db->query($is_admin ? "SELECT COUNT(*) FROM students" : "SELECT COUNT(*) FROM students WHERE school_id = $uid")->fetchColumn();
$total_schools = $db->query("SELECT COUNT(*) FROM schools WHERE id > 1")->fetchColumn();
$absent_today = $db->query($is_admin ? "SELECT COUNT(*) FROM attendance WHERE status='Absent' AND date=date('now')" : "SELECT COUNT(a.id) FROM attendance a JOIN students s ON a.student_id = s.id WHERE a.status='Absent' AND a.date=date('now') AND s.school_id = $uid")->fetchColumn();
?>
<div class="grid grid-cols-4 gap-8 mb-12">
    <div class="col-span-1 bg-white p-10 rounded-[3rem] shadow-sm border border-slate-200/50 hover:scale-105 transition-transform duration-300">
        <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center mb-6">
            <i data-lucide="user-group" class="text-indigo-600 w-8 h-8"></i>
        </div>
        <p class="text-slate-400 font-bold text-xs uppercase mb-2">Total Students</p>
        <h3 class="text-5xl font-black text-slate-900"><?= $total_students ?></h3>
    </div>

    <div class="col-span-1 bg-white p-10 rounded-[3rem] shadow-sm border border-slate-200/50 hover:scale-105 transition-transform duration-300">
        <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center mb-6">
            <i data-lucide="building" class="text-emerald-600 w-8 h-8"></i>
        </div>
        <p class="text-slate-400 font-bold text-xs uppercase mb-2">Partner Schools</p>
        <h3 class="text-5xl font-black text-slate-900"><?= $total_schools ?></h3>
    </div>

    <div class="col-span-2 bg-slate-900 p-10 rounded-[3rem] text-white shadow-2xl shadow-indigo-900/30 overflow-hidden relative group">
        <div class="absolute -right-10 -top-10 bg-indigo-600 w-40 h-40 rounded-full blur-[80px] opacity-50 group-hover:opacity-75 transition duration-500"></div>
        <div class="relative z-10 flex justify-between items-start h-full flex-col">
            <div class="w-full flex justify-between">
                <div>
                    <p class="text-indigo-300 font-bold text-xs uppercase mb-2 tracking-widest">Daily Absence Rate</p>
                    <h3 class="text-6xl font-black tracking-tighter"><?= $total_students > 0 ? round(($absent_today / $total_students) * 100, 1) : 0 ?>%</h3>
                </div>
                <div class="bg-white/10 p-3 rounded-2xl backdrop-blur-sm">
                    <i data-lucide="activity" class="text-indigo-300 w-8 h-8"></i>
                </div>
            </div>
            <div class="w-full bg-white/10 h-1.5 rounded-full mt-auto overflow-hidden">
                <div class="bg-indigo-500 h-full transition-all duration-1000" style="width: <?= $total_students > 0 ? round(($absent_today / $total_students) * 100, 1) : 0 ?>%"></div>
            </div>
        </div>
    </div>
</div>

<?php if($is_admin): ?>
<div class="grid grid-cols-2 gap-10">
    <div class="bg-white p-12 rounded-[3.5rem] border border-slate-200/50 hover:shadow-xl transition duration-500 group">
        <div class="flex items-center gap-4 mb-8">
            <div class="bg-indigo-100 p-4 rounded-2xl text-indigo-600 group-hover:scale-110 transition"><i data-lucide="plus-circle" class="w-8 h-8"></i></div>
            <h3 class="text-2xl font-black text-slate-900">Register Partner</h3>
        </div>
        <form method="POST" class="space-y-5">
            <div class="grid grid-cols-1 gap-4">
                <input type="text" name="sch_name" placeholder="Official Institution Name" class="w-full p-5 bg-slate-50 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 border border-transparent focus:bg-white transition" required>
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" name="sch_user" placeholder="Username" class="w-full p-5 bg-slate-50 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 border border-transparent focus:bg-white transition" required>
                    <input type="password" name="sch_pass" placeholder="Password" class="w-full p-5 bg-slate-50 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 border border-transparent focus:bg-white transition" required>
                </div>
                <input type="text" name="sch_contact" placeholder="Contact Information" class="w-full p-5 bg-slate-50 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 border border-transparent focus:bg-white transition" required>
            </div>
            <button name="add_school" class="w-full bg-indigo-600 text-white font-bold p-5 rounded-2xl hover:bg-slate-900 transition shadow-lg shadow-indigo-500/30 flex justify-center gap-2 items-center">
                <span>Establish Partnership</span> <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>
    </div>

    <div class="bg-indigo-50 p-12 rounded-[3.5rem] border border-indigo-100 hover:shadow-xl hover:shadow-indigo-100 transition duration-500 group">
        <div class="flex items-center gap-4 mb-8">
            <div class="bg-white p-4 rounded-2xl text-indigo-600 group-hover:scale-110 transition shadow-sm"><i data-lucide="user-plus" class="w-8 h-8"></i></div>
            <h3 class="text-2xl font-black text-indigo-900">Enroll Student</h3>
        </div>
        <form method="POST" class="space-y-5">
            <input type="text" name="st_name" placeholder="Full Name" class="w-full p-5 bg-white rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 border-none shadow-sm transition" required>
            <select name="st_school" class="w-full p-5 bg-white rounded-2xl outline-none border-none shadow-sm transition cursor-pointer" required>
                <option value="" disabled selected>Select Partner School</option>
                <?php foreach($db->query("SELECT * FROM schools WHERE id > 1") as $s): ?>
                    <option value="<?=$s['id']?>"><?= htmlspecialchars($s['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="st_group" placeholder="Educational Group" class="w-full p-5 bg-white rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 border-none shadow-sm transition" required>
            <div class="grid grid-cols-2 gap-4">
                <input type="number" name="st_fee" placeholder="Fee (UZS)" class="w-full p-5 bg-white rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 border-none shadow-sm transition" required>
                <select name="st_schedule" class="w-full p-5 bg-white rounded-2xl outline-none border-none shadow-sm transition cursor-pointer">
                    <option value="odd">Odd (M/W/F)</option>
                    <option value="even">Even (T/T/S)</option>
                </select>
            </div>
            <button name="add_student" class="w-full bg-slate-900 text-white font-bold p-5 rounded-2xl hover:bg-indigo-600 transition shadow-lg flex justify-center gap-2 items-center">
                <span>Confirm Enrollment</span> <i data-lucide="check-circle" class="w-4 h-4"></i>
            </button>
        </form>
    </div>
</div>
<?php endif; ?>
