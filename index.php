<?php
session_start();
ob_start();

// --- 1. DATABASE & SYSTEM CORE ---
$db_dir = __DIR__ . '/data';
if (!file_exists($db_dir)) mkdir($db_dir, 0777, true);
$db = new PDO('sqlite:' . $db_dir . '/oxford_infinity.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Advanced Schema
$db->exec("CREATE TABLE IF NOT EXISTS schools (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, username TEXT UNIQUE, password TEXT, contact TEXT, joined_date DATE DEFAULT CURRENT_DATE)");
$db->exec("CREATE TABLE IF NOT EXISTS students (id INTEGER PRIMARY KEY AUTOINCREMENT, school_id INTEGER, name TEXT, group_name TEXT, status TEXT DEFAULT 'Active', FOREIGN KEY(school_id) REFERENCES schools(id))");
$db->exec("CREATE TABLE IF NOT EXISTS attendance (id INTEGER PRIMARY KEY AUTOINCREMENT, student_id INTEGER, status TEXT, date DATE DEFAULT CURRENT_DATE, marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

// Create Master Admin (admin / admin123)
if (!$db->query("SELECT 1 FROM schools WHERE username = 'admin'")->fetch()) {
    $db->prepare("INSERT INTO schools (name, username, password) VALUES (?, ?, ?)")->execute(['Oxford LC Master', 'admin', password_hash('admin123', PASSWORD_DEFAULT)]);
}

// Auth Controller
if (isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); exit; }
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $s = $db->prepare("SELECT * FROM schools WHERE username = ?");
    $s->execute([$_POST['user']]);
    $u = $s->fetch();
    if ($u && password_verify($_POST['pass'], $u['password'])) {
        $_SESSION['user_id'] = $u['id']; $_SESSION['username'] = $u['username']; $_SESSION['name'] = $u['name'];
        header("Location: index.php"); exit;
    } $err = "Invalid credentials";
}

$is_admin = ($_SESSION['username'] ?? '') === 'admin';
$uid = $_SESSION['user_id'] ?? 0;

// --- 2. ADMINISTRATIVE LOGIC ---
if ($is_admin) {
    if (isset($_POST['add_school'])) {
        $db->prepare("INSERT INTO schools (name, username, password, contact) VALUES (?, ?, ?, ?)")
           ->execute([$_POST['sch_name'], $_POST['sch_user'], password_hash($_POST['sch_pass'], PASSWORD_DEFAULT), $_POST['sch_contact']]);
    }
    if (isset($_POST['add_student'])) {
        $db->prepare("INSERT INTO students (name, school_id, group_name) VALUES (?, ?, ?)")
           ->execute([$_POST['st_name'], $_POST['st_school'], $_POST['st_group']]);
    }
    if (isset($_POST['save_att'])) {
        foreach ($_POST['att'] as $sid => $status) {
            $db->prepare("INSERT INTO attendance (student_id, status) VALUES (?, ?)")->execute([$sid, $status]);
        }
        $success_msg = "Attendance synchronized!";
    }
    if (isset($_GET['del_st'])) { $db->prepare("DELETE FROM students WHERE id = ?")->execute([$_GET['del_st']]); header("Location: index.php?p=students"); }
}

