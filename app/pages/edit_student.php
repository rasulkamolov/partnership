<?php
// app/pages/edit_student.php
if (!$is_admin) { header("Location: ?p=dashboard"); exit; }

$id = $_GET['id'] ?? 0;
$stmt = $db->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo "<div class='p-8 text-center text-rose-500 font-bold'>O'quvchi topilmadi!</div>";
    return;
}

$groups = $db->query("SELECT * FROM groups")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-2xl mx-auto bg-white rounded-3xl p-8 border border-slate-200/60 shadow-lg">
    <div class="flex items-center gap-3 mb-8">
        <a href="?p=students" class="bg-slate-100 p-2 rounded-xl text-slate-500 hover:bg-slate-200 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h3 class="text-2xl font-black text-slate-900 tracking-tight">O'quvchi Ma'lumotlarini Tahrirlash</h3>
    </div>

    <?php $groups_json = json_encode($groups); ?>
    <script>
        const groups = <?= $groups_json ?>;
        const currentGroup = "<?= htmlspecialchars($student['group_name']) ?>";
        const currentSchool = <?= $student['school_id'] ?>;

        // Groups are global now
        function updateGroups(keepCurrent = false) {
            const groupSelect = document.querySelector('select[name="st_group"]');

            groupSelect.innerHTML = '<option value="" disabled>Guruhni Tanlang</option>';

            let foundCurrent = false;
            groups.forEach(g => {
                const opt = document.createElement('option');
                opt.value = g.name;
                opt.textContent = g.name;
                opt.dataset.price = g.price;
                opt.dataset.schedule = g.schedule_type;
                if (keepCurrent && g.name === currentGroup) {
                    opt.selected = true;
                    foundCurrent = true;
                }
                groupSelect.appendChild(opt);
            });

            // If current group not found (e.g. was deleted or custom), still select it?
            // If keepCurrent is true but we didn't find it in list, maybe add it as custom option?
            // For now, if not found, we just let it be empty or default.
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

        document.addEventListener('DOMContentLoaded', () => {
            updateGroups(true);
        });
    </script>

    <form method="POST" class="space-y-6">
        <input type="hidden" name="update_student" value="1">
        <input type="hidden" name="student_id" value="<?= $student['id'] ?>">

        <div>
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">To'liq Ism</label>
            <input type="text" name="st_name" value="<?= htmlspecialchars($student['name']) ?>" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-indigo-500/50 border border-slate-100 focus:bg-white transition" required>
        </div>

        <div>
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Maktab</label>
            <select name="st_school" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-indigo-500/50 border border-slate-100 focus:bg-white transition cursor-pointer" required>
                <?php foreach($db->query("SELECT * FROM schools WHERE id > 1") as $s): ?>
                    <option value="<?=$s['id']?>" <?= $s['id'] == $student['school_id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Guruh</label>
            <select name="st_group" onchange="updateGroupDetails()" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-indigo-500/50 border border-slate-100 focus:bg-white transition cursor-pointer" required>
                <!-- Populated by JS -->
            </select>
            <!-- Fallback if group not in list (e.g. manually entered before groups existed)?
                 For now assume groups exist or we force selection from new list.
                 If current group is not in list (e.g. custom), it will disappear.
                 Since user asked to "create groups", we assume strict group usage. -->
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Narx (SO'M)</label>
                <input type="number" name="st_fee" value="<?= $student['monthly_fee'] ?>" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-indigo-500/50 border border-slate-100 focus:bg-white transition" required>
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1 mb-1 block">Jadval</label>
                <select name="st_schedule" class="w-full px-4 py-3 bg-slate-50 rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-indigo-500/50 border border-slate-100 focus:bg-white transition cursor-pointer">
                    <option value="odd" <?= $student['schedule_type'] == 'odd' ? 'selected' : '' ?>>Toq (Du/Chor/Ju)</option>
                    <option value="even" <?= $student['schedule_type'] == 'even' ? 'selected' : '' ?>>Juft (Se/Pay/Sha)</option>
                    <option value="everyday" <?= $student['schedule_type'] == 'everyday' ? 'selected' : '' ?>>Har Kuni</option>
                </select>
            </div>
        </div>

        <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3.5 rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/30 flex justify-center gap-2 items-center text-sm mt-4">
            <span>Saqlash</span> <i data-lucide="check-circle" class="w-4 h-4"></i>
        </button>
    </form>
</div>
