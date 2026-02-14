<?php
// app/pages/dashboard.php
$total_students = $db->query($is_admin ? "SELECT COUNT(*) FROM students" : "SELECT COUNT(*) FROM students WHERE school_id = $uid")->fetchColumn();
$total_schools = $db->query("SELECT COUNT(*) FROM schools WHERE id > 1")->fetchColumn();
$absent_today = $db->query($is_admin ? "SELECT COUNT(*) FROM attendance WHERE status='Absent' AND date=date('now')" : "SELECT COUNT(a.id) FROM attendance a JOIN students s ON a.student_id = s.id WHERE a.status='Absent' AND a.date=date('now') AND s.school_id = $uid")->fetchColumn();
?>
<div class="grid grid-cols-4 gap-6 mb-8">
    <div class="col-span-1 bg-white p-6 rounded-3xl shadow-sm border border-slate-200/60 hover:shadow-md transition-all duration-300">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center">
                <i data-lucide="user-group" class="text-indigo-600 w-5 h-5"></i>
            </div>
            <p class="text-slate-500 font-bold text-xs uppercase tracking-wide">Jami O'quvchilar</p>
        </div>
        <h3 class="text-3xl font-black text-slate-900"><?= $total_students ?></h3>
    </div>

    <div class="col-span-1 bg-white p-6 rounded-3xl shadow-sm border border-slate-200/60 hover:shadow-md transition-all duration-300">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center">
                <i data-lucide="building" class="text-emerald-600 w-5 h-5"></i>
            </div>
            <p class="text-slate-500 font-bold text-xs uppercase tracking-wide">Hamkor Maktablar</p>
        </div>
        <h3 class="text-3xl font-black text-slate-900"><?= $total_schools ?></h3>
    </div>

    <div class="col-span-2 bg-slate-900 p-6 rounded-3xl text-white shadow-xl shadow-indigo-900/20 overflow-hidden relative group">
        <div class="absolute -right-10 -top-10 bg-indigo-600 w-32 h-32 rounded-full blur-[60px] opacity-40 group-hover:opacity-60 transition duration-500"></div>
        <div class="relative z-10 flex justify-between items-start h-full flex-col">
            <div class="w-full flex justify-between items-start">
                <div>
                    <p class="text-indigo-300 font-bold text-xs uppercase mb-1 tracking-widest">Kunlik Kelmaganlar Foizi</p>
                    <h3 class="text-4xl font-black tracking-tighter"><?= $total_students > 0 ? round(($absent_today / $total_students) * 100, 1) : 0 ?>%</h3>
                </div>
                <div class="bg-white/10 p-2 rounded-xl backdrop-blur-sm">
                    <i data-lucide="activity" class="text-indigo-300 w-5 h-5"></i>
                </div>
            </div>
            <div class="w-full bg-white/10 h-1 rounded-full mt-auto overflow-hidden">
                <div class="bg-indigo-500 h-full transition-all duration-1000" style="width: <?= $total_students > 0 ? round(($absent_today / $total_students) * 100, 1) : 0 ?>%"></div>
            </div>
        </div>
    </div>
</div>