// --- 3. DATA AGGREGATION FOR REPORTS ---
$total_students = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_schools = $db->query("SELECT COUNT(*) FROM schools WHERE id > 1")->fetchColumn();
$absent_today = $db->query("SELECT COUNT(*) FROM attendance WHERE status='Absent' AND date=date('now')")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Oxford LC Infinity | Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #f8fafc; }
        .custom-gradient { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); }
        .tab-active { background: #6366f1; color: white; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3); }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['user_id'])): ?>
    <div class="min-h-screen flex items-center justify-center p-6 bg-[#020617]">
        <div class="w-full max-w-md bg-white rounded-[3rem] shadow-2xl p-12 text-center border border-slate-800/10">
            <div class="inline-flex p-5 bg-indigo-600 rounded-3xl mb-6 shadow-xl shadow-indigo-500/20"><i data-lucide="zap" class="text-white w-10 h-10"></i></div>
            <h1 class="text-4xl font-black text-slate-900 mb-2">Oxford LC</h1>
            <p class="text-slate-400 mb-10 font-medium">Enterprise Student Tracking</p>
            <form method="POST" class="space-y-4">
                <input type="text" name="user" placeholder="Identifier" class="w-full p-5 bg-slate-50 border border-slate-100 rounded-3xl focus:ring-4 focus:ring-indigo-500/10 outline-none transition" required>
                <input type="password" name="pass" placeholder="Secret Key" class="w-full p-5 bg-slate-50 border border-slate-100 rounded-3xl focus:ring-4 focus:ring-indigo-500/10 outline-none transition" required>
                <button name="login" class="w-full bg-slate-900 text-white font-bold p-5 rounded-3xl hover:bg-indigo-600 transition shadow-2xl shadow-indigo-500/20">Authorize Access</button>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="flex min-h-screen">
        <aside class="w-80 custom-gradient text-slate-400 p-10 flex flex-col fixed h-full shadow-2xl">
            <div class="flex items-center gap-4 text-white mb-20">
                <div class="bg-indigo-500 p-2 rounded-xl"><i data-lucide="component" class="w-6 h-6"></i></div>
                <span class="text-2xl font-black tracking-tighter">INFINITY <span class="text-indigo-400">LC</span></span>
            </div>
            
            <nav class="space-y-4 flex-1">
                <div class="px-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-4">Navigation</div>
                <a href="index.php" class="flex items-center gap-4 p-4 rounded-2xl transition <?= !isset($_GET['p']) ? 'tab-active' : 'hover:bg-white/5 hover:text-white' ?>"><i data-lucide="layout-dashboard"></i> Dashboard</a>
                <a href="?p=attendance" class="flex items-center gap-4 p-4 rounded-2xl transition <?= $_GET['p']=='attendance' ? 'tab-active' : 'hover:bg-white/5 hover:text-white' ?>"><i data-lucide="calendar-check"></i> Attendance</a>
                <a href="?p=students" class="flex items-center gap-4 p-4 rounded-2xl transition <?= $_GET['p']=='students' ? 'tab-active' : 'hover:bg-white/5 hover:text-white' ?>"><i data-lucide="users"></i> Students</a>
                <a href="?p=reports" class="flex items-center gap-4 p-4 rounded-2xl transition <?= $_GET['p']=='reports' ? 'tab-active' : 'hover:bg-white/5 hover:text-white' ?>"><i data-lucide="bar-chart-horizontal"></i> Analytics</a>
            </nav>

            <div class="mt-auto pt-10 border-t border-white/5">
                <a href="?logout=1" class="flex items-center gap-4 p-4 rounded-2xl text-rose-400 hover:bg-rose-400/10 transition"><i data-lucide="log-out"></i> Termination</a>
            </div>
        </aside>

        <main class="ml-80 flex-1 p-16">
            <header class="flex justify-between items-center mb-16">
                <div>
                    <h2 class="text-slate-400 font-bold text-xs uppercase tracking-[0.3em] mb-2">Enterprise Console</h2>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tight"><?= ucfirst($_GET['p'] ?? 'Overview') ?></h1>
                </div>
                <div class="flex items-center gap-6 bg-white p-3 rounded-[2rem] border border-slate-200/50 shadow-sm">
                    <div class="px-6 border-r border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Live Server Status</p>
                        <p class="text-sm font-black text-emerald-500 flex items-center gap-2"><span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span> Operational</p>
                    </div>
                    <div class="flex items-center gap-3 pr-4">
                        <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black"><?= strtoupper(substr($_SESSION['name'], 0, 1)) ?></div>
                        <div><p class="text-xs font-bold text-slate-900"><?= $_SESSION['name'] ?></p><p class="text-[10px] text-slate-400"><?= $is_admin ? 'Master Admin' : 'Partner' ?></p></div>
                    </div>
                </div>
            </header>

            <?php if(!isset($_GET['p'])): ?>
                <div class="grid grid-cols-4 gap-8 mb-12">
                    <div class="col-span-1 bg-white p-10 rounded-[3rem] shadow-sm border border-slate-200/50">
                        <i data-lucide="user-group" class="text-indigo-500 mb-6"></i>
                        <p class="text-slate-400 font-bold text-xs uppercase mb-2">Students</p>
                        <h3 class="text-5xl font-black"><?= $total_students ?></h3>
                    </div>
                    <div class="col-span-1 bg-white p-10 rounded-[3rem] shadow-sm border border-slate-200/50">
                        <i data-lucide="building" class="text-blue-500 mb-6"></i>
                        <p class="text-slate-400 font-bold text-xs uppercase mb-2">Schools</p>
                        <h3 class="text-5xl font-black"><?= $total_schools ?></h3>
                    </div>
                    <div class="col-span-2 bg-slate-900 p-10 rounded-[3rem] text-white shadow-2xl shadow-indigo-200">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-indigo-400 font-bold text-xs uppercase mb-2">Daily Absence Rate</p>
                                <h3 class="text-5xl font-black"><?= $total_students > 0 ? round(($absent_today / $total_students) * 100, 1) : 0 ?>%</h3>
                            </div>
                            <i data-lucide="activity" class="text-indigo-400 w-10 h-10"></i>
                        </div>
                    </div>
                </div>

                <?php if($is_admin): ?>
                <div class="grid grid-cols-2 gap-10">
                    <div class="bg-white p-12 rounded-[3.5rem] border border-slate-200/50">
                        <h3 class="text-2xl font-black mb-8">Register Partner School</h3>
                        <form method="POST" class="space-y-5">
                            <input type="text" name="sch_name" placeholder="Official Institution Name" class="w-full p-5 bg-slate-50 rounded-3xl outline-none focus:ring-2 focus:ring-indigo-500 border-none">
                            <input type="text" name="sch_user" placeholder="Assigned Username" class="w-full p-5 bg-slate-50 rounded-3xl outline-none focus:ring-2 focus:ring-indigo-500 border-none">
                            <input type="password" name="sch_pass" placeholder="Password Access" class="w-full p-5 bg-slate-50 rounded-3xl outline-none focus:ring-2 focus:ring-indigo-500 border-none">
                            <button name="add_school" class="w-full bg-indigo-600 text-white font-bold p-5 rounded-3xl hover:bg-slate-900 transition shadow-xl shadow-indigo-100">Establish Partnership</button>
                        </form>
                    </div>
                    <div class="bg-indigo-50 p-12 rounded-[3.5rem] border border-indigo-100">
                        <h3 class="text-2xl font-black mb-8 text-indigo-900">Enroll New Student</h3>
                        <form method="POST" class="space-y-5">
                            <input type="text" name="st_name" placeholder="Full Name" class="w-full p-5 bg-white rounded-3xl outline-none focus:ring-2 focus:ring-indigo-500 border-none shadow-sm">
                            <select name="st_school" class="w-full p-5 bg-white rounded-3xl outline-none border-none shadow-sm">
                                <?php foreach($db->query("SELECT * FROM schools WHERE id > 1") as $s): ?>
                                    <option value="<?=$s['id']?>"><?=$s['name']?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="st_group" placeholder="Educational Group (e.g. IELTS Foundation)" class="w-full p-5 bg-white rounded-3xl outline-none focus:ring-2 focus:ring-indigo-500 border-none shadow-sm">
                            <button name="add_student" class="w-full bg-slate-900 text-white font-bold p-5 rounded-3xl hover:bg-indigo-600 transition">Confirm Enrollment</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

            <?php elseif($_GET['p'] == 'attendance'): ?>
                <div class="bg-white rounded-[3.5rem] p-12 border border-slate-200/50 shadow-sm">
                    <div class="flex justify-between items-center mb-10">
                        <h3 class="text-3xl font-black">Daily Roster</h3>
                        <div class="bg-indigo-600 text-white px-8 py-3 rounded-2xl font-bold"><?= date('M d, Y') ?></div>
                    </div>
                    <form method="POST">
                        <table class="w-full">
                            <thead>
                                <tr class="text-slate-400 text-[11px] font-black uppercase tracking-widest border-b border-slate-50">
                                    <th class="p-6 text-left">Student Information</th>
                                    <th class="p-6 text-center">Status Assignment</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php foreach($db->query("SELECT s.*, sc.name as s_name FROM students s JOIN schools sc ON s.school_id = sc.id") as $row): ?>
                                <tr class="group hover:bg-slate-50 transition">
                                    <td class="p-6">
                                        <p class="font-black text-slate-800 text-lg"><?= $row['name'] ?></p>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-tighter"><?= $row['s_name'] ?> • <?= $row['group_name'] ?></p>
                                    </td>
                                    <td class="p-6">
                                        <div class="flex justify-center gap-4">
                                            <?php foreach(['Present', 'Absent', 'Late'] as $status): ?>
                                            <label class="cursor-pointer">
                                                <input type="radio" name="att[<?=$row['id']?>]" value="<?=$status?>" class="hidden peer" <?= $status == 'Present' ? 'checked' : '' ?>>
                                                <span class="px-8 py-3 rounded-2xl border border-slate-100 bg-slate-50 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:shadow-lg transition block font-bold text-sm"><?=$status?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="mt-12 text-center">
                            <button name="save_att" class="bg-slate-900 text-white px-16 py-6 rounded-[2.5rem] font-black text-xl hover:scale-105 transition shadow-2xl">Publish Attendance Data</button>
                        </div>
                    </form>
                </div>

            <?php elseif($_GET['p'] == 'students'): ?>
                <div class="bg-white rounded-[3.5rem] p-12 shadow-sm border border-slate-200/50">
                    <div class="flex justify-between items-center mb-12">
                        <h3 class="text-3xl font-black">Managed Students</h3>
                        <input type="text" placeholder="Search Database..." class="p-4 bg-slate-50 rounded-2xl border-none outline-none w-80 shadow-inner">
                    </div>
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-slate-400 text-xs font-black uppercase tracking-widest">
                            <tr><th class="p-8">Name</th><th class="p-8">School</th><th class="p-8">Group</th><th class="p-8 text-right">Actions</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach($db->query("SELECT s.*, sc.name as sname FROM students s JOIN schools sc ON s.school_id = sc.id") as $s): ?>
                            <tr>
                                <td class="p-8 font-bold text-slate-800 text-xl"><?= $s['name'] ?></td>
                                <td class="p-8 text-indigo-600 font-black italic"><?= $s['sname'] ?></td>
                                <td class="p-8"><span class="bg-slate-100 px-4 py-2 rounded-xl text-xs font-bold text-slate-600"><?= $s['group_name'] ?></span></td>
                                <td class="p-8 text-right">
                                    <a href="?del_st=<?=$s['id']?>" class="text-rose-400 hover:text-rose-600 transition"><i data-lucide="trash-2"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif($_GET['p'] == 'reports'): ?>
                <div class="grid grid-cols-2 gap-10">
                    <div class="bg-white p-12 rounded-[3.5rem] border border-slate-200/50 shadow-sm">
                        <h3 class="text-2xl font-black mb-10 flex items-center gap-3"><i data-lucide="bar-chart-2" class="text-indigo-500"></i> Performance Trends</h3>
                        <div class="space-y-8">
                            <?php 
                            $groups = $db->query("SELECT group_name, COUNT(*) as c FROM students GROUP BY group_name ORDER BY c DESC LIMIT 4")->fetchAll();
                            foreach($groups as $g): ?>
                            <div>
                                <div class="flex justify-between mb-3 font-bold text-slate-700"><span><?= $g['group_name'] ?></span><span><?= $g['c'] ?> Enrolled</span></div>
                                <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden"><div class="bg-indigo-600 h-full" style="width: <?= min(($g['c']/20)*100, 100) ?>%"></div></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="bg-white p-12 rounded-[3.5rem] border border-slate-200/50 shadow-sm">
                        <h3 class="text-2xl font-black mb-10 flex items-center gap-3 text-rose-500"><i data-lucide="alert-circle"></i> Intervention Required</h3>
                        <div class="space-y-6">
                            <?php 
                            $crit = $db->query("SELECT s.name, COUNT(a.id) as abs FROM students s JOIN attendance a ON s.id = a.student_id WHERE a.status = 'Absent' GROUP BY s.id HAVING abs > 0 ORDER BY abs DESC LIMIT 5")->fetchAll();
                            foreach($crit as $c): ?>
                            <div class="flex items-center justify-between p-4 bg-rose-50 rounded-2xl border border-rose-100">
                                <span class="font-bold text-rose-900"><?= $c['name'] ?></span>
                                <span class="text-xs font-black bg-rose-500 text-white px-4 py-1 rounded-full uppercase"><?= $c['abs'] ?> Missed Days</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="mt-10 bg-slate-900 rounded-[3.5rem] p-12 text-white overflow-hidden relative">
                    <div class="relative z-10">
                        <h3 class="text-3xl font-black mb-2 tracking-tighter">Global Audit Log</h3>
                        <p class="text-slate-500 mb-10">Historical record of all synchronized attendance sessions.</p>
                        <div class="max-h-96 overflow-y-auto custom-scroll pr-4">
                            <table class="w-full text-left">
                                <thead class="text-[10px] font-black uppercase text-slate-600 tracking-widest">
                                    <tr><th class="pb-6">Timestamp</th><th>Identity</th><th>Status</th></tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <?php 
                                    $log_q = $is_admin ? "SELECT a.*, s.name FROM attendance a JOIN students s ON a.student_id = s.id" : "SELECT a.*, s.name FROM attendance a JOIN students s ON a.student_id = s.id WHERE s.school_id = $uid";
                                    foreach($db->query($log_q . " ORDER BY a.id DESC") as $l): ?>
                                    <tr>
                                        <td class="py-6 text-slate-400 font-medium"><?= date('M d • H:i', strtotime($l['marked_at'])) ?></td>
                                        <td class="py-6 font-bold"><?= $l['name'] ?></td>
                                        <td class="py-6"><span class="px-4 py-1 rounded-full text-[10px] font-black uppercase <?= $l['status'] == 'Present' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' ?>"><?= $l['status'] ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
<?php endif; ?>

<script>
    lucide.createIcons();
    // Auto-fade success messages
    setTimeout(() => { document.querySelectorAll('.success-toast').forEach(e => e.style.display = 'none'); }, 3000);
</script>
</body>
</html>