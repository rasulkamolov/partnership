<?php
// app/pages/add_enrollment.php
if (!$is_admin) { header("Location: ?p=dashboard"); exit; }

$pid = $_GET['profile_id'] ?? 0;
$stmt = $db->prepare("SELECT * FROM student_profiles WHERE id = ?");
$stmt->execute([$pid]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    echo "<div class='p-8 text-center text-rose-500 font-bold'>Profil topilmadi!</div>";
    return;
}

$groups = $db->query("SELECT * FROM groups ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$school_name = $db->query("SELECT name FROM schools WHERE id = " . $profile['school_id'])->fetchColumn();
?>

<div class="max-w-2xl mx-auto bg-white rounded-3xl p-8 border border-slate-200/60 shadow-lg">
    <div class="flex items-center gap-3 mb-8">
        <a href="?p=students" class="bg-slate-100 p-2 rounded-xl text-slate-500 hover:bg-slate-200 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h3 class="text-2xl font-black text-slate-900 tracking-tight">O'quvchiga Guruh Qo'shish</h3>
    </div>

    <?php $groups_json = json_encode($groups); ?>
    <script>
        const groups = <?= $groups_json ?>;

        function getGroupOptions() {
            if (groups.length === 0) return '<option disabled>Guruhlar mavjud emas</option>';
            return '<option value="" disabled selected>Guruhni Tanlang</option>' +
                   groups.map(g => `<option value="${g.name}" data-price="${g.price}">${g.name}</option>`).join('');
        }

        function addGroupRow() {
            const container = document.getElementById('groups-container');
            const index = container.children.length;
            const div = document.createElement('div');
            div.className = "space-y-3 p-4 bg-slate-50/50 rounded-xl border border-slate-100 relative group-row";
            div.innerHTML = `
                <div class="absolute -right-2 -top-2 cursor-pointer bg-white text-rose-500 rounded-full p-1 shadow-sm border border-rose-100 hover:bg-rose-50 transition ${index === 0 ? 'hidden' : ''}" onclick="this.parentElement.remove()">
                    <i data-lucide="x" class="w-3 h-3"></i>
                </div>
                <div>
                    <select name="st_group[]" onchange="updateRowPrice(this)" class="w-full px-4 py-3 bg-white rounded-xl text-sm font-semibold outline-none border border-slate-200/50 shadow-sm transition cursor-pointer text-slate-600" required>
                        ${getGroupOptions()}
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <input type="number" name="st_fee[]" placeholder="To'lov (SO'M)" class="w-full px-4 py-3 bg-white rounded-xl text-sm font-semibold outline-none focus:ring-2 focus:ring-indigo-500/50 border border-slate-200/50 shadow-sm transition" required>
                    <select name="st_schedule[]" class="w-full px-4 py-3 bg-white rounded-xl text-sm font-semibold outline-none border border-slate-200/50 shadow-sm transition cursor-pointer text-slate-600">
                        <option value="odd">Toq (Du/Chor/Ju)</option>
                        <option value="even">Juft (Se/Pay/Sha)</option>
                        <option value="everyday">Har Kuni</option>
                    </select>
                </div>
            `;
            container.appendChild(div);
            lucide.createIcons();
        }

        function updateRowPrice(select) {
            const price = select.options[select.selectedIndex].dataset.price;
            if (price) {
                const row = select.closest('.group-row');
                row.querySelector('input[name="st_fee[]"]').value = price;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            if(document.getElementById('groups-container').children.length === 0) {
                addGroupRow();
            }
        });
    </script>

    <form method="POST" class="space-y-6">
        <input type="hidden" name="add_enrollment_to_profile" value="1">
        <input type="hidden" name="profile_id" value="<?= $profile['id'] ?>">
        <input type="hidden" name="school_id" value="<?= $profile['school_id'] ?>">
        <input type="hidden" name="st_name" value="<?= htmlspecialchars($profile['name']) ?>">

        <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest">O'quvchi</p>
                <p class="font-bold text-indigo-900 text-lg"><?= htmlspecialchars($profile['name']) ?></p>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest">Maktab</p>
                <p class="font-bold text-indigo-900 text-sm"><?= htmlspecialchars($school_name) ?></p>
            </div>
        </div>

        <div id="groups-container" class="space-y-4">
            <!-- Group rows added by JS -->
        </div>

        <button type="button" onclick="addGroupRow()" class="w-full py-2 text-xs font-bold text-indigo-600 bg-indigo-50 rounded-xl border border-dashed border-indigo-200 hover:bg-indigo-100 transition flex items-center justify-center gap-2">
            <i data-lucide="plus" class="w-3 h-3"></i> Yana guruh qo'shish
        </button>

        <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3.5 rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/30 flex justify-center gap-2 items-center text-sm mt-4">
            <span>Saqlash</span> <i data-lucide="check-circle" class="w-4 h-4"></i>
        </button>
    </form>
</div>