<?php if($is_admin): ?>
<div class="grid grid-cols-2 gap-6">
    <div class="bg-white p-8 rounded-3xl border border-slate-200/60 hover:shadow-lg transition duration-500 group">
        <div class="flex items-center gap-3 mb-6">
            <div class="bg-indigo-50 p-2.5 rounded-xl text-indigo-600 group-hover:scale-110 transition"><i data-lucide="plus-circle" class="w-6 h-6"></i></div>
            <h3 class="text-xl font-black text-slate-900">Hamkor Qo'shish</h3>
        </div>
        <form method="POST" class="space-y-4">
            <div class="space-y-3">
                <input type="text" name="sch_name" placeholder="Muassasa Nomi" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-indigo-500/50 border border-slate-100 focus:bg-white transition" required>
                <div class="grid grid-cols-2 gap-3">
                    <input type="text" name="sch_user" placeholder="Foydalanuvchi nomi" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-indigo-500/50 border border-slate-100 focus:bg-white transition" required>
                    <input type="password" name="sch_pass" placeholder="Parol" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-indigo-500/50 border border-slate-100 focus:bg-white transition" required>
                </div>
                <input type="text" name="sch_contact" placeholder="Aloqa Ma'lumotlari" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-indigo-500/50 border border-slate-100 focus:bg-white transition" required>
            </div>
            <button name="add_school" class="w-full bg-indigo-600 text-white font-bold py-3.5 rounded-xl hover:bg-slate-900 transition shadow-lg shadow-indigo-500/30 flex justify-center gap-2 items-center text-sm">
                <span>Hamkorlikni Boshlash</span> <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>
    </div>

    <div class="bg-slate-50/50 p-8 rounded-3xl border border-slate-200/60 hover:shadow-lg hover:bg-white transition duration-500 group">
        <div class="flex items-center gap-3 mb-6">
            <div class="bg-white p-2.5 rounded-xl text-indigo-600 group-hover:scale-110 transition shadow-sm ring-1 ring-slate-100"><i data-lucide="user-plus" class="w-6 h-6"></i></div>
            <h3 class="text-xl font-black text-indigo-950">O'quvchi Qo'shish</h3>
        </div>
        <?php $groups_json = json_encode($db->query("SELECT * FROM groups")->fetchAll(PDO::FETCH_ASSOC)); ?>
        <script>
            const groups = <?= $groups_json ?>;
            function updateGroups() {
                const schoolId = document.querySelector('select[name="st_school"]').value;
                const groupSelect = document.querySelector('select[name="st_group"]');
                const schoolGroups = groups.filter(g => g.school_id == schoolId);
                const msg = document.getElementById('no-groups-msg');

                groupSelect.innerHTML = '<option value="" disabled selected>Guruhni Tanlang</option>';

                if (schoolGroups.length === 0) {
                    if(msg) msg.classList.remove('hidden');
                    const opt = document.createElement('option');
                    opt.disabled = true;
                    opt.textContent = "Guruhlar mavjud emas";
                    groupSelect.appendChild(opt);
                } else {
                    if(msg) msg.classList.add('hidden');
                    schoolGroups.forEach(g => {
                        const opt = document.createElement('option');
                        opt.value = g.name;
                        opt.textContent = g.name;
                        opt.dataset.price = g.price;
                        opt.dataset.schedule = g.schedule_type;
                        groupSelect.appendChild(opt);
                    });
                }
                document.querySelector('input[name="st_fee"]').value = '';
                document.querySelector('select[name="st_schedule"]').value = 'odd';
            }
            function updateGroupDetails() {
                const groupSelect = document.querySelector('select[name="st_group"]');
                const selectedOpt = groupSelect.options[groupSelect.selectedIndex];
                if (selectedOpt && selectedOpt.dataset.price) {
                    document.querySelector('input[name="st_fee"]').value = selectedOpt.dataset.price;
                }
                if (selectedOpt && selectedOpt.dataset.schedule) {
                    document.querySelector('select[name="st_schedule"]').value = selectedOpt.dataset.schedule;
                }
            }
        </script>
        <form method="POST" class="space-y-4">
            <input type="text" name="st_name" placeholder="To'liq Ism" class="w-full px-4 py-3 bg-white rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-indigo-500/50 border border-slate-200/50 shadow-sm transition" required>
            <select name="st_school" onchange="updateGroups()" class="w-full px-4 py-3 bg-white rounded-xl text-sm font-semibold outline-none border border-slate-200/50 shadow-sm transition cursor-pointer text-slate-600" required>
                <option value="" disabled selected>Hamkor Maktabni Tanlang</option>
                <?php foreach($db->query("SELECT * FROM schools WHERE id > 1") as $s): ?>
                    <option value="<?=$s['id']?>"><?= htmlspecialchars($s['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div>
                <select name="st_group" onchange="updateGroupDetails()" class="w-full px-4 py-3 bg-white rounded-xl text-sm font-semibold outline-none border border-slate-200/50 shadow-sm transition cursor-pointer text-slate-600" required>
                    <option value="" disabled selected>Avval Maktabni Tanlang</option>
                </select>
                <div id="no-groups-msg" class="text-xs text-rose-500 font-bold mt-2 hidden flex items-center gap-1">
                    <i data-lucide="alert-circle" class="w-3 h-3"></i> Guruhlar topilmadi. <a href="?p=settings" class="underline hover:text-rose-600">Sozlamalarda yarating</a>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <input type="number" name="st_fee" placeholder="To'lov (SO'M)" class="w-full px-4 py-3 bg-white rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-indigo-500/50 border border-slate-200/50 shadow-sm transition" required>
                <select name="st_schedule" class="w-full px-4 py-3 bg-white rounded-xl text-sm font-semibold outline-none border border-slate-200/50 shadow-sm transition cursor-pointer text-slate-600">
                    <option value="odd">Toq (Du/Chor/Ju)</option>
                    <option value="even">Juft (Se/Pay/Sha)</option>
                    <option value="everyday">Har Kuni</option>
                </select>
            </div>
            <button name="add_student" class="w-full bg-slate-900 text-white font-bold py-3.5 rounded-xl hover:bg-indigo-600 transition shadow-lg flex justify-center gap-2 items-center text-sm">
                <span>Ro'yxatga Olish</span> <i data-lucide="check-circle" class="w-4 h-4"></i>
            </button>
        </form>
    </div>
</div>
<?php endif; ?>
